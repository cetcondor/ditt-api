<?php

namespace App\Controller;

use App\Entity\BusinessTripWorkLog;
use App\Event\BusinessTripWorkLogApprovedEvent;
use App\Event\BusinessTripWorkLogRejectedEvent;
use App\Event\MultipleBusinessTripWorkLogApprovedEvent;
use App\Event\MultipleBusinessTripWorkLogRejectedEvent;
use App\Repository\SpecialWorkLogRepository;
use App\Repository\SupportedYearRepository;
use App\Repository\WorkMonthRepository;
use App\Service\SpecialWorkLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class BusinessTripWorkLogController extends AbstractSpecialWorkLogController
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
            new SpecialWorkLogRepository($entityManager, BusinessTripWorkLog::class),
            $specialWorkLogService,
            $supportedYearRepository,
            $validator,
            $workMonthRepository,
            BusinessTripWorkLog::class,
            'business_trip_work_log_out_detail',
            BusinessTripWorkLogApprovedEvent::class,
            MultipleBusinessTripWorkLogApprovedEvent::class,
            BusinessTripWorkLogRejectedEvent::class,
            MultipleBusinessTripWorkLogRejectedEvent::class,
        );
    }
}
