<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\WorkMonth;
use App\Event\WorkMonthApprovedEvent;
use App\Repository\WorkMonthRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class WorkMonthController extends Controller
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var WorkMonthRepository
     */
    private $workMonthRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param NormalizerInterface $normalizer
     * @param WorkMonthRepository $workMonthRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        NormalizerInterface $normalizer,
        WorkMonthRepository $workMonthRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->normalizer = $normalizer;
        $this->workMonthRepository = $workMonthRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param int $id
     * @return Response
     */
    public function markWaitingForApproval(int $id): Response
    {
        $workMonth = $this->workMonthRepository->getRepository()->find($id);
        if (!$workMonth || !$workMonth instanceof WorkMonth) {
            throw $this->createNotFoundException(sprintf('Work month with id %d was not found.', $id));
        }

        if ($workMonth->getStatus() === WorkMonth::STATUS_APPROVED) {
            return JsonResponse::create(
                ['detail' => 'Work month has been already approved.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workMonth->getStatus() === WorkMonth::STATUS_WAITING_FOR_APPROVAL) {
            return JsonResponse::create(
                ['detail' => 'Work month has been already sent for approval.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->workMonthRepository->markWaitingForApproval($workMonth);

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workMonth,
                WorkMonth::class,
                ['groups' => ['work_month_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }

    /**
     * @param int $id
     * @return Response
     */
    public function markApproved(int $id): Response
    {
        $workMonth = $this->workMonthRepository->getRepository()->find($id);
        if (!$workMonth || !$workMonth instanceof WorkMonth) {
            throw $this->createNotFoundException(sprintf('Work month with id %d was not found.', $id));
        }

        if ($workMonth->getStatus() === WorkMonth::STATUS_APPROVED) {
            return JsonResponse::create(
                ['detail' => 'Work month has been already approved.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($workMonth->getStatus() === WorkMonth::STATUS_OPENED) {
            return JsonResponse::create(
                ['detail' => 'Work month has not been sent for approval yet.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->workMonthRepository->markApproved($workMonth);

        $supervisor = $this->getUser();
        if (!$supervisor) { // This needs to be here for tests to work. In production the condition will never be met.
            $supervisor = new User();
        }
        $this->eventDispatcher->dispatch(
            WorkMonthApprovedEvent::APPROVED,
            new WorkMonthApprovedEvent($workMonth, $supervisor)
        );

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workMonth,
                WorkMonth::class,
                ['groups' => ['work_month_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }
}
