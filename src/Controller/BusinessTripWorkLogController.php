<?php

namespace App\Controller;

use App\Entity\BusinessTripWorkLog;
use App\Event\BusinessTripWorkLogApprovedEvent;
use App\Event\BusinessTripWorkLogRejectedEvent;
use App\Repository\BusinessTripWorkLogRepository;
use App\Service\BusinessTripWorkLogService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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
     * @var BusinessTripWorkLogService
     */
    private $businessTripWorkLogService;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        NormalizerInterface $normalizer,
        BusinessTripWorkLogRepository $businessTripWorkLogRepository,
        BusinessTripWorkLogService $businessTripWorkLogService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->normalizer = $normalizer;
        $this->businessTripWorkLogRepository = $businessTripWorkLogRepository;
        $this->businessTripWorkLogService = $businessTripWorkLogService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function markApproved(int $id): Response
    {
        $workLog = $this->businessTripWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof BusinessTripWorkLog) {
            throw $this->createNotFoundException(sprintf('Business trip work log with id %d was not found.', $id));
        }

        if (!in_array($this->getUser(), $workLog->getWorkMonth()->getUser()->getAllSupervisors())) {
            return JsonResponse::create(null, JsonResponse::HTTP_FORBIDDEN);
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

        $this->businessTripWorkLogService->markApproved($workLog);
        $supervisor = $this->getUser();

        $this->eventDispatcher->dispatch(
            new BusinessTripWorkLogApprovedEvent($workLog, $supervisor),
            BusinessTripWorkLogApprovedEvent::APPROVED
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                BusinessTripWorkLog::class,
                ['groups' => ['business_trip_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }

    public function markRejected(Request $request, int $id): Response
    {
        $workLog = $this->businessTripWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof BusinessTripWorkLog) {
            throw $this->createNotFoundException(sprintf('Business trip work log with id %d was not found.', $id));
        }

        if (!in_array($this->getUser(), $workLog->getWorkMonth()->getUser()->getAllSupervisors())) {
            return JsonResponse::create(null, JsonResponse::HTTP_FORBIDDEN);
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

        $data = json_decode((string) $request->getContent());
        if (!isset($data->rejectionMessage)) {
            return JsonResponse::create(
                ['detail' => 'Rejection message is missing.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->businessTripWorkLogService->markRejected($workLog, $data->rejectionMessage);
        $supervisor = $this->getUser();

        $this->eventDispatcher->dispatch(
            new BusinessTripWorkLogRejectedEvent($workLog, $supervisor),
            BusinessTripWorkLogRejectedEvent::REJECTED
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
