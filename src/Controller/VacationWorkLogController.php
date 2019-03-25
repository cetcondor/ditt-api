<?php

namespace App\Controller;

use App\Entity\SupportedYear;
use App\Entity\User;
use App\Entity\VacationWorkLog;
use App\Entity\WorkMonth;
use App\Event\MultipleVacationWorkLogApprovedEvent;
use App\Event\MultipleVacationWorkLogRejectedEvent;
use App\Event\VacationWorkLogApprovedEvent;
use App\Event\VacationWorkLogRejectedEvent;
use App\Repository\SupportedYearRepository;
use App\Repository\VacationWorkLogRepository;
use App\Repository\WorkMonthRepository;
use App\Service\VacationWorkLogService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VacationWorkLogController extends Controller
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
     * @var VacationWorkLogRepository
     */
    private $vacationWorkLogRepository;

    /**
     * @var VacationWorkLogService
     */
    private $vacationWorkLogService;

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

    /**
     * @param NormalizerInterface $normalizer
     * @param DenormalizerInterface $denormalizer
     * @param SupportedYearRepository $supportedYearRepository
     * @param VacationWorkLogRepository $vacationWorkLogRepository
     * @param VacationWorkLogService $vacationWorkLogService
     * @param WorkMonthRepository $workMonthRepository
     * @param EventDispatcherInterface $eventDispatcher
     * @param ValidatorInterface $validator
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        SupportedYearRepository $supportedYearRepository,
        VacationWorkLogRepository $vacationWorkLogRepository,
        VacationWorkLogService $vacationWorkLogService,
        WorkMonthRepository $workMonthRepository,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
        $this->supportedYearRepository = $supportedYearRepository;
        $this->vacationWorkLogRepository = $vacationWorkLogRepository;
        $this->vacationWorkLogService = $vacationWorkLogService;
        $this->workMonthRepository = $workMonthRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
        $this->tokenStorage = $tokenStorage;
    }

    public function bulkCreate(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);
        $vacationWorkLogs = [];
        $vacationWorkLogsByYear = [];

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

        foreach ($data as $normalizedVacationWorkLog) {
            try {
                $vacationWorkLog = $this->denormalizer->denormalize(
                    $normalizedVacationWorkLog,
                    VacationWorkLog::class
                );

                if (!$vacationWorkLog instanceof VacationWorkLog) {
                    throw new NotNormalizableValueException();
                }
            } catch (NotNormalizableValueException $e) {
                return JsonResponse::create(
                    ['detail' => 'Cannot denormalize work log.'], JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $workMonth = $this->workMonthRepository->findByWorkLogAndUser($vacationWorkLog, $token->getUser());

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

            $vacationWorkLog->setWorkMonth($workMonth);
            $vacationWorkLogs[] = $vacationWorkLog;

            if (!array_key_exists($workMonth->getYear()->getYear(), $vacationWorkLogsByYear)) {
                $vacationWorkLogsByYear[$workMonth->getYear()->getYear()] = 0;
            }

            ++$vacationWorkLogsByYear[$workMonth->getYear()->getYear()];
        }

        foreach ($vacationWorkLogsByYear as $year => $workLogCount) {
            /** @var SupportedYear */
            $supportedYear = $this->supportedYearRepository->getRepository()->find($year);

            if ($this->vacationWorkLogRepository->getRemainingVacationDays($token->getUser(), $supportedYear) < $workLogCount) {
                return JsonResponse::create(
                    ['detail' => 'Set duration exceeds number of vacation days allocated for this year.'],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }
        }

        foreach ($vacationWorkLogs as $index => $vacationWorkLog) {
            $errors = $this->validator->validate($vacationWorkLog);

            if (count($errors) > 0) {
                return JsonResponse::create(
                    ['detail' => sprintf('Vacation work log with index %d is not valid: %s', $index, $errors[0]->getMessage())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }
        }

        $this->vacationWorkLogService->createVacationWorkLogs($vacationWorkLogs);
        $normalizedVacationWorkLogs = [];

        foreach ($vacationWorkLogs as $vacationWorkLog) {
            $normalizedVacationWorkLogs[] = $this->normalizer->normalize(
                $vacationWorkLog,
                VacationWorkLog::class,
                ['groups' => ['vacation_work_log_out_detail']]
            );
        }

        return JsonResponse::create($normalizedVacationWorkLogs, JsonResponse::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function bulkMarkApproved(Request $request): Response
    {
        $data = json_decode((string) $request->getContent());
        if (!isset($data->workLogIds) || !is_array($data->workLogIds)) {
            return JsonResponse::create(
                ['detail' => 'Work log ids are missing.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $workLogs = [];

        foreach ($this->vacationWorkLogRepository->findByIds($data->workLogIds) as $workLog) {
            if (
                $workLog->getWorkMonth()->getUser()->getSupervisor() === null
                || $workLog->getWorkMonth()->getUser()->getSupervisor() !== $this->getUser()
            ) {
                return JsonResponse::create(null, JsonResponse::HTTP_FORBIDDEN);
            }

            if ($workLog->getTimeApproved()) {
                return JsonResponse::create(
                    ['detail' => sprintf('Vacation work log with id %d has been already approved.', $workLog->getId())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            if ($workLog->getTimeRejected()) {
                return JsonResponse::create(
                    ['detail' => sprintf('Vacation work log with id %d has been already rejected.', $workLog->getId())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $workLogs[] = $workLog;
        }

        foreach ($workLogs as $workLog) {
            $this->vacationWorkLogService->markApproved($workLog);
        }

        $supervisor = $workLogs[0]->getWorkMonth()->getUser()->getSupervisor();

        $this->eventDispatcher->dispatch(
            MultipleVacationWorkLogApprovedEvent::APPROVED,
            new MultipleVacationWorkLogApprovedEvent($workLogs, $supervisor)
        );

        $normalizedWorkLogs = [];

        foreach ($workLogs as $workLog) {
            $normalizedWorkLogs[] = $this->normalizer->normalize(
                $workLog,
                VacationWorkLog::class,
                ['groups' => ['vacation_work_log_out_detail']]
            );
        }

        return JsonResponse::create($normalizedWorkLogs, JsonResponse::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return Response
     */
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

        foreach ($this->vacationWorkLogRepository->findByIds($data->workLogIds) as $workLog) {
            if (
                $workLog->getWorkMonth()->getUser()->getSupervisor() === null
                || $workLog->getWorkMonth()->getUser()->getSupervisor() !== $this->getUser()
            ) {
                return JsonResponse::create(null, JsonResponse::HTTP_FORBIDDEN);
            }

            if ($workLog->getTimeApproved()) {
                return JsonResponse::create(
                    ['detail' => sprintf('Vacation work log with id %d has been already approved.', $workLog->getId())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            if ($workLog->getTimeRejected()) {
                return JsonResponse::create(
                    ['detail' => sprintf('Vacation work log with id %d has been already rejected.', $workLog->getId())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $workLogs[] = $workLog;
        }

        foreach ($workLogs as $workLog) {
            $this->vacationWorkLogService->markRejected($workLog, $data->rejectionMessage);
        }

        $supervisor = $workLogs[0]->getWorkMonth()->getUser()->getSupervisor();

        $this->eventDispatcher->dispatch(
            MultipleVacationWorkLogRejectedEvent::REJECTED,
            new MultipleVacationWorkLogRejectedEvent($workLogs, $supervisor)
        );

        $normalizedWorkLogs = [];

        foreach ($workLogs as $workLog) {
            $normalizedWorkLogs[] = $this->normalizer->normalize(
                $workLog,
                VacationWorkLog::class,
                ['groups' => ['vacation_work_log_out_detail']]
            );
        }

        return JsonResponse::create($normalizedWorkLogs, JsonResponse::HTTP_OK);
    }

    /**
     * @param int $id
     * @return Response
     */
    public function markApproved(int $id): Response
    {
        $workLog = $this->vacationWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof VacationWorkLog) {
            throw $this->createNotFoundException(sprintf('Vacation work log with id %d was not found.', $id));
        }

        if (
            $workLog->getWorkMonth()->getUser()->getSupervisor() === null
            || $workLog->getWorkMonth()->getUser()->getSupervisor() !== $this->getUser()
        ) {
            return JsonResponse::create(null, JsonResponse::HTTP_FORBIDDEN);
        }

        if ($workLog->getTimeApproved()) {
            return JsonResponse::create(
                ['detail' => 'Vacation work log month has been already approved.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workLog->getTimeRejected()) {
            return JsonResponse::create(
                ['detail' => 'Vacation work log month has been already rejected.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->vacationWorkLogService->markApproved($workLog);
        $supervisor = $workLog->getWorkMonth()->getUser()->getSupervisor();

        $this->eventDispatcher->dispatch(
            VacationWorkLogApprovedEvent::APPROVED,
            new VacationWorkLogApprovedEvent($workLog, $supervisor)
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                VacationWorkLog::class,
                ['groups' => ['vacation_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }

    /**
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function markRejected(Request $request, int $id): Response
    {
        $workLog = $this->vacationWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof VacationWorkLog) {
            throw $this->createNotFoundException(sprintf('Vacation work log with id %d was not found.', $id));
        }

        if (
            $workLog->getWorkMonth()->getUser()->getSupervisor() === null
            || $workLog->getWorkMonth()->getUser()->getSupervisor() !== $this->getUser()
        ) {
            return JsonResponse::create(null, JsonResponse::HTTP_FORBIDDEN);
        }

        if ($workLog->getTimeApproved()) {
            return JsonResponse::create(
                ['detail' => 'Vacation work log month has been already approved.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workLog->getTimeRejected()) {
            return JsonResponse::create(
                ['detail' => 'Vacation work log month has been already rejected.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $data = json_decode((string) $request->getContent());
        if (!isset($data->rejectionMessage)) {
            return JsonResponse::create(
                ['detail' => 'Rejection message is missing.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->vacationWorkLogService->markRejected($workLog, $data->rejectionMessage);
        $supervisor = $workLog->getWorkMonth()->getUser()->getSupervisor();

        $this->eventDispatcher->dispatch(
            VacationWorkLogRejectedEvent::REJECTED,
            new VacationWorkLogRejectedEvent($workLog, $supervisor)
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                VacationWorkLog::class,
                ['groups' => ['vacation_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }
}
