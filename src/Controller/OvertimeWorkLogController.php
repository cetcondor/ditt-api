<?php

namespace App\Controller;

use App\Entity\OvertimeWorkLog;
use App\Event\OvertimeWorkLogApprovedEvent;
use App\Event\OvertimeWorkLogRejectedEvent;
use App\Repository\OvertimeWorkLogRepository;
use App\Service\OvertimeWorkLogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OvertimeWorkLogController extends AbstractController
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

    public function markApproved(int $id): Response
    {
        $workLog = $this->overtimeWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof OvertimeWorkLog) {
            throw $this->createNotFoundException(sprintf('Overtime work log with id %d was not found.', $id));
        }

        if (!in_array($this->getUser(), $workLog->getWorkMonth()->getUser()->getAllSupervisors())) {
            return JsonResponse::create(null, JsonResponse::HTTP_FORBIDDEN);
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

        $this->eventDispatcher->dispatch(
            new OvertimeWorkLogApprovedEvent($workLog, $supervisor),
            OvertimeWorkLogApprovedEvent::APPROVED
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                OvertimeWorkLog::class,
                ['groups' => ['overtime_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }

    public function markRejected(Request $request, int $id): Response
    {
        $workLog = $this->overtimeWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof OvertimeWorkLog) {
            throw $this->createNotFoundException(sprintf('Overtime work log with id %d was not found.', $id));
        }

        if (!in_array($this->getUser(), $workLog->getWorkMonth()->getUser()->getAllSupervisors())) {
            return JsonResponse::create(null, JsonResponse::HTTP_FORBIDDEN);
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

        $data = json_decode((string) $request->getContent());
        if (!isset($data->rejectionMessage)) {
            return JsonResponse::create(
                ['detail' => 'Rejection message is missing.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->overtimeWorkLogService->markRejected($workLog, $data->rejectionMessage);
        $supervisor = $this->getUser();

        $this->eventDispatcher->dispatch(
            new OvertimeWorkLogRejectedEvent($workLog, $supervisor),
            OvertimeWorkLogRejectedEvent::REJECTED
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
