<?php

namespace App\Controller;

use App\Entity\TimeOffWorkLog;
use App\Repository\TimeOffWorkLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints\Time;

class TimeOffWorkLogController extends Controller
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var TimeOffWorkLogRepository
     */
    private $timeOffWorkLogRepository;

    /**
     * @param NormalizerInterface $normalizer
     * @param TimeOffWorkLogRepository $timeOffWorkLogRepository
     */
    public function __construct(
        NormalizerInterface $normalizer,
        TimeOffWorkLogRepository $timeOffWorkLogRepository
    ) {
        $this->normalizer = $normalizer;
        $this->timeOffWorkLogRepository = $timeOffWorkLogRepository;
    }

    /**
     * @param int $id
     * @return Response
     */
    public function markApproved(int $id): Response
    {
        $workLog = $this->timeOffWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof TimeOffWorkLog) {
            throw $this->createNotFoundException(sprintf('Time off work log with id %d was not found.', $id));
        }

        if ($workLog->getTimeApproved()) {
            return JsonResponse::create(
                ['detail' => 'Time off work log month has been already approved.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workLog->getTimeRejected()) {
            return JsonResponse::create(
                ['detail' => 'Time off work log month has been already rejected.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->timeOffWorkLogRepository->markApproved($workLog);

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                TimeOffWorkLog::class,
                ['groups' => ['time_off_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }

    /**
     * @param int $id
     * @return Response
     */
    public function markRejected(int $id): Response
    {
        $workLog = $this->timeOffWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof TimeOffWorkLog) {
            throw $this->createNotFoundException(sprintf('Time off work log with id %d was not found.', $id));
        }

        if ($workLog->getTimeApproved()) {
            return JsonResponse::create(
                ['detail' => 'Time off work log month has been already approved.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workLog->getTimeRejected()) {
            return JsonResponse::create(
                ['detail' => 'Time off work log month has been already rejected.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->timeOffWorkLogRepository->markRejected($workLog);

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                TimeOffWorkLog::class,
                ['groups' => ['time_off_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }
}
