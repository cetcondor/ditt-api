<?php

namespace App\Controller;

use App\Entity\OvertimeWorkLog;
use App\Entity\User;
use App\Event\OvertimeWorkLogApprovedEvent;
use App\Event\OvertimeWorkLogRejectedEvent;
use App\Repository\OvertimeWorkLogRepository;
use App\Service\OvertimeWorkLogService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OvertimeWorkLogController extends Controller
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var OvertimeWorkLogRepository
     */
    private $overtimeWorkLogRepository;

    /**
     * @var OvertimeWorkLogService
     */
    private $overtimeWorkLogService;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param NormalizerInterface $normalizer
     * @param OvertimeWorkLogRepository $overtimeWorkLogRepository
     * @param OvertimeWorkLogService $overtimeWorkLogService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        NormalizerInterface $normalizer,
        OvertimeWorkLogRepository $overtimeWorkLogRepository,
        OvertimeWorkLogService $overtimeWorkLogService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->normalizer = $normalizer;
        $this->overtimeWorkLogRepository = $overtimeWorkLogRepository;
        $this->overtimeWorkLogService = $overtimeWorkLogService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param int $id
     * @return Response
     */
    public function markApproved(int $id): Response
    {
        $workLog = $this->overtimeWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof OvertimeWorkLog) {
            throw $this->createNotFoundException(sprintf('Overtime work log with id %d was not found.', $id));
        }

        if ($workLog->getTimeApproved()) {
            return JsonResponse::create(
                ['detail' => 'Overtime work log month has been already approved.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workLog->getTimeRejected()) {
            return JsonResponse::create(
                ['detail' => 'Overtime work log month has been already rejected.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->overtimeWorkLogService->markApproved($workLog);

        $supervisor = $this->getUser();
        if (!$supervisor) {
            $supervisor = new User();
        }

        $this->eventDispatcher->dispatch(
            OvertimeWorkLogApprovedEvent::APPROVED,
            new OvertimeWorkLogApprovedEvent($workLog, $supervisor)
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                OvertimeWorkLog::class,
                ['groups' => ['overtime_work_log_out_detail']]
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
        $workLog = $this->overtimeWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof OvertimeWorkLog) {
            throw $this->createNotFoundException(sprintf('Overtime work log with id %d was not found.', $id));
        }

        if ($workLog->getTimeApproved()) {
            return JsonResponse::create(
                ['detail' => 'Overtime work log month has been already approved.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workLog->getTimeRejected()) {
            return JsonResponse::create(
                ['detail' => 'Overtime work log month has been already rejected.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $data = json_decode($request->getContent());
        if (!isset($data->rejectionMessage)) {
            return JsonResponse::create(
                ['detail' => 'Rejection message is missing.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->overtimeWorkLogService->markRejected($workLog, $data->rejectionMessage);

        $supervisor = $this->getUser();
        if (!$supervisor) {
            $supervisor = new User();
        }

        $this->eventDispatcher->dispatch(
            OvertimeWorkLogRejectedEvent::REJECTED,
            new OvertimeWorkLogRejectedEvent($workLog, $supervisor)
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                OvertimeWorkLog::class,
                ['groups' => ['overtime_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }
}
