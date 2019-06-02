<?php

namespace App\Listener;

use App\Entity\User;
use App\Entity\WorkLogInterface;
use App\Event\BusinessTripWorkLogApprovedEvent;
use App\Event\BusinessTripWorkLogCanceledEvent;
use App\Event\BusinessTripWorkLogRejectedEvent;
use App\Event\HomeOfficeWorkLogApprovedEvent;
use App\Event\HomeOfficeWorkLogCanceledEvent;
use App\Event\HomeOfficeWorkLogRejectedEvent;
use App\Event\MultipleVacationWorkLogApprovedEvent;
use App\Event\MultipleVacationWorkLogRejectedEvent;
use App\Event\OvertimeWorkLogApprovedEvent;
use App\Event\OvertimeWorkLogCanceledEvent;
use App\Event\OvertimeWorkLogRejectedEvent;
use App\Event\TimeOffWorkLogApprovedEvent;
use App\Event\TimeOffWorkLogCanceledEvent;
use App\Event\TimeOffWorkLogRejectedEvent;
use App\Event\VacationWorkLogApprovedEvent;
use App\Event\VacationWorkLogCanceledEvent;
use App\Event\VacationWorkLogRejectedEvent;
use App\Exception\EmailNotSentException;
use App\Repository\UserRepository;

class WorkLogEmailNotificationListener
{
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
     * @var \Twig_Environment
     */
    private $templating;

    /**
     * @param \App\Repository\UserRepository $userRepository
     * @param \Swift_Mailer $mailer
     * @param string $mailSenderAddress
     * @param \Twig_Environment $templating
     */
    public function __construct(
        UserRepository $userRepository,
        \Swift_Mailer $mailer,
        $mailSenderAddress,
        \Twig_Environment $templating
    ) {
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        $this->mailSenderAddress = $mailSenderAddress;
        $this->templating = $templating;
    }

