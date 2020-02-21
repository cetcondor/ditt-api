<?php

namespace App\Controller;

use App\Entity\BusinessTripWorkLog;
use App\Entity\HomeOfficeWorkLog;
use App\Entity\OvertimeWorkLog;
use App\Entity\SpecialLeaveWorkLog;
use App\Entity\TimeOffWorkLog;
use App\Entity\User;
use App\Entity\VacationWorkLog;
use App\Entity\WorkMonth;
use App\Event\WorkMonthApprovedEvent;
use App\Repository\BusinessTripWorkLogRepository;
use App\Repository\HomeOfficeWorkLogRepository;
use App\Repository\OvertimeWorkLogRepository;
use App\Repository\SpecialLeaveWorkLogRepository;
use App\Repository\TimeOffWorkLogRepository;
use App\Repository\UserRepository;
use App\Repository\VacationWorkLogRepository;
use App\Repository\WorkMonthRepository;
use App\Service\UserService;
use App\Service\WorkMonthService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class WorkMonthController extends Controller
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var WorkMonthRepository
     */
    private $workMonthRepository;

    /**
     * @var WorkMonthService
     */
    private $workMonthService;

    /**
     * @var BusinessTripWorkLogRepository
     */
    private $businessTripWorkLogRepository;

    /**
     * @var HomeOfficeWorkLogRepository
     */
    private $homeOfficeWorkLogRepository;

    /**
     * @var OvertimeWorkLogRepository
     */
    private $overtimeWorkLogRepository;

    /**
     * @var SpecialLeaveWorkLogRepository
     */
    private $specialLeaveWorkLogRepository;

    /**
     * @var TimeOffWorkLogRepository
     */
    private $timeOffWorkLogRepository;

    /**
     * @var VacationWorkLogRepository
     */
    private $vacationWorkLogRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var User
     */
    private $loggedUser;

    public function __construct(
        NormalizerInterface $normalizer,
        UserRepository $userRepository,
        UserService $userService,
        WorkMonthRepository $workMonthRepository,
        WorkMonthService $workMonthService,
        BusinessTripWorkLogRepository $businessTripWorkLogRepository,
        HomeOfficeWorkLogRepository $homeOfficeWorkLogRepository,
        OvertimeWorkLogRepository $overtimeWorkLogRepository,
        SpecialLeaveWorkLogRepository $specialLeaveWorkLogRepository,
        TimeOffWorkLogRepository $timeOffWorkLogRepository,
        VacationWorkLogRepository $vacationWorkLogRepository,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage
    ) {
        $this->normalizer = $normalizer;
        $this->userRepository = $userRepository;
        $this->userService = $userService;
        $this->workMonthRepository = $workMonthRepository;
        $this->workMonthService = $workMonthService;
        $this->businessTripWorkLogRepository = $businessTripWorkLogRepository;
        $this->homeOfficeWorkLogRepository = $homeOfficeWorkLogRepository;
        $this->overtimeWorkLogRepository = $overtimeWorkLogRepository;
        $this->specialLeaveWorkLogRepository = $specialLeaveWorkLogRepository;
        $this->timeOffWorkLogRepository = $timeOffWorkLogRepository;
        $this->vacationWorkLogRepository = $vacationWorkLogRepository;
        $this->eventDispatcher = $eventDispatcher;

        if (null !== $tokenStorage->getToken() && $tokenStorage->getToken()->getUser() instanceof User) {
            $this->loggedUser = $tokenStorage->getToken()->getUser();
        }
    }

    public function getWorkMonthDetail(int $id): Response
    {
        $workMonth = $this->workMonthRepository->getRepository()->find($id);
        if (!$workMonth || !$workMonth instanceof  WorkMonth) {
            throw $this->createNotFoundException(sprintf('Work month with id %d was not found', $id));
        }

        if (
            $workMonth->getUser()->getId() !== $this->loggedUser->getId()
            && WorkMonth::STATUS_OPENED === $workMonth->getStatus()
        ) {
            $emptyCollection = new ArrayCollection();
            $workMonth->setBusinessTripWorkLogs($emptyCollection);
            $workMonth->setHomeOfficeWorkLogs($emptyCollection);
            $workMonth->setOvertimeWorkLogs($emptyCollection);
            $workMonth->setSickDayWorkLogs($emptyCollection);
            $workMonth->setSpecialLeaveWorkLogs($emptyCollection);
            $workMonth->setTimeOffWorkLogs($emptyCollection);
            $workMonth->setVacationWorkLogs($emptyCollection);
            $workMonth->setWorkLogs($emptyCollection);
        }

        $user = $workMonth->getUser();
        $this->userService->fullfilRemainingVacationDays($user);
        $workMonth->setUser($user);

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workMonth,
                WorkMonth::class,
                ['groups' => ['work_month_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }

    public function setWorkTimeCorrection(Request $request, int $id)
    {
        $workMonth = $this->workMonthRepository->getRepository()->find($id);
        if (!$workMonth || !$workMonth instanceof  WorkMonth) {
            throw $this->createNotFoundException(sprintf('Work month with id %d was not found', $id));
        }

        if (
        !(
            $this->loggedUser !== $workMonth->getUser()
            && (
                in_array($this->loggedUser, $workMonth->getUser()->getAllSupervisors())
                || in_array(User::ROLE_SUPER_ADMIN, $this->loggedUser->getRoles())
            )
        )
        ) {
            throw $this->createAccessDeniedException('Not allowed to change work time correction.');
        }

        if ($workMonth->getStatus() === WorkMonth::STATUS_APPROVED) {
            return JsonResponse::create(
                ['detail' => 'Cannot set work time correction to closed work month.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $data = json_decode((string) $request->getContent());
        if (!isset($data->workTimeCorrection) || !is_numeric($data->workTimeCorrection)) {
            return JsonResponse::create(
                ['detail' => 'Work time correction is missing or it is not a number.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->workMonthService->setWorkTimeCorrection($workMonth, (int) $data->workTimeCorrection);

        if (
            $workMonth->getUser()->getId() !== $this->loggedUser->getId()
            && WorkMonth::STATUS_OPENED === $workMonth->getStatus()
        ) {
            $emptyCollection = new ArrayCollection();
            $workMonth->setBusinessTripWorkLogs($emptyCollection);
            $workMonth->setHomeOfficeWorkLogs($emptyCollection);
            $workMonth->setOvertimeWorkLogs($emptyCollection);
            $workMonth->setSickDayWorkLogs($emptyCollection);
            $workMonth->setSpecialLeaveWorkLogs($emptyCollection);
            $workMonth->setTimeOffWorkLogs($emptyCollection);
            $workMonth->setVacationWorkLogs($emptyCollection);
            $workMonth->setWorkLogs($emptyCollection);
        }

        $user = $workMonth->getUser();
        $this->userService->fullfilRemainingVacationDays($user);
        $workMonth->setUser($user);

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workMonth,
                WorkMonth::class,
                ['groups' => ['work_month_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }


    public function specialApprovals(int $supervisorId): Response
    {
        $supervisor = $this->userRepository->getRepository()->find($supervisorId);
        if (!$supervisor || !$supervisor instanceof User) {
            throw $this->createNotFoundException(sprintf('User with id %d was not found', $supervisorId));
        }

        $isSuperAdmin = in_array(
            User::ROLE_SUPER_ADMIN,
            $this->loggedUser->getRoles()
        );

        $response = [
            'businessTripWorkLogs' => [],
            'homeOfficeWorkLogs' => [],
            'overtimeWorkLogs' => [],
            'specialLeaveWorkLogs' => [],
            'timeOffWorkLogs' => [],
            'vacationWorkLogs' => [],
        ];

        if ($isSuperAdmin) {
            foreach ($this->businessTripWorkLogRepository->findAllWaitingForApproval() as $workLog) {
                $response['businessTripWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    BusinessTripWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->homeOfficeWorkLogRepository->findAllWaitingForApproval() as $workLog) {
                $response['homeOfficeWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    HomeOfficeWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->overtimeWorkLogRepository->findAllWaitingForApproval() as $workLog) {
                $response['overtimeWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    OvertimeWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->specialLeaveWorkLogRepository->findAllWaitingForApproval() as $workLog) {
                $response['specialLeaveWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    SpecialLeaveWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->timeOffWorkLogRepository->findAllWaitingForApproval() as $workLog) {
                $response['timeOffWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    TimeOffWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->vacationWorkLogRepository->findAllWaitingForApproval() as $workLog) {
                $response['vacationWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    VacationWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }
        } else {
            foreach ($this->businessTripWorkLogRepository->findAllWaitingForApprovalBySupervisor($supervisor) as $workLog) {
                $response['businessTripWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    BusinessTripWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->homeOfficeWorkLogRepository->findAllWaitingForApprovalBySupervisor($supervisor) as $workLog) {
                $response['homeOfficeWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    HomeOfficeWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->overtimeWorkLogRepository->findAllWaitingForApprovalBySupervisor($supervisor) as $workLog) {
                $response['overtimeWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    OvertimeWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->specialLeaveWorkLogRepository->findAllWaitingForApprovalBySupervisor($supervisor) as $workLog) {
                $response['specialLeaveWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    SpecialLeaveWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->timeOffWorkLogRepository->findAllWaitingForApprovalBySupervisor($supervisor) as $workLog) {
                $response['timeOffWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    TimeOffWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->vacationWorkLogRepository->findAllWaitingForApprovalBySupervisor($supervisor) as $workLog) {
                $response['vacationWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    VacationWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }
        }

        return JsonResponse::create($response, JsonResponse::HTTP_OK);
    }

    public function recentSpecialApprovals(int $supervisorId): Response
    {
        $supervisor = $this->userRepository->getRepository()->find($supervisorId);
        if (!$supervisor || !$supervisor instanceof User) {
            throw $this->createNotFoundException(sprintf('User with id %d was not found', $supervisorId));
        }

        $isSuperAdmin = in_array(
            User::ROLE_SUPER_ADMIN,
            $this->loggedUser->getRoles()
        );

        $response = [
            'businessTripWorkLogs' => [],
            'homeOfficeWorkLogs' => [],
            'overtimeWorkLogs' => [],
            'specialLeaveWorkLogs' => [],
            'timeOffWorkLogs' => [],
            'vacationWorkLogs' => [],
        ];

        if ($isSuperAdmin) {
            foreach ($this->businessTripWorkLogRepository->findAllRecent() as $workLog) {
                $response['businessTripWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    BusinessTripWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->homeOfficeWorkLogRepository->findAllRecent() as $workLog) {
                $response['homeOfficeWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    HomeOfficeWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->overtimeWorkLogRepository->findAllRecent() as $workLog) {
                $response['overtimeWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    OvertimeWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->specialLeaveWorkLogRepository->findAllRecent() as $workLog) {
                $response['specialLeaveWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    SpecialLeaveWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->timeOffWorkLogRepository->findAllRecent() as $workLog) {
                $response['timeOffWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    TimeOffWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->vacationWorkLogRepository->findAllRecent() as $workLog) {
                $response['vacationWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    VacationWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }
        } else {
            foreach ($this->businessTripWorkLogRepository->findAllRecentBySupervisor($supervisor) as $workLog) {
                $response['businessTripWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    BusinessTripWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->homeOfficeWorkLogRepository->findAllRecentBySupervisor($supervisor) as $workLog) {
                $response['homeOfficeWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    HomeOfficeWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->overtimeWorkLogRepository->findAllRecentBySupervisor($supervisor) as $workLog) {
                $response['overtimeWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    OvertimeWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->specialLeaveWorkLogRepository->findAllRecentBySupervisor($supervisor) as $workLog) {
                $response['specialLeaveWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    SpecialLeaveWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->timeOffWorkLogRepository->findAllRecentBySupervisor($supervisor) as $workLog) {
                $response['timeOffWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    TimeOffWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }

            foreach ($this->vacationWorkLogRepository->findAllRecentBySupervisor($supervisor) as $workLog) {
                $response['vacationWorkLogs'][] = $this->normalizer->normalize(
                    $workLog,
                    VacationWorkLog::class,
                    ['groups' => ['special_approvals_out_list']]
                );
            }
        }

        return JsonResponse::create($response, JsonResponse::HTTP_OK);
    }

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

        $this->workMonthService->markWaitingForApproval($workMonth);

        $user = $workMonth->getUser();
        $this->userService->fullfilRemainingVacationDays($user);
        $workMonth->setUser($user);

        return JsonResponse::create(
            $this->normalizer->normalize(
                $workMonth,
                WorkMonth::class,
                ['groups' => ['work_month_out_detail']]
            ), JsonResponse::HTTP_OK
        );
    }

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

        $this->workMonthService->markApproved($workMonth);

        $user = $workMonth->getUser();
        $this->userService->fullfilRemainingVacationDays($user);
        $workMonth->setUser($user);

        $supervisor = $this->getUser();

        $this->eventDispatcher->dispatch(
            new WorkMonthApprovedEvent($workMonth, $supervisor),
            WorkMonthApprovedEvent::APPROVED
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
