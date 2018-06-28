<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\VacationWorkLog;
use App\Event\VacationWorkLogApprovedEvent;
use App\Event\VacationWorkLogRejectedEvent;
use App\Repository\VacationWorkLogRepository;
use App\Service\VacationWorkLogService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VacationWorkLogController extends Controller
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var VacationWorkLogRepository
     */
    private $vacationWorkLogRepository;

    /**
     * @var VacationWorkLogService
     */
    private $vacationWorkLogService;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param NormalizerInterface $normalizer
     * @param VacationWorkLogRepository $vacationWorkLogRepository
     * @param VacationWorkLogService $vacationWorkLogService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        NormalizerInterface $normalizer,
        VacationWorkLogRepository $vacationWorkLogRepository,
        VacationWorkLogService $vacationWorkLogService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->normalizer = $normalizer;
        $this->vacationWorkLogRepository = $vacationWorkLogRepository;
        $this->vacationWorkLogService = $vacationWorkLogService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param int $id
     * @return Response
     */
    public function markApproved(int $id): Response
    {
        $workLog = $this->vacationWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof VacationWorkLog) {
            throw $this->createNotFoundException(sprintf('Vacation work log with id %d was not found.', $id));
        }

        if ($workLog->getTimeApproved()) {
            return JsonResponse::create(
                ['detail' => 'Vacation work log month has been already approved.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workLog->getTimeRejected()) {
            return JsonResponse::create(
                ['detail' => 'Vacation work log month has been already rejected.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->vacationWorkLogService->markApproved($workLog);

        $supervisor = $this->getUser();
        if (!$supervisor) {
            $supervisor = new User();
        }

        $this->eventDispatcher->dispatch(
            VacationWorkLogApprovedEvent::APPROVED,
            new VacationWorkLogApprovedEvent($workLog, $supervisor)
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                VacationWorkLog::class,
                ['groups' => ['vacation_work_log_out_detail']]
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
        $workLog = $this->vacationWorkLogRepository->getRepository()->find($id);
        if (!$workLog || !$workLog instanceof VacationWorkLog) {
            throw $this->createNotFoundException(sprintf('Vacation work log with id %d was not found.', $id));
        }

        if ($workLog->getTimeApproved()) {
            return JsonResponse::create(
                ['detail' => 'Vacation work log month has been already approved.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workLog->getTimeRejected()) {
            return JsonResponse::create(
                ['detail' => 'Vacation work log month has been already rejected.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $data = json_decode($request->getContent());
        if (!isset($data->rejectionMessage)) {
            return JsonResponse::create(
                ['detail' => 'Rejection message is missing.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->vacationWorkLogService->markRejected($workLog, $data->rejectionMessage);

        $supervisor = $this->getUser();
        if (!$supervisor) {
            $supervisor = new User();
        }

        $this->eventDispatcher->dispatch(
            VacationWorkLogRejectedEvent::REJECTED,
            new VacationWorkLogRejectedEvent($workLog, $supervisor)
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workLog,
                VacationWorkLog::class,
                ['groups' => ['vacation_work_log_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }
}
