<?php

namespace App\Controller;

use App\Entity\TimeOffWorkLog;
use App\Event\TimeOffWorkLogApprovedEvent;
use App\Event\TimeOffWorkLogRejectedEvent;
use App\Repository\TimeOffWorkLogRepository;
use App\Service\TimeOffWorkLogService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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
     * @var TimeOffWorkLogService
     */
    private $timeOffWorkLogService;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param NormalizerInterface $normalizer
     * @param TimeOffWorkLogRepository $timeOffWorkLogRepository
     * @param TimeOffWorkLogService $timeOffWorkLogService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        NormalizerInterface $normalizer,
        TimeOffWorkLogRepository $timeOffWorkLogRepository,
        TimeOffWorkLogService $timeOffWorkLogService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->normalizer = $normalizer;
        $this->timeOffWorkLogRepository = $timeOffWorkLogRepository;
        $this->timeOffWorkLogService = $timeOffWorkLogService;
        $this->eventDispatcher = $eventDispatcher;
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

        if (!in_array($this->getUser(), $workLog->getWorkMonth()->getUser()->getAllSupervisors())) {
            return JsonResponse::create(null, JsonResponse::HTTP_FORBIDDEN);
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

        $this->timeOffWorkLogService->markApproved($workLog);
        $supervisor = $workLog->getWorkMonth()->getUser()->getSupervisor();

        $this->eventDispatcher->dispatch(
            TimeOffWorkLogApprovedEvent::APPROVED,
            new TimeOffWorkLogApprovedEvent($workLog, $supervisor)
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                TimeOffWorkLog::class,
                ['groups' => ['time_off_work_log_out_detail']]
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
        $workLog = $this->timeOffWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof TimeOffWorkLog) {
            throw $this->createNotFoundException(sprintf('Time off work log with id %d was not found.', $id));
        }

        if (!in_array($this->getUser(), $workLog->getWorkMonth()->getUser()->getAllSupervisors())) {
            return JsonResponse::create(null, JsonResponse::HTTP_FORBIDDEN);
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

        $data = json_decode((string) $request->getContent());
        if (!isset($data->rejectionMessage)) {
            return JsonResponse::create(
                ['detail' => 'Rejection message is missing.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->timeOffWorkLogService->markRejected($workLog, $data->rejectionMessage);
        $supervisor = $workLog->getWorkMonth()->getUser()->getSupervisor();

        $this->eventDispatcher->dispatch(
            TimeOffWorkLogRejectedEvent::REJECTED,
            new TimeOffWorkLogRejectedEvent($workLog, $supervisor)
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                TimeOffWorkLog::class,
                ['groups' => ['time_off_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }
}
