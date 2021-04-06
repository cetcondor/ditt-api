<?php

namespace App\Controller;

use App\Entity\SpecialWorkLogInterface;
use App\Entity\User;
use App\Entity\WorkMonth;
use App\Repository\SpecialWorkLogRepository;
use App\Repository\SupportedYearRepository;
use App\Repository\WorkMonthRepository;
use App\Service\SpecialWorkLogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractSpecialWorkLogController extends AbstractController
{
    protected DenormalizerInterface $denormalizer;
    protected EventDispatcherInterface $eventDispatcher;
    protected NormalizerInterface $normalizer;
    protected SpecialWorkLogRepository $specialWorkLogRepository;
    protected SpecialWorkLogService $specialWorkLogService;
    protected SupportedYearRepository $supportedYearRepository;
    protected ValidatorInterface $validator;
    protected WorkMonthRepository $workMonthRepository;

    protected string $entityClassName;
    protected string $entityNormalizationGroup;

    protected string $approvedEventClassName;
    protected string $multipleApprovedEventClassName;
    protected string $rejectedEventClassName;
    protected string $multipleRejectedEventClassName;

    public function __construct(
        DenormalizerInterface $denormalizer,
        EventDispatcherInterface $eventDispatcher,
        NormalizerInterface $normalizer,
        SpecialWorkLogRepository $specialWorkLogRepository,
        SpecialWorkLogService $specialWorkLogService,
        SupportedYearRepository $supportedYearRepository,
        ValidatorInterface $validator,
        WorkMonthRepository $workMonthRepository,
        string $entityClassName,
        string $entityNormalizationGroup,
        string $approvedEventClassName,
        string $multipleApprovedEventClassName,
        string $rejectedEventClassName,
        string $multipleRejectedEventClassName
    ) {
        $this->denormalizer = $denormalizer;
        $this->eventDispatcher = $eventDispatcher;
        $this->normalizer = $normalizer;
        $this->specialWorkLogRepository = $specialWorkLogRepository;
        $this->specialWorkLogService = $specialWorkLogService;
        $this->supportedYearRepository = $supportedYearRepository;
        $this->validator = $validator;
        $this->workMonthRepository = $workMonthRepository;

        $this->entityClassName = $entityClassName;
        $this->entityNormalizationGroup = $entityNormalizationGroup;

        $this->approvedEventClassName = $approvedEventClassName;
        $this->multipleApprovedEventClassName = $multipleApprovedEventClassName;
        $this->rejectedEventClassName = $rejectedEventClassName;
        $this->multipleRejectedEventClassName = $multipleRejectedEventClassName;
    }

    public function bulkCreate(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (!$data || !is_array($data)) {
            return new JsonResponse(
                ['detail' => 'Expected array of work logs.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // Authorization is checked in security layer of Symfony, this is necessary because of PHP Stan
        if (!$this->getUser() || !$this->getUser() instanceof User) {
            return new JsonResponse(
                ['detail' => 'Cannot create work log without user.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $workLogs = [];

        foreach ($data as $normalizedWorkLog) {
            try {
                $workLog = $this->denormalizer->denormalize(
                    $normalizedWorkLog,
                    $this->entityClassName
                );

                if (!$workLog instanceof SpecialWorkLogInterface) {
                    throw new NotNormalizableValueException();
                }
            } catch (\Exception $e) {
                return new JsonResponse(
                    ['detail' => 'Cannot denormalize work log.'], JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $workMonth = $this->workMonthRepository->findByWorkLogAndUser($workLog, $this->getUser());

            if (!$workMonth) {
                return new JsonResponse(
                    ['detail' => 'Cannot create work log without work month.'],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            if ($workMonth->getStatus() === WorkMonth::STATUS_APPROVED) {
                return new JsonResponse(
                    ['detail' => 'Cannot add work log to closed work month.'],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $workLog->setWorkMonth($workMonth);
            $workLogs[] = $workLog;
        }

        foreach ($workLogs as $index => $item) {
            $errors = $this->validator->validate($item);

            if (count($errors) > 0) {
                return new JsonResponse(
                    ['detail' => sprintf('Work log with index %d is not valid: %s', $index, $errors[0]->getMessage())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }
        }

        $this->specialWorkLogService->createWorkLogs($workLogs);
        $normalizedWorkLogs = [];

        foreach ($workLogs as $item) {
            $normalizedWorkLogs[] = $this->normalizer->normalize(
                $item,
                $this->entityClassName,
                ['groups' => [$this->entityNormalizationGroup]]
            );
        }

        return new JsonResponse($normalizedWorkLogs, JsonResponse::HTTP_CREATED);
    }

    public function bulkMarkApproved(Request $request): Response
    {
        $data = json_decode((string) $request->getContent());

        if (!isset($data->workLogIds) || !is_array($data->workLogIds)) {
            return new JsonResponse(
                ['detail' => 'Work log ids are missing.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // Authorization is checked in security layer of Symfony, this is necessary because of PHP Stan
        if (!$this->getUser() || !$this->getUser() instanceof User) {
            return new JsonResponse(
                ['detail' => 'Cannot process work log without user.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $workLogs = [];

        foreach ($this->specialWorkLogRepository->findByIds($data->workLogIds) as $workLog) {
            if (!in_array($this->getUser(), $workLog->getWorkMonth()->getUser()->getAllSupervisors())) {
                return new JsonResponse(null, JsonResponse::HTTP_FORBIDDEN);
            }

            if ($workLog->getTimeApproved()) {
                return new JsonResponse(
                    ['detail' => sprintf('Work log with id %d has been already approved.', $workLog->getId())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            if ($workLog->getTimeRejected()) {
                return new JsonResponse(
                    ['detail' => sprintf('Work log with id %d has been already rejected.', $workLog->getId())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $workLogs[] = $workLog;
        }

        foreach ($workLogs as $workLog) {
            $this->specialWorkLogService->markApproved($workLog);
        }

        $supervisor = $this->getUser();
        $event = new $this->multipleApprovedEventClassName($workLogs, $supervisor);
        $this->eventDispatcher->dispatch($event, $event::EVENT);

        $normalizedWorkLogs = [];

        foreach ($workLogs as $workLog) {
            $normalizedWorkLogs[] = $this->normalizer->normalize(
                $workLog,
                $this->entityClassName,
                ['groups' => [$this->entityNormalizationGroup]]
            );
        }

        return new JsonResponse($normalizedWorkLogs, JsonResponse::HTTP_OK);
    }

    public function bulkMarkRejected(Request $request): Response
    {
        $data = json_decode((string) $request->getContent());

        if (!isset($data->workLogIds) || !is_array($data->workLogIds)) {
            return new JsonResponse(
                ['detail' => 'Work log ids are missing.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if (!isset($data->rejectionMessage)) {
            return new JsonResponse(
                ['detail' => 'Rejection message is missing.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // Authorization is checked in security layer of Symfony, this is necessary because of PHP Stan
        if (!$this->getUser() || !$this->getUser() instanceof User) {
            return new JsonResponse(
                ['detail' => 'Cannot process work log without user.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $workLogs = [];

        foreach ($this->specialWorkLogRepository->findByIds($data->workLogIds) as $workLog) {
            if (!in_array($this->getUser(), $workLog->getWorkMonth()->getUser()->getAllSupervisors())) {
                return new JsonResponse(null, JsonResponse::HTTP_FORBIDDEN);
            }

            if ($workLog->getTimeApproved()) {
                return new JsonResponse(
                    ['detail' => sprintf('Work log with id %d has been already approved.', $workLog->getId())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            if ($workLog->getTimeRejected()) {
                return new JsonResponse(
                    ['detail' => sprintf('Work log with id %d has been already rejected.', $workLog->getId())],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $workLogs[] = $workLog;
        }

        foreach ($workLogs as $workLog) {
            $this->specialWorkLogService->markRejected($workLog, $data->rejectionMessage);
        }

        $supervisor = $this->getUser();
        $event = new $this->multipleRejectedEventClassName($workLogs, $supervisor);
        $this->eventDispatcher->dispatch($event, $event::EVENT);

        $normalizedWorkLogs = [];

        foreach ($workLogs as $workLog) {
            $normalizedWorkLogs[] = $this->normalizer->normalize(
                $workLog,
                $this->entityClassName,
                ['groups' => [$this->entityNormalizationGroup]]
            );
        }

        return new JsonResponse($normalizedWorkLogs, JsonResponse::HTTP_OK);
    }

    public function markApproved(int $id): Response
    {
        $workLog = $this->specialWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof SpecialWorkLogInterface) {
            throw $this->createNotFoundException(sprintf('Work log with id %d was not found.', $id));
        }

        if (!in_array($this->getUser(), $workLog->getWorkMonth()->getUser()->getAllSupervisors())) {
            return new JsonResponse(null, JsonResponse::HTTP_FORBIDDEN);
        }

        if ($workLog->getTimeApproved()) {
            return new JsonResponse(
                ['detail' => 'Work log month has been already approved.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workLog->getTimeRejected()) {
            return new JsonResponse(
                ['detail' => 'Work log month has been already rejected.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->specialWorkLogService->markApproved($workLog);

        $supervisor = $this->getUser();
        $event = new $this->approvedEventClassName($workLog, $supervisor);
        $this->eventDispatcher->dispatch($event, $event::EVENT);

        return new JsonResponse(
            $this->normalizer->normalize(
                $workLog,
                $this->entityClassName,
                ['groups' => [$this->entityNormalizationGroup]]
            ), JsonResponse::HTTP_OK
        );
    }

    public function markRejected(Request $request, int $id): Response
    {
        $workLog = $this->specialWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof SpecialWorkLogInterface) {
            throw $this->createNotFoundException(sprintf('Work log with id %d was not found.', $id));
        }

        if (!in_array($this->getUser(), $workLog->getWorkMonth()->getUser()->getAllSupervisors())) {
            return new JsonResponse(null, JsonResponse::HTTP_FORBIDDEN);
        }

        if ($workLog->getTimeApproved()) {
            return new JsonResponse(
                ['detail' => 'Work log month has been already approved.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workLog->getTimeRejected()) {
            return new JsonResponse(
                ['detail' => 'Work log month has been already rejected.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $data = json_decode((string) $request->getContent());

        if (!isset($data->rejectionMessage)) {
            return new JsonResponse(
                ['detail' => 'Rejection message is missing.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->specialWorkLogService->markRejected($workLog, $data->rejectionMessage);

        $supervisor = $this->getUser();
        $event = new $this->rejectedEventClassName($workLog, $supervisor);
        $this->eventDispatcher->dispatch($event, $event::EVENT);

        return new JsonResponse(
            $this->normalizer->normalize(
                $workLog,
                $this->entityClassName,
                ['groups' => [$this->entityNormalizationGroup]]
            ), JsonResponse::HTTP_OK
        );
    }
}
