<?php

namespace App\Controller;

use App\Entity\BusinessTripWorkLog;
use App\Repository\BusinessTripWorkLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     * @param NormalizerInterface $normalizer
     * @param BusinessTripWorkLogRepository $businessTripWorkLogRepository
     */
    public function __construct(
        NormalizerInterface $normalizer,
        BusinessTripWorkLogRepository $businessTripWorkLogRepository
    ) {
        $this->normalizer = $normalizer;
        $this->businessTripWorkLogRepository = $businessTripWorkLogRepository;
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

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                BusinessTripWorkLog::class,
                ['groups' => ['business_trip_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }
}
