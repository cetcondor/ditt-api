<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\BusinessTripWorkLogRepository;
use App\Repository\HomeOfficeWorkLogRepository;
use App\Repository\OvertimeWorkLogRepository;
use App\Repository\SpecialLeaveWorkLogRepository;
use App\Repository\TimeOffWorkLogRepository;
use App\Repository\UserRepository;
use App\Repository\VacationWorkLogRepository;
use App\Service\ConfigService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NotificationsCommand extends Command
{
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
     * @var string
     */
    private $clientSpecialApprovalsUrl;

    /**
     * @var ConfigService
     */
    private $configService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $mailSenderAddress;

    /**
     * @var \Twig\Environment
     */
    private $templating;

    public function __construct(
        BusinessTripWorkLogRepository $businessTripWorkLogRepository,
        HomeOfficeWorkLogRepository $homeOfficeWorkLogRepository,
        OvertimeWorkLogRepository $overtimeWorkLogRepository,
        SpecialLeaveWorkLogRepository $specialLeaveWorkLogRepository,
        TimeOffWorkLogRepository $timeOffWorkLogRepository,
        VacationWorkLogRepository $vacationWorkLogRepository,
        string $clientSpecialApprovalsUrl,
        ConfigService $configService,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        \Swift_Mailer $mailer,
        string $mailSenderAddress,
        \Twig\Environment $templating
    ) {
        parent::__construct();

        $this->businessTripWorkLogRepository = $businessTripWorkLogRepository;
        $this->homeOfficeWorkLogRepository = $homeOfficeWorkLogRepository;
        $this->overtimeWorkLogRepository = $overtimeWorkLogRepository;
        $this->specialLeaveWorkLogRepository = $specialLeaveWorkLogRepository;
        $this->timeOffWorkLogRepository = $timeOffWorkLogRepository;
        $this->vacationWorkLogRepository = $vacationWorkLogRepository;
        $this->mailSenderAddress = $mailSenderAddress;
        $this->clientSpecialApprovalsUrl = $clientSpecialApprovalsUrl;
        $this->configService = $configService;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        $this->mailSenderAddress = $mailSenderAddress;
        $this->templating = $templating;
    }

    protected function configure(): void
    {
        $this
            ->setName('notifications:send')
            ->setDescription('Send notifications to all users according to their notification settings');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->configService->getConfig();
        $isHolidayToday = $config->isHolidayToday();

        $toNotify = [];
        $toStoreNotificationTimeOnly = [];

        foreach ($this->userRepository->getRepository()->findAll() as $user) {
            if (!$user->getNotifications()->canSendSupervisorInfo($isHolidayToday)) {
                continue;
            }

            try {
                $waitingCount = count($this->businessTripWorkLogRepository->findAllWaitingForApprovalBySupervisor($user))
                    + count($this->homeOfficeWorkLogRepository->findAllWaitingForApprovalBySupervisor($user))
                    + count($this->overtimeWorkLogRepository->findAllWaitingForApprovalBySupervisor($user))
                    + count($this->specialLeaveWorkLogRepository->findAllWaitingForApprovalBySupervisor($user))
                    + count($this->timeOffWorkLogRepository->findAllWaitingForApprovalBySupervisor($user))
                    + count($this->vacationWorkLogRepository->findAllWaitingForApprovalBySupervisor($user));
            } catch (\Exception $ex) {
                $waitingCount = 0;
            }

            if ($waitingCount > 0) {
                $toNotify[] = new class($user, $waitingCount) {
                    /**
                     * @var User
                     */
                    public $user;
                    /**
                     * @var int
                     */
                    public $waitingForApprovalCount;

                    public function __construct(User $user, int $waitingForApprovalCount)
                    {
                        $this->user = $user;
                        $this->waitingForApprovalCount = $waitingForApprovalCount;
                    }
                };
            } else {
                $toStoreNotificationTimeOnly[] = $user;
            }
        }

        $dateTime = new \DateTimeImmutable();

        foreach ($toNotify as $details) {
            $htmlContent = $this->templating->render('notifications/supervisor_daily_info.html.twig', [
                'waitingForApprovalCount' => $details->waitingForApprovalCount,
                'clientSpecialApprovalsUrl' => $this->clientSpecialApprovalsUrl,
            ]);
            $message = (new \Swift_Message())
                ->setSubject('Versand Erinnerungsmails')
                ->setFrom([$this->mailSenderAddress => $this->mailSenderAddress])
                ->setTo($details->user->getEmail())
                ->setBody($htmlContent, 'text/html')
                ->addPart((new \Html2Text\Html2Text($htmlContent))->getText(), 'text/plain');

            if (!$this->mailer->send($message)) {
                $output->writeln(
                    sprintf('[%s] %s | NOT SENT', $dateTime->format('Y-m-d H:i:s'), $details->user->getEmail())
                );
            } else {
                /** @var User $user */
                $user = $details->user;
                $user->getNotifications()->setSupervisorInfoLastNotificationDateTime($dateTime);
                $this->entityManager->flush();

                $output->writeln(
                    sprintf('[%s] %s | SENT', $dateTime->format('Y-m-d H:i:s'), $details->user->getEmail())
                );
            }
        }

        foreach ($toStoreNotificationTimeOnly as $user) {
            $user->getNotifications()->setSupervisorInfoLastNotificationDateTime($dateTime);
            $this->entityManager->flush();

            $output->writeln(
                sprintf('[%s] %s | NOTHING TO SEND', $dateTime->format('Y-m-d H:i:s'), $user->getEmail())
            );
        }

        return 0;
    }
}
