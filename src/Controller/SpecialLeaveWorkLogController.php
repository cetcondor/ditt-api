<?php

namespace App\Controller;

use App\Entity\SpecialLeaveWorkLog;
use App\Entity\User;
use App\Entity\WorkMonth;
use App\Event\MultipleSpecialLeaveWorkLogApprovedEvent;
use App\Event\MultipleSpecialLeaveWorkLogRejectedEvent;
use App\Event\SpecialLeaveWorkLogApprovedEvent;
use App\Event\SpecialLeaveWorkLogRejectedEvent;
use App\Repository\SpecialLeaveWorkLogRepository;
use App\Repository\SupportedYearRepository;
use App\Repository\WorkMonthRepository;
use App\Service\SpecialLeaveWorkLogService;
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

class SpecialLeaveWorkLogController extends Controller
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
     * @var SupportedYearRepository
     */
    private $supportedYearRepository;

    /**
     * @var SpecialLeaveWorkLogRepository
     */
    private $specialLeaveWorkLogRepository;

    /**
     * @var SpecialLeaveWorkLogService
     */
    private $specialLeaveWorkLogService;

    /**
     * @var WorkMonthRepository
     */
    private $workMonthRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        SupportedYearRepository $supportedYearRepository,
        SpecialLeaveWorkLogRepository $specialLeaveWorkLogRepository,
        SpecialLeaveWorkLogService $specialLeaveWorkLogService,
        WorkMonthRepository $workMonthRepository,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
        $this->supportedYearRepository = $supportedYearRepository;
        $this->specialLeaveWorkLogRepository = $specialLeaveWorkLogRepository;
        $this->specialLeaveWorkLogService = $specialLeaveWorkLogService;
        $this->workMonthRepository = $workMonthRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
        $this->tokenStorage = $tokenStorage;
    }

    public function bulkCreate(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);
        $specialLeaveWorkLogs = [];

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

        foreach ($data as $normalizedSpecialLeaveWorkLog) {
            try {
                $specialLeaveWorkLog = $this->denormalizer->denormalize(
                    $normalizedSpecialLeaveWorkLog,
                    SpecialLeaveWorkLog::class
                );

                if (!$specialLeaveWorkLog instanceof SpecialLeaveWorkLog) {
                    throw new NotNormalizableValueException();
                }
            } catch (NotNormalizableValueException $e) {
                return JsonResponse::create(
                    ['detail' => 'Cannot denormalize work log.'], JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $workMonth = $this->workMonthRepository->findByWorkLogAndUser($specialLeaveWorkLog, $token->getUser());

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

            $specialLeaveWorkLog->setWorkMonth($workMonth);
            $specialLeaveWorkLogs[] = $specialLeaveWorkLog;
        }

        foreach ($specialLeaveWorkLogs as $index => $specialLeaveWorkLog) {
            $errors = $this->validator->validate($specialLeaveWorkLog);

            if (count($errors) > 0) {
                return JsonResponse::create(
                    ['detail' => sprintf('Special leave work log with index %d is not valid: %s', $index, $errors[0]->getMessage())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }
        }

        $this->specialLeaveWorkLogService->createSpecialLeaveWorkLogs($specialLeaveWorkLogs);
        $normalizedSpecialLeaveWorkLogs = [];

        foreach ($specialLeaveWorkLogs as $specialLeaveWorkLog) {
            $normalizedSpecialLeaveWorkLogs[] = $this->normalizer->normalize(
                $specialLeaveWorkLog,
                SpecialLeaveWorkLog::class,
                ['groups' => ['special_leave_work_log_out_detail']]
            );
        }

        return JsonResponse::create($normalizedSpecialLeaveWorkLogs, JsonResponse::HTTP_CREATED);
    }

    public function bulkMarkApproved(Request $request): Response
    {
        $data = json_decode((string) $request->getContent());
        if (!isset($data->workLogIds) || !is_array($data->workLogIds)) {
            return JsonResponse::create(
                ['detail' => 'Work log ids are missing.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $workLogs = [];

        foreach ($this->specialLeaveWorkLogRepository->findByIds($data->workLogIds) as $workLog) {
            if (!in_array($this->getUser(), $workLog->getWorkMonth()->getUser()->getAllSupervisors())) {
                return JsonResponse::create(null, JsonResponse::HTTP_FORBIDDEN);
            }

            if ($workLog->getTimeApproved()) {
                return JsonResponse::create(
                    ['detail' => sprintf('Special leave work log with id %d has been already approved.', $workLog->getId())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            if ($workLog->getTimeRejected()) {
                return JsonResponse::create(
                    ['detail' => sprintf('Special leave work log with id %d has been already rejected.', $workLog->getId())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $workLogs[] = $workLog;
        }

        foreach ($workLogs as $workLog) {
            $this->specialLeaveWorkLogService->markApproved($workLog);
        }

        $supervisor = $this->getUser();

        $this->eventDispatcher->dispatch(
            new MultipleSpecialLeaveWorkLogApprovedEvent($workLogs, $supervisor),
            MultipleSpecialLeaveWorkLogApprovedEvent::APPROVED
        );

        $normalizedWorkLogs = [];

        foreach ($workLogs as $workLog) {
            $normalizedWorkLogs[] = $this->normalizer->normalize(
                $workLog,
                SpecialLeaveWorkLog::class,
                ['groups' => ['special_leave_work_log_out_detail']]
            );
        }

        return JsonResponse::create($normalizedWorkLogs, JsonResponse::HTTP_OK);
    }

    public function bulkMarkRejected(Request $request): Response
    {
        $data = json_decode((string) $request->getContent());
        if (!isset($data->workLogIds) || !is_array($data->workLogIds)) {
            return JsonResponse::create(
                ['detail' => 'Work log ids are missing.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }
        if (!isset($data->rejectionMessage)) {
            return JsonResponse::create(
                ['detail' => 'Rejection message is missing.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $workLogs = [];

        foreach ($this->specialLeaveWorkLogRepository->findByIds($data->workLogIds) as $workLog) {
            if (!in_array($this->getUser(), $workLog->getWorkMonth()->getUser()->getAllSupervisors())) {
                return JsonResponse::create(null, JsonResponse::HTTP_FORBIDDEN);
            }

            if ($workLog->getTimeApproved()) {
                return JsonResponse::create(
                    ['detail' => sprintf('Special leave work log with id %d has been already approved.', $workLog->getId())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            if ($workLog->getTimeRejected()) {
                return JsonResponse::create(
                    ['detail' => sprintf('Special leave work log with id %d has been already rejected.', $workLog->getId())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $workLogs[] = $workLog;
        }

        foreach ($workLogs as $workLog) {
            $this->specialLeaveWorkLogService->markRejected($workLog, $data->rejectionMessage);
        }

        $supervisor = $this->getUser();

        $this->eventDispatcher->dispatch(
            new MultipleSpecialLeaveWorkLogRejectedEvent($workLogs, $supervisor),
            MultipleSpecialLeaveWorkLogRejectedEvent::REJECTED
        );

        $normalizedWorkLogs = [];

        foreach ($workLogs as $workLog) {
            $normalizedWorkLogs[] = $this->normalizer->normalize(
                $workLog,
                SpecialLeaveWorkLog::class,
                ['groups' => ['special_leave_work_log_out_detail']]
            );
        }

        return JsonResponse::create($normalizedWorkLogs, JsonResponse::HTTP_OK);
    }

    public function markApproved(int $id): Response
    {
        $workLog = $this->specialLeaveWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof SpecialLeaveWorkLog) {
            throw $this->createNotFoundException(sprintf('Special leave work log with id %d was not found.', $id));
        }

        if (!in_array($this->getUser(), $workLog->getWorkMonth()->getUser()->getAllSupervisors())) {
            return JsonResponse::create(null, JsonResponse::HTTP_FORBIDDEN);
        }

        if ($workLog->getTimeApproved()) {
            return JsonResponse::create(
                ['detail' => 'Special leave work log month has been already approved.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workLog->getTimeRejected()) {
            return JsonResponse::create(
                ['detail' => 'Special leave work log month has been already rejected.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->specialLeaveWorkLogService->markApproved($workLog);
        $supervisor = $this->getUser();

        $this->eventDispatcher->dispatch(
            new SpecialLeaveWorkLogApprovedEvent($workLog, $supervisor),
            SpecialLeaveWorkLogApprovedEvent::APPROVED
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                SpecialLeaveWorkLog::class,
                ['groups' => ['special_leave_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }

    public function markRejected(Request $request, int $id): Response
    {
        $workLog = $this->specialLeaveWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof SpecialLeaveWorkLog) {
            throw $this->createNotFoundException(sprintf('Special leave work log with id %d was not found.', $id));
        }

        if (!in_array($this->getUser(), $workLog->getWorkMonth()->getUser()->getAllSupervisors())) {
            return JsonResponse::create(null, JsonResponse::HTTP_FORBIDDEN);
        }

        if ($workLog->getTimeApproved()) {
            return JsonResponse::create(
                ['detail' => 'Special leave work log month has been already approved.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workLog->getTimeRejected()) {
            return JsonResponse::create(
                ['detail' => 'Special leave work log month has been already rejected.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $data = json_decode((string) $request->getContent());
        if (!isset($data->rejectionMessage)) {
            return JsonResponse::create(
                ['detail' => 'Rejection message is missing.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->specialLeaveWorkLogService->markRejected($workLog, $data->rejectionMessage);
        $supervisor = $this->getUser();

        $this->eventDispatcher->dispatch(
            new SpecialLeaveWorkLogRejectedEvent($workLog, $supervisor),
            SpecialLeaveWorkLogRejectedEvent::REJECTED
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                SpecialLeaveWorkLog::class,
                ['groups' => ['special_leave_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }
}
