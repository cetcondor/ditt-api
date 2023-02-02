<?php

namespace App\Controller;

use App\Entity\TrainingWorkLog;
use App\Event\MultipleTrainingWorkLogApprovedEvent;
use App\Event\MultipleTrainingWorkLogRejectedEvent;
use App\Event\TrainingWorkLogApprovedEvent;
use App\Event\TrainingWorkLogRejectedEvent;
use App\Repository\SpecialWorkLogRepository;
use App\Repository\SupportedYearRepository;
use App\Repository\WorkMonthRepository;
use App\Service\SpecialWorkLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TrainingWorkLogController extends AbstractSpecialWorkLogController
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
            new SpecialWorkLogRepository($entityManager, TrainingWorkLog::class),
            $specialWorkLogService,
            $supportedYearRepository,
            $validator,
            $workMonthRepository,
            TrainingWorkLog::class,
            'training_work_log_out_detail',
            TrainingWorkLogApprovedEvent::class,
            MultipleTrainingWorkLogApprovedEvent::class,
            TrainingWorkLogRejectedEvent::class,
            MultipleTrainingWorkLogRejectedEvent::class,
        );
    }
}
