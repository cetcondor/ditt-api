<?php

namespace App\Listener;

use App\Entity\User;
use App\Entity\WorkLogInterface;
use App\Event\BusinessTripWorkLogApprovedEvent;
use App\Event\BusinessTripWorkLogRejectedEvent;
use App\Event\HomeOfficeWorkLogApprovedEvent;
use App\Event\HomeOfficeWorkLogRejectedEvent;
use App\Event\TimeOffWorkLogApprovedEvent;
use App\Event\TimeOffWorkLogRejectedEvent;
use App\Event\VacationWorkLogApprovedEvent;
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
            'Business trip work log was approved by %s %s',
            'notifications/business_trip_work_log_approved.html.twig'
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
            'Business trip work log was rejected by %s %s',
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
            'Home office work log was approved by %s %s',
            'notifications/home_office_work_log_approved.html.twig'
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
            'Home office work log was rejected by %s %s',
            'notifications/home_office_work_log_rejected.html.twig'
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
            'Time off work log was approved by %s %s',
            'notifications/time_off_work_log_approved.html.twig'
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
            'Time off work log was rejected by %s %s',
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
            'Vacation work log was approved by %s %s',
            'notifications/vacation_work_log_approved.html.twig'
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
            'Vacation work log was rejected by %s %s',
            'notifications/vacation_work_log_rejected.html.twig'
        );
    }

    /**
     * @param User $supervisor
     * @param WorkLogInterface $workLog
     * @param string $emailSubject
     * @param string $emailTemplate
     * @throws EmailNotSentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function sendWorkLogMail(
        User $supervisor,
        WorkLogInterface $workLog,
        string $emailSubject,
        string $emailTemplate
    ) {
        $admins = $this->userRepository->getAllAdmins();
        $toEmails = [$workLog->getWorkMonth()->getUser()->getEmail()];
        foreach ($admins as $admin) {
            $toEmails[] = $admin->getEmail();
        }

        $this->sendMail(
            sprintf($emailSubject, $supervisor->getFirstName(), $supervisor->getLastName()),
            $toEmails,
            $this->templating->render($emailTemplate, [
                'supervisor' => $supervisor,
                'workLog' => $workLog,
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
