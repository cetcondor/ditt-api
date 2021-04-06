<?php

namespace App\Controller;

use App\Entity\SpecialLeaveWorkLog;
use App\Event\MultipleSpecialLeaveWorkLogApprovedEvent;
use App\Event\MultipleSpecialLeaveWorkLogRejectedEvent;
use App\Event\SpecialLeaveWorkLogApprovedEvent;
use App\Event\SpecialLeaveWorkLogRejectedEvent;
use App\Repository\SpecialWorkLogRepository;
use App\Repository\SupportedYearRepository;
use App\Repository\WorkMonthRepository;
use App\Service\SpecialWorkLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SpecialLeaveWorkLogController extends AbstractSpecialWorkLogController
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
            new SpecialWorkLogRepository($entityManager, SpecialLeaveWorkLog::class),
            $specialWorkLogService,
            $supportedYearRepository,
            $validator,
            $workMonthRepository,
            SpecialLeaveWorkLog::class,
            'special_leave_work_log_out_detail',
            SpecialLeaveWorkLogApprovedEvent::class,
            MultipleSpecialLeaveWorkLogApprovedEvent::class,
            SpecialLeaveWorkLogRejectedEvent::class,
            MultipleSpecialLeaveWorkLogRejectedEvent::class,
        );
    }
}
