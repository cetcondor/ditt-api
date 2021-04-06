<?php

namespace App\Controller;

use App\Entity\SpecialWorkLogInterface;
use App\Entity\SupportedYear;
use App\Entity\User;
use App\Entity\VacationWorkLog;
use App\Entity\WorkMonth;
use App\Event\MultipleVacationWorkLogApprovedEvent;
use App\Event\MultipleVacationWorkLogRejectedEvent;
use App\Event\VacationWorkLogApprovedEvent;
use App\Event\VacationWorkLogRejectedEvent;
use App\Repository\SpecialWorkLogRepository;
use App\Repository\SupportedYearRepository;
use App\Repository\VacationWorkLogRepository;
use App\Repository\WorkMonthRepository;
use App\Service\SpecialWorkLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class VacationWorkLogController extends AbstractSpecialWorkLogController
{
    private VacationWorkLogRepository $vacationWorkLogRepository;

    public function __construct(
        DenormalizerInterface $denormalizer,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        NormalizerInterface $normalizer,
        SpecialWorkLogService $specialWorkLogService,
        SupportedYearRepository $supportedYearRepository,
        VacationWorkLogRepository $vacationWorkLogRepository,
        ValidatorInterface $validator,
        WorkMonthRepository $workMonthRepository
    ) {
        parent::__construct(
            $denormalizer,
            $eventDispatcher,
            $normalizer,
            new SpecialWorkLogRepository($entityManager, VacationWorkLog::class),
            $specialWorkLogService,
            $supportedYearRepository,
            $validator,
            $workMonthRepository,
            VacationWorkLog::class,
            'vacation_work_log_out_detail',
            VacationWorkLogApprovedEvent::class,
            MultipleVacationWorkLogApprovedEvent::class,
            VacationWorkLogRejectedEvent::class,
            MultipleVacationWorkLogRejectedEvent::class,
        );

        $this->vacationWorkLogRepository = $vacationWorkLogRepository;
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
        $vacationWorkLogsByYear = [];

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

            if (!array_key_exists($workMonth->getYear()->getYear(), $vacationWorkLogsByYear)) {
                $vacationWorkLogsByYear[$workMonth->getYear()->getYear()] = 0;
            }

            ++$vacationWorkLogsByYear[$workMonth->getYear()->getYear()];
        }

        foreach ($vacationWorkLogsByYear as $year => $workLogCount) {
            /** @var SupportedYear */
            $supportedYear = $this->supportedYearRepository->getRepository()->find($year);

            if ($this->vacationWorkLogRepository->getRemainingVacationDays($this->getUser(), $supportedYear) < $workLogCount) {
                return JsonResponse::create(
                    ['detail' => 'Set duration exceeds number of vacation days allocated for this year.'],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }
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
}
