<?php

namespace App\Controller;

use App\Entity\HomeOfficeWorkLog;
use App\Entity\User;
use App\Event\HomeOfficeWorkLogApprovedEvent;
use App\Event\HomeOfficeWorkLogRejectedEvent;
use App\Repository\HomeOfficeWorkLogRepository;
use App\Service\HomeOfficeWorkLogService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * @var HomeOfficeWorkLogService
     */
    private $homeOfficeWorkLogService;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param NormalizerInterface $normalizer
     * @param HomeOfficeWorkLogRepository $homeOfficeWorkLogRepository
     * @param HomeOfficeWorkLogService $homeOfficeWorkLogService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        NormalizerInterface $normalizer,
        HomeOfficeWorkLogRepository $homeOfficeWorkLogRepository,
        HomeOfficeWorkLogService $homeOfficeWorkLogService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->normalizer = $normalizer;
        $this->homeOfficeWorkLogRepository = $homeOfficeWorkLogRepository;
        $this->homeOfficeWorkLogService = $homeOfficeWorkLogService;
        $this->eventDispatcher = $eventDispatcher;
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

        $this->homeOfficeWorkLogService->markApproved($workLog);

        $supervisor = $this->getUser();
        if (!$supervisor) {
            $supervisor = new User();
        }

        $this->eventDispatcher->dispatch(
            HomeOfficeWorkLogApprovedEvent::APPROVED,
            new HomeOfficeWorkLogApprovedEvent($workLog, $supervisor)
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                HomeOfficeWorkLog::class,
                ['groups' => ['home_office_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }

    /**
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function markRejected(Request $request, int $id): Response
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

        $data = json_decode($request->getContent());
        if (!isset($data->rejectionMessage)) {
            return JsonResponse::create(
                ['detail' => 'Rejection message is missing.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->homeOfficeWorkLogService->markRejected($workLog, $data->rejectionMessage);

        $supervisor = $this->getUser();
        if (!$supervisor) {
            $supervisor = new User();
        }

        $this->eventDispatcher->dispatch(
            HomeOfficeWorkLogRejectedEvent::REJECTED,
            new HomeOfficeWorkLogRejectedEvent($workLog, $supervisor)
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                HomeOfficeWorkLog::class,
                ['groups' => ['home_office_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }
}
