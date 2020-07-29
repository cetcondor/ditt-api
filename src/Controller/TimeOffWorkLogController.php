<?php

namespace App\Controller;

use App\Entity\TimeOffWorkLog;
use App\Entity\User;
use App\Entity\WorkMonth;
use App\Event\TimeOffWorkLogApprovedEvent;
use App\Event\TimeOffWorkLogRejectedEvent;
use App\Repository\TimeOffWorkLogRepository;
use App\Repository\WorkMonthRepository;
use App\Service\TimeOffWorkLogService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TimeOffWorkLogController extends Controller
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

    /**
     * @var TimeOffWorkLogRepository
     */
    private $timeOffWorkLogRepository;

    /**
     * @var TimeOffWorkLogService
     */
    private $timeOffWorkLogService;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var WorkMonthRepository
     */
    private $workMonthRepository;

    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        TimeOffWorkLogRepository $timeOffWorkLogRepository,
        TimeOffWorkLogService $timeOffWorkLogService,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        ValidatorInterface $validator,
        WorkMonthRepository $workMonthRepository
    ) {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
        $this->timeOffWorkLogRepository = $timeOffWorkLogRepository;
        $this->timeOffWorkLogService = $timeOffWorkLogService;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->validator = $validator;
        $this->workMonthRepository = $workMonthRepository;
    }

    public function bulkCreate(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);
        $timeOffWorkLogs = [];

        if (!$data || !is_array($data)) {
            return JsonResponse::create(
                ['detail' => 'Expected array of work logs.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $token = $this->tokenStorage->getToken();

        // Authorization is checked in security layer of Symfony, this is necessary because of PHP Stan
        if (!$token || !$token->getUser() instanceof User) {
            return JsonResponse::create(
                ['detail' => 'Cannot create work log without user.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        foreach ($data as $normalizedTimeOffWorkLog) {
            try {
                $timeOffWorkLog = $this->denormalizer->denormalize(
                    $normalizedTimeOffWorkLog,
                    TimeOffWorkLog::class
                );

                if (!$timeOffWorkLog instanceof TimeOffWorkLog) {
                    throw new NotNormalizableValueException();
                }
            } catch (NotNormalizableValueException $e) {
                return JsonResponse::create(
                    ['detail' => 'Cannot denormalize work log.'], JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $workMonth = $this->workMonthRepository->findByWorkLogAndUser($timeOffWorkLog, $token->getUser());

            if (!$workMonth) {
                return JsonResponse::create(
                    ['detail' => 'Cannot create work log without work month.'], JsonResponse::HTTP_BAD_REQUEST
                );
            }

            if ($workMonth->getStatus() === WorkMonth::STATUS_APPROVED) {
                return JsonResponse::create(
                    ['detail' => 'Cannot add work log to closed work month.'], JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $timeOffWorkLog->setWorkMonth($workMonth);
            $timeOffWorkLogs[] = $timeOffWorkLog;
        }

        foreach ($timeOffWorkLogs as $index => $timeOffWorkLog) {
            $errors = $this->validator->validate($timeOffWorkLog);

            if (count($errors) > 0) {
                return JsonResponse::create(
                    ['detail' => sprintf('Time off work log with index %d is not valid: %s', $index, $errors[0]->getMessage())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }
        }

        $this->timeOffWorkLogService->createTimeOffWorkLogs($timeOffWorkLogs);
        $normalizedTimeOffWorkLogs = [];

        foreach ($timeOffWorkLogs as $timeOffWorkLog) {
            $normalizedTimeOffWorkLogs[] = $this->normalizer->normalize(
                $timeOffWorkLog,
                TimeOffWorkLog::class,
                ['groups' => ['time_off_work_log_out_detail']]
            );
        }

        return JsonResponse::create($normalizedTimeOffWorkLogs, JsonResponse::HTTP_CREATED);
    }

    public function markApproved(int $id): Response
    {
        $workLog = $this->timeOffWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof TimeOffWorkLog) {
            throw $this->createNotFoundException(sprintf('Time off work log with id %d was not found.', $id));
        }

        if (!in_array($this->getUser(), $workLog->getWorkMonth()->getUser()->getAllSupervisors())) {
            return JsonResponse::create(null, JsonResponse::HTTP_FORBIDDEN);
        }

        if ($workLog->getTimeApproved()) {
            return JsonResponse::create(
                ['detail' => 'Time off work log month has been already approved.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workLog->getTimeRejected()) {
            return JsonResponse::create(
                ['detail' => 'Time off work log month has been already rejected.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->timeOffWorkLogService->markApproved($workLog);
        $supervisor = $this->getUser();

        $this->eventDispatcher->dispatch(
            new TimeOffWorkLogApprovedEvent($workLog, $supervisor),
            TimeOffWorkLogApprovedEvent::APPROVED
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                TimeOffWorkLog::class,
                ['groups' => ['time_off_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }

    public function markRejected(Request $request, int $id): Response
    {
        $workLog = $this->timeOffWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof TimeOffWorkLog) {
            throw $this->createNotFoundException(sprintf('Time off work log with id %d was not found.', $id));
        }

        if (!in_array($this->getUser(), $workLog->getWorkMonth()->getUser()->getAllSupervisors())) {
            return JsonResponse::create(null, JsonResponse::HTTP_FORBIDDEN);
        }

        if ($workLog->getTimeApproved()) {
            return JsonResponse::create(
                ['detail' => 'Time off work log month has been already approved.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workLog->getTimeRejected()) {
            return JsonResponse::create(
                ['detail' => 'Time off work log month has been already rejected.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $data = json_decode((string) $request->getContent());
        if (!isset($data->rejectionMessage)) {
            return JsonResponse::create(
                ['detail' => 'Rejection message is missing.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->timeOffWorkLogService->markRejected($workLog, $data->rejectionMessage);
        $supervisor = $this->getUser();

        $this->eventDispatcher->dispatch(
            new TimeOffWorkLogRejectedEvent($workLog, $supervisor),
            TimeOffWorkLogRejectedEvent::REJECTED
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                TimeOffWorkLog::class,
                ['groups' => ['time_off_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }
}
