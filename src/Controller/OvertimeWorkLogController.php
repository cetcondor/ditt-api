<?php

namespace App\Controller;

use App\Entity\OvertimeWorkLog;
use App\Event\MultipleOvertimeWorkLogApprovedEvent;
use App\Event\MultipleOvertimeWorkLogRejectedEvent;
use App\Event\OvertimeWorkLogApprovedEvent;
use App\Event\OvertimeWorkLogRejectedEvent;
use App\Repository\SpecialWorkLogRepository;
use App\Repository\SupportedYearRepository;
use App\Repository\WorkMonthRepository;
use App\Service\SpecialWorkLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OvertimeWorkLogController extends AbstractSpecialWorkLogController
{
    public function __construct(
        DenormalizerInterface $denormalizer,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        NormalizerInterface $normalizer,
        SpecialWorkLogService $specialWorkLogService,
        SupportedYearRepository $supportedYearRepository,
        ValidatorInterface $validator,
        WorkMonthRepository $workMonthRepository
    ) {
        parent::__construct(
            $denormalizer,
            $eventDispatcher,
            $normalizer,
            new SpecialWorkLogRepository($entityManager, OvertimeWorkLog::class),
            $specialWorkLogService,
            $supportedYearRepository,
            $validator,
            $workMonthRepository,
            OvertimeWorkLog::class,
            'overtime_work_log_out_detail',
            OvertimeWorkLogApprovedEvent::class,
            MultipleOvertimeWorkLogApprovedEvent::class,
            OvertimeWorkLogRejectedEvent::class,
            MultipleOvertimeWorkLogRejectedEvent::class,
        );
    }
}