    /**
     * @param BusinessTripWorkLogApprovedEvent $event
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onBusinessTripWorkLogApproved(BusinessTripWorkLogApprovedEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getBusinessTripWorkLog(),
            'Antrag auf Dienstreise gewährt – %s',
            $event->getBusinessTripWorkLog()->getDate(),
            'notifications/business_trip_work_log_approved.html.twig'
        );
    }

    /**
     * @param BusinessTripWorkLogCanceledEvent $event
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onBusinessTripWorkLogCanceled(BusinessTripWorkLogCanceledEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getBusinessTripWorkLog(),
            'Antrag auf Dienstreise storniert – %s',
            $event->getBusinessTripWorkLog()->getDate(),
            'notifications/business_trip_work_log_canceled.html.twig'
        );
    }

    /**
     * @param BusinessTripWorkLogRejectedEvent $event
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onBusinessTripWorkLogRejected(BusinessTripWorkLogRejectedEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getBusinessTripWorkLog(),
            'Antrag auf Dienstreise abgelehnt – %s',
            $event->getBusinessTripWorkLog()->getDate(),
            'notifications/business_trip_work_log_rejected.html.twig'
        );
    }

    /**
     * @param HomeOfficeWorkLogApprovedEvent $event
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onHomeOfficeWorkLogApproved(HomeOfficeWorkLogApprovedEvent $event)
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getHomeOfficeWorkLog(),
            'Antrag auf Mobile Arbeit gewährt – %s',
            $event->getHomeOfficeWorkLog()->getDate(),
            'notifications/home_office_work_log_approved.html.twig'
        );
    }

    /**
     * @param HomeOfficeWorkLogCanceledEvent $event
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onHomeOfficeWorkLogCanceled(HomeOfficeWorkLogCanceledEvent $event)
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getHomeOfficeWorkLog(),
            'Antrag auf Mobile Arbeit storniert – %s',
            $event->getHomeOfficeWorkLog()->getDate(),
            'notifications/home_office_work_log_canceled.html.twig'
        );
    }

    /**
     * @param HomeOfficeWorkLogRejectedEvent $event
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onHomeOfficeWorkLogRejected(HomeOfficeWorkLogRejectedEvent $event)
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getHomeOfficeWorkLog(),
            'Antrag auf Mobile Arbeit abgelehnt – %s',
            $event->getHomeOfficeWorkLog()->getDate(),
            'notifications/home_office_work_log_rejected.html.twig'
        );
    }

    /**
     * @param MultipleVacationWorkLogApprovedEvent $event
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onMultipleVacationWorkLogApproved(MultipleVacationWorkLogApprovedEvent $event)
    {
        $workLogs = $event->getVacationWorkLogs();
        $startDate = $workLogs[0]->getDate();
        $endDate = $workLogs[0]->getDate();

        if (count($workLogs) > 1) {
            $endDate = end($workLogs)->getDate();
        }

        $this->sendWorkLogsMail(
            $event->getSupervisor(),
            $workLogs,
            'Antrag auf mehrtägigen Urlaub gewährt – %s bis %s',
            $startDate,
            $endDate,
            'notifications/multiple_vacation_work_log_approved.html.twig'
        );
    }

    /**
     * @param MultipleVacationWorkLogRejectedEvent $event
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onMultipleVacationWorkLogRejected(MultipleVacationWorkLogRejectedEvent $event)
    {
        $workLogs = $event->getVacationWorkLogs();
        $startDate = $workLogs[0]->getDate();
        $endDate = $workLogs[0]->getDate();

        if (count($workLogs) > 1) {
            $endDate = end($workLogs)->getDate();
        }

        $this->sendWorkLogsMail(
            $event->getSupervisor(),
            $workLogs,
            'Antrag auf mehrtägigen Urlaub abgelehnt – %s bis %s',
            $startDate,
            $endDate,
            'notifications/multiple_vacation_work_log_rejected.html.twig'
        );
    }

    /**
     * @param OvertimeWorkLogApprovedEvent $event
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onOvertimeWorkLogApproved(OvertimeWorkLogApprovedEvent $event)
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getOvertimeWorkLog(),
            'Antrag auf Mehrarbeit gewährt – %s',
            $event->getOvertimeWorkLog()->getDate(),
            'notifications/overtime_work_log_approved.html.twig'
        );
    }

    /**
     * @param OvertimeWorkLogCanceledEvent $event
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onOvertimeWorkLogCanceled(OvertimeWorkLogCanceledEvent $event)
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getOvertimeWorkLog(),
            'Antrag auf Mehrarbeit storniert – %s',
            $event->getOvertimeWorkLog()->getDate(),
            'notifications/overtime_work_log_canceled.html.twig'
        );
    }

    /**
     * @param OvertimeWorkLogRejectedEvent $event
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onOvertimeWorkLogRejected(OvertimeWorkLogRejectedEvent $event)
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getOvertimeWorkLog(),
            'Antrag auf Mehrarbeit abgelehnt – %s',
            $event->getOvertimeWorkLog()->getDate(),
            'notifications/overtime_work_log_rejected.html.twig'
        );
    }

    /**
     * @param TimeOffWorkLogApprovedEvent $event
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onTimeOffWorkLogApproved(TimeOffWorkLogApprovedEvent $event)
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getTimeOffWorkLog(),
            'Antrag auf Freizeitausgleich gewährt – %s',
            $event->getTimeOffWorkLog()->getDate(),
            'notifications/time_off_work_log_approved.html.twig'
        );
    }

    /**
     * @param TimeOffWorkLogCanceledEvent $event
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onTimeOffWorkLogCanceled(TimeOffWorkLogCanceledEvent $event)
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getTimeOffWorkLog(),
            'Antrag auf Freizeitausgleich storniert – %s',
            $event->getTimeOffWorkLog()->getDate(),
            'notifications/time_off_work_log_canceled.html.twig'
        );
    }

    /**
     * @param TimeOffWorkLogRejectedEvent $event
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onTimeOffWorkLogRejected(TimeOffWorkLogRejectedEvent $event)
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getTimeOffWorkLog(),
            'Antrag auf Freizeitausgleich abgelehnt – %s',
            $event->getTimeOffWorkLog()->getDate(),
            'notifications/time_off_work_log_rejected.html.twig'
        );
    }

    /**
     * @param VacationWorkLogApprovedEvent $event
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onVacationWorkLogApproved(VacationWorkLogApprovedEvent $event)
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getVacationWorkLog(),
            'Antrag auf Urlaub gewährt – %s',
            $event->getVacationWorkLog()->getDate(),
            'notifications/vacation_work_log_approved.html.twig'
        );
    }

    /**
     * @param VacationWorkLogCanceledEvent $event
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onVacationWorkLogCanceled(VacationWorkLogCanceledEvent $event)
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getVacationWorkLog(),
            'Antrag auf Urlaub storniert – %s',
            $event->getVacationWorkLog()->getDate(),
            'notifications/vacation_work_log_canceled.html.twig'
        );
    }

    /**
     * @param VacationWorkLogRejectedEvent $event
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function onVacationWorkLogRejected(VacationWorkLogRejectedEvent $event)
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getVacationWorkLog(),
            'Antrag auf Urlaub abgelehnt – %s',
            $event->getVacationWorkLog()->getDate(),
            'notifications/vacation_work_log_rejected.html.twig'
        );
    }

    /**
     * @param User|null $supervisor
     * @param WorkLogInterface $workLog
     * @param string $emailSubject
     * @param \DateTimeImmutable|null $date
     * @param string $emailTemplate
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function sendWorkLogMail(
        ?User $supervisor,
        WorkLogInterface $workLog,
        string $emailSubject,
        ?\DateTimeImmutable $date,
        string $emailTemplate
    ) {
        $admins = $this->userRepository->getAllAdmins();
        $toEmails = [$workLog->getWorkMonth()->getUser()->getEmail()];
        foreach ($admins as $admin) {
            $toEmails[] = $admin->getEmail();
        }

        if ($supervisor && !in_array($supervisor->getEmail(), $toEmails)) {
            $toEmails[] = $supervisor->getEmail();
        }

        $subjectDate = '';
        if ($date) {
            $subjectDate = $date->format('m.d.');
        }

        $this->sendMail(
            sprintf($emailSubject, $subjectDate),
            $toEmails,
            $this->templating->render($emailTemplate, [
                'supervisor' => $supervisor,
                'workLog' => $workLog,
            ])
        );
    }

    /**
     * @param User|null $supervisor
     * @param array $workLogs
     * @param string $emailSubject
     * @param \DateTimeImmutable|null $startDate
     * @param \DateTimeImmutable|null $endDate
     * @param string $emailTemplate
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function sendWorkLogsMail(
        ?User $supervisor,
        array $workLogs,
        string $emailSubject,
        ?\DateTimeImmutable $startDate,
        ?\DateTimeImmutable $endDate,
        string $emailTemplate
    ) {
        $admins = $this->userRepository->getAllAdmins();
        $toEmails = [$workLogs[0]->getWorkMonth()->getUser()->getEmail()];
        foreach ($admins as $admin) {
            $toEmails[] = $admin->getEmail();
        }

        if ($supervisor && !in_array($supervisor->getEmail(), $toEmails)) {
            $toEmails[] = $supervisor->getEmail();
        }

        $subjectStartDate = '';
        $subjectEndDate = '';
        if ($startDate) {
            $subjectStartDate = $startDate->format('mm.DD.');
        }
        if ($endDate) {
            $subjectEndDate = $endDate->format('mm.DD.');
        }

        $this->sendMail(
            sprintf($emailSubject, $subjectStartDate, $subjectEndDate),
            $toEmails,
            $this->templating->render($emailTemplate, [
                'supervisor' => $supervisor,
                'workLogs' => $workLogs,
            ])
        );
    }

    /**
     * @param string $subject
     * @param array $toEmail
     * @param string $htmlContent
     * @throws EmailNotSentException
     */
    private function sendMail($subject, array $toEmail, $htmlContent): void
    {
        $message = (new \Swift_Message())
            ->setSubject($subject)
            ->setFrom([$this->mailSenderAddress => $this->mailSenderAddress])
            ->setTo($toEmail)
            ->setBody($htmlContent, 'text/html')
            ->addPart((new \Html2Text\Html2Text($htmlContent))->getText(), 'text/plain');

        if (count($toEmail) && !$this->mailer->send($message)) {
            throw new EmailNotSentException(sprintf('Some e-mail notifications were not sent.'));
        }
    }
}
