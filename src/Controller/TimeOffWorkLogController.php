<?php

namespace App\Controller;

use App\Entity\TimeOffWorkLog;
use App\Event\MultipleTimeOffWorkLogApprovedEvent;
use App\Event\MultipleTimeOffWorkLogRejectedEvent;
use App\Event\TimeOffWorkLogApprovedEvent;
use App\Event\TimeOffWorkLogRejectedEvent;
use App\Repository\SpecialWorkLogRepository;
use App\Repository\SupportedYearRepository;
use App\Repository\WorkMonthRepository;
use App\Service\SpecialWorkLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TimeOffWorkLogController extends AbstractSpecialWorkLogController
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
            new SpecialWorkLogRepository($entityManager, TimeOffWorkLog::class),
            $specialWorkLogService,
            $supportedYearRepository,
            $validator,
            $workMonthRepository,
            TimeOffWorkLog::class,
            'time_off_work_log_out_detail',
            TimeOffWorkLogApprovedEvent::class,
            MultipleTimeOffWorkLogApprovedEvent::class,
            TimeOffWorkLogRejectedEvent::class,
            MultipleTimeOffWorkLogRejectedEvent::class,
        );
    }
}
