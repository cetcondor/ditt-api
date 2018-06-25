<?php

namespace App\Controller;

use App\Entity\HomeOfficeWorkLog;
use App\Repository\HomeOfficeWorkLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class HomeOfficeWorkLogController extends Controller
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var HomeOfficeWorkLogRepository
     */
    private $homeOfficeWorkLogRepository;

    /**
     * @param NormalizerInterface $normalizer
     * @param HomeOfficeWorkLogRepository $homeOfficeWorkLogRepository
     */
    public function __construct(
        NormalizerInterface $normalizer,
        HomeOfficeWorkLogRepository $homeOfficeWorkLogRepository
    ) {
        $this->normalizer = $normalizer;
        $this->homeOfficeWorkLogRepository = $homeOfficeWorkLogRepository;
    }

    /**
     * @param int $id
     * @return Response
     */
    public function markApproved(int $id): Response
    {
        $workLog = $this->homeOfficeWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof HomeOfficeWorkLog) {
            throw $this->createNotFoundException(sprintf('Home office work log with id %d was not found.', $id));
        }

        if ($workLog->getTimeApproved()) {
            return JsonResponse::create(
                ['detail' => 'Home office work log month has been already approved.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workLog->getTimeRejected()) {
            return JsonResponse::create(
                ['detail' => 'Home office work log month has been already rejected.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->homeOfficeWorkLogRepository->markApproved($workLog);

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                HomeOfficeWorkLog::class,
                ['groups' => ['home_office_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }

    /**
     * @param int $id
     * @return Response
     */
    public function markRejected(int $id): Response
    {
        $workLog = $this->homeOfficeWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof HomeOfficeWorkLog) {
            throw $this->createNotFoundException(sprintf('Home office work log with id %d was not found.', $id));
        }

        if ($workLog->getTimeApproved()) {
            return JsonResponse::create(
                ['detail' => 'Home office work log month has been already approved.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workLog->getTimeRejected()) {
            return JsonResponse::create(
                ['detail' => 'Home office work log month has been already rejected.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->homeOfficeWorkLogRepository->markRejected($workLog);

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                HomeOfficeWorkLog::class,
                ['groups' => ['home_office_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }
}
