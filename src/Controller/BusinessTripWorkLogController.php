<?php

namespace App\Controller;

use App\Entity\BusinessTripWorkLog;
use App\Entity\User;
use App\Event\BusinessTripWorkLogApprovedEvent;
use App\Event\BusinessTripWorkLogRejectedEvent;
use App\Repository\BusinessTripWorkLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BusinessTripWorkLogController extends Controller
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var BusinessTripWorkLogRepository
     */
    private $businessTripWorkLogRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param NormalizerInterface $normalizer
     * @param BusinessTripWorkLogRepository $businessTripWorkLogRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        NormalizerInterface $normalizer,
        BusinessTripWorkLogRepository $businessTripWorkLogRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->normalizer = $normalizer;
        $this->businessTripWorkLogRepository = $businessTripWorkLogRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param int $id
     * @return Response
     */
    public function markApproved(int $id): Response
    {
        $workLog = $this->businessTripWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof BusinessTripWorkLog) {
            throw $this->createNotFoundException(sprintf('Business trip work log with id %d was not found.', $id));
        }

        if ($workLog->getTimeApproved()) {
            return JsonResponse::create(
                ['detail' => 'Business trip work log month has been already approved.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workLog->getTimeRejected()) {
            return JsonResponse::create(
                ['detail' => 'Business trip work log month has been already rejected.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->businessTripWorkLogRepository->markApproved($workLog);

        $supervisor = $this->getUser();
        if (!$supervisor) {
            $supervisor = new User();
        }

        $this->eventDispatcher->dispatch(
            BusinessTripWorkLogApprovedEvent::APPROVED,
            new BusinessTripWorkLogApprovedEvent($workLog, $supervisor)
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                BusinessTripWorkLog::class,
                ['groups' => ['business_trip_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }

    /**
     * @param int $id
     * @return Response
     */
    public function markRejected(int $id): Response
    {
        $workLog = $this->businessTripWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof BusinessTripWorkLog) {
            throw $this->createNotFoundException(sprintf('Business trip work log with id %d was not found.', $id));
        }

        if ($workLog->getTimeApproved()) {
            return JsonResponse::create(
                ['detail' => 'Business trip work log month has been already approved.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workLog->getTimeRejected()) {
            return JsonResponse::create(
                ['detail' => 'Business trip work log month has been already rejected.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->businessTripWorkLogRepository->markRejected($workLog);

        $supervisor = $this->getUser();
        if (!$supervisor) {
            $supervisor = new User();
        }

        $this->eventDispatcher->dispatch(
            BusinessTripWorkLogRejectedEvent::REJECTED,
            new BusinessTripWorkLogRejectedEvent($workLog, $supervisor)
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                BusinessTripWorkLog::class,
                ['groups' => ['business_trip_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }
}
