<?php

namespace App\Controller;

use App\Entity\HomeOfficeWorkLog;
use App\Event\HomeOfficeWorkLogApprovedEvent;
use App\Event\HomeOfficeWorkLogRejectedEvent;
use App\Event\MultipleHomeOfficeWorkLogApprovedEvent;
use App\Event\MultipleHomeOfficeWorkLogRejectedEvent;
use App\Repository\SpecialWorkLogRepository;
use App\Repository\SupportedYearRepository;
use App\Repository\WorkMonthRepository;
use App\Service\SpecialWorkLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class HomeOfficeWorkLogController extends AbstractSpecialWorkLogController
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
            new SpecialWorkLogRepository($entityManager, HomeOfficeWorkLog::class),
            $specialWorkLogService,
            $supportedYearRepository,
            $validator,
            $workMonthRepository,
            HomeOfficeWorkLog::class,
            'home_office_work_log_out_detail',
            HomeOfficeWorkLogApprovedEvent::class,
            MultipleHomeOfficeWorkLogApprovedEvent::class,
            HomeOfficeWorkLogRejectedEvent::class,
            MultipleHomeOfficeWorkLogRejectedEvent::class,
        );
    }
}
