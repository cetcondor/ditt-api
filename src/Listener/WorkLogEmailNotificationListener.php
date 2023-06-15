<?php

namespace App\Listener;

use App\Entity\BusinessTripWorkLog;
use App\Entity\HomeOfficeWorkLog;
use App\Entity\OvertimeWorkLog;
use App\Entity\SpecialLeaveWorkLog;
use App\Entity\TimeOffWorkLog;
use App\Entity\TrainingWorkLog;
use App\Entity\User;
use App\Entity\VacationWorkLog;
use App\Entity\WorkLogInterface;
use App\Event\BusinessTripWorkLogApprovedEvent;
use App\Event\BusinessTripWorkLogCanceledEvent;
use App\Event\BusinessTripWorkLogRejectedEvent;
use App\Event\HomeOfficeWorkLogApprovedEvent;
use App\Event\HomeOfficeWorkLogCanceledEvent;
use App\Event\HomeOfficeWorkLogRejectedEvent;
use App\Event\MultipleBusinessTripWorkLogApprovedEvent;
use App\Event\MultipleBusinessTripWorkLogRejectedEvent;
use App\Event\MultipleHomeOfficeWorkLogApprovedEvent;
use App\Event\MultipleHomeOfficeWorkLogRejectedEvent;
use App\Event\MultipleOvertimeWorkLogApprovedEvent;
use App\Event\MultipleOvertimeWorkLogRejectedEvent;
use App\Event\MultipleSickDayWorkLogCreatedEvent;
use App\Event\MultipleSpecialLeaveWorkLogApprovedEvent;
use App\Event\MultipleSpecialLeaveWorkLogRejectedEvent;
use App\Event\MultipleTimeOffWorkLogApprovedEvent;
use App\Event\MultipleTimeOffWorkLogRejectedEvent;
use App\Event\MultipleTrainingWorkLogApprovedEvent;
use App\Event\MultipleTrainingWorkLogRejectedEvent;
use App\Event\MultipleVacationWorkLogApprovedEvent;
use App\Event\MultipleVacationWorkLogRejectedEvent;
use App\Event\OvertimeWorkLogApprovedEvent;
use App\Event\OvertimeWorkLogCanceledEvent;
use App\Event\OvertimeWorkLogRejectedEvent;
use App\Event\SickDayWorkLogCreatedEvent;
use App\Event\SpecialLeaveWorkLogApprovedEvent;
use App\Event\SpecialLeaveWorkLogCanceledEvent;
use App\Event\SpecialLeaveWorkLogRejectedEvent;
use App\Event\TimeOffWorkLogApprovedEvent;
use App\Event\TimeOffWorkLogCanceledEvent;
use App\Event\TimeOffWorkLogRejectedEvent;
use App\Event\TrainingWorkLogApprovedEvent;
use App\Event\TrainingWorkLogCanceledEvent;
use App\Event\TrainingWorkLogRejectedEvent;
use App\Event\VacationWorkLogApprovedEvent;
use App\Event\VacationWorkLogCanceledEvent;
use App\Event\VacationWorkLogRejectedEvent;
use App\Exception\EmailNotSentException;
use App\Repository\UserRepository;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

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
     * @var \Twig\Environment
     */
    private $templating;

    /**
     * @param string $mailSenderAddress
     */
    public function __construct(
        UserRepository $userRepository,
        \Swift_Mailer $mailer,
        $mailSenderAddress,
        \Twig\Environment $templating
    ) {
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        $this->mailSenderAddress = $mailSenderAddress;
        $this->templating = $templating;
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onBusinessTripWorkLogApproved(BusinessTripWorkLogApprovedEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Dienstreise gewährt – %s',
            $event->getWorkLog()->getDate(),
            'notifications/business_trip_work_log_approved.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onBusinessTripWorkLogCanceled(BusinessTripWorkLogCanceledEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Dienstreise storniert – %s',
            $event->getWorkLog()->getDate(),
            'notifications/business_trip_work_log_canceled.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onBusinessTripWorkLogRejected(BusinessTripWorkLogRejectedEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Dienstreise abgelehnt – %s',
            $event->getWorkLog()->getDate(),
            'notifications/business_trip_work_log_rejected.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onHomeOfficeWorkLogApproved(HomeOfficeWorkLogApprovedEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Mobile Arbeit gewährt – %s',
            $event->getWorkLog()->getDate(),
            'notifications/home_office_work_log_approved.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onHomeOfficeWorkLogCanceled(HomeOfficeWorkLogCanceledEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Mobile Arbeit storniert – %s',
            $event->getWorkLog()->getDate(),
            'notifications/home_office_work_log_canceled.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onHomeOfficeWorkLogRejected(HomeOfficeWorkLogRejectedEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Mobile Arbeit abgelehnt – %s',
            $event->getWorkLog()->getDate(),
            'notifications/home_office_work_log_rejected.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onMultipleBusinessTripWorkLogApproved(MultipleBusinessTripWorkLogApprovedEvent $event): void
    {
        $workLogs = $event->getWorkLogs();
        $startDate = $workLogs[0]->getDate();
        $endDate = $workLogs[0]->getDate();

        if (count($workLogs) > 1 && end($workLogs) instanceof BusinessTripWorkLog) {
            $endDate = end($workLogs)->getDate();
        }

        $this->sendWorkLogsMail(
            $event->getSupervisor(),
            $workLogs,
            'Antrag auf mehrtägigen Dienstreise gewährt – %s bis %s',
            $startDate,
            $endDate,
            'notifications/multiple_business_trip_work_log_approved.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onMultipleBusinessTripWorkLogRejected(MultipleBusinessTripWorkLogRejectedEvent $event): void
    {
        $workLogs = $event->getWorkLogs();
        $startDate = $workLogs[0]->getDate();
        $endDate = $workLogs[0]->getDate();

        if (count($workLogs) > 1 && end($workLogs) instanceof BusinessTripWorkLog) {
            $endDate = end($workLogs)->getDate();
        }

        $this->sendWorkLogsMail(
            $event->getSupervisor(),
            $workLogs,
            'Antrag auf mehrtägigen Dienstreise abgelehnt – %s bis %s',
            $startDate,
            $endDate,
            'notifications/multiple_business_trip_work_log_rejected.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onMultipleHomeOfficeWorkLogApproved(MultipleHomeOfficeWorkLogApprovedEvent $event): void
    {
        $workLogs = $event->getWorkLogs();
        $startDate = $workLogs[0]->getDate();
        $endDate = $workLogs[0]->getDate();

        if (count($workLogs) > 1 && end($workLogs) instanceof HomeOfficeWorkLog) {
            $endDate = end($workLogs)->getDate();
        }

        $this->sendWorkLogsMail(
            $event->getSupervisor(),
            $workLogs,
            'Antrag auf mehrtägigen Mobile Arbeit gewährt – %s bis %s',
            $startDate,
            $endDate,
            'notifications/multiple_home_office_work_log_approved.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onMultipleHomeOfficeWorkLogRejected(MultipleHomeOfficeWorkLogRejectedEvent $event): void
    {
        $workLogs = $event->getWorkLogs();
        $startDate = $workLogs[0]->getDate();
        $endDate = $workLogs[0]->getDate();

        if (count($workLogs) > 1 && end($workLogs) instanceof HomeOfficeWorkLog) {
            $endDate = end($workLogs)->getDate();
        }

        $this->sendWorkLogsMail(
            $event->getSupervisor(),
            $workLogs,
            'Antrag auf mehrtägigen Mobile Arbeit abgelehnt – %s bis %s',
            $startDate,
            $endDate,
            'notifications/multiple_home_office_work_log_rejected.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onMultipleOvertimeWorkLogApproved(MultipleOvertimeWorkLogApprovedEvent $event): void
    {
        $workLogs = $event->getWorkLogs();
        $startDate = $workLogs[0]->getDate();
        $endDate = $workLogs[0]->getDate();

        if (count($workLogs) > 1 && end($workLogs) instanceof OvertimeWorkLog) {
            $endDate = end($workLogs)->getDate();
        }

        $this->sendWorkLogsMail(
            $event->getSupervisor(),
            $workLogs,
            'Antrag auf mehrtägigen Mehrarbeit gewährt – %s bis %s',
            $startDate,
            $endDate,
            'notifications/multiple_overtime_work_log_approved.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onMultipleOvertimeWorkLogRejected(MultipleOvertimeWorkLogRejectedEvent $event): void
    {
        $workLogs = $event->getWorkLogs();
        $startDate = $workLogs[0]->getDate();
        $endDate = $workLogs[0]->getDate();

        if (count($workLogs) > 1 && end($workLogs) instanceof OvertimeWorkLog) {
            $endDate = end($workLogs)->getDate();
        }

        $this->sendWorkLogsMail(
            $event->getSupervisor(),
            $workLogs,
            'Antrag auf mehrtägigen Mehrarbeit abgelehnt – %s bis %s',
            $startDate,
            $endDate,
            'notifications/multiple_overtime_work_log_rejected.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onMultipleSickDayWorkLogCreated(MultipleSickDayWorkLogCreatedEvent $event): void
    {
        $workLogs = $event->getWorkLogs();
        $startDate = $workLogs[0]->getDate();
        $endDate = $workLogs[0]->getDate();

        if (count($workLogs) > 1 && end($workLogs) instanceof SpecialLeaveWorkLog) {
            $endDate = end($workLogs)->getDate();
        }

        $this->sendWorkLogsMail(
            $event->getSupervisor(),
            $workLogs,
            'Krankmeldung – %s bis %s',
            $startDate,
            $endDate,
            'notifications/multiple_sick_day_work_log_created.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onMultipleSpecialLeaveWorkLogApproved(MultipleSpecialLeaveWorkLogApprovedEvent $event): void
    {
        $workLogs = $event->getWorkLogs();
        $startDate = $workLogs[0]->getDate();
        $endDate = $workLogs[0]->getDate();

        if (count($workLogs) > 1 && end($workLogs) instanceof SpecialLeaveWorkLog) {
            $endDate = end($workLogs)->getDate();
        }

        $this->sendWorkLogsMail(
            $event->getSupervisor(),
            $workLogs,
            'Antrag auf mehrtägigen Sonderurlaub gewährt – %s bis %s',
            $startDate,
            $endDate,
            'notifications/multiple_special_leave_work_log_approved.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onMultipleSpecialLeaveWorkLogRejected(MultipleSpecialLeaveWorkLogRejectedEvent $event): void
    {
        $workLogs = $event->getWorkLogs();
        $startDate = $workLogs[0]->getDate();
        $endDate = $workLogs[0]->getDate();

        if (count($workLogs) > 1 && end($workLogs) instanceof SpecialLeaveWorkLog) {
            $endDate = end($workLogs)->getDate();
        }

        $this->sendWorkLogsMail(
            $event->getSupervisor(),
            $workLogs,
            'Antrag auf mehrtägigen Sonderurlaub abgelehnt – %s bis %s',
            $startDate,
            $endDate,
            'notifications/multiple_special_leave_work_log_rejected.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onMultipleTimeOffWorkLogApproved(MultipleTimeOffWorkLogApprovedEvent $event): void
    {
        $workLogs = $event->getWorkLogs();
        $startDate = $workLogs[0]->getDate();
        $endDate = $workLogs[0]->getDate();

        if (count($workLogs) > 1 && end($workLogs) instanceof TimeOffWorkLog) {
            $endDate = end($workLogs)->getDate();
        }

        $this->sendWorkLogsMail(
            $event->getSupervisor(),
            $workLogs,
            'Antrag auf mehrtägigen Freizeitausgleich – %s bis %s',
            $startDate,
            $endDate,
            'notifications/multiple_time_off_work_log_approved.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onMultipleTimeOffWorkLogRejected(MultipleTimeOffWorkLogRejectedEvent $event): void
    {
        $workLogs = $event->getWorkLogs();
        $startDate = $workLogs[0]->getDate();
        $endDate = $workLogs[0]->getDate();

        if (count($workLogs) > 1 && end($workLogs) instanceof TimeOffWorkLog) {
            $endDate = end($workLogs)->getDate();
        }

        $this->sendWorkLogsMail(
            $event->getSupervisor(),
            $workLogs,
            'Antrag auf mehrtägigen Freizeitausgleich abgelehnt – %s bis %s',
            $startDate,
            $endDate,
            'notifications/multiple_time_off_work_log_rejected.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onMultipleTrainingWorkLogApproved(MultipleTrainingWorkLogApprovedEvent $event): void
    {
        $workLogs = $event->getWorkLogs();
        $startDate = $workLogs[0]->getDate();
        $endDate = $workLogs[0]->getDate();

        if (count($workLogs) > 1 && end($workLogs) instanceof TrainingWorkLog) {
            $endDate = end($workLogs)->getDate();
        }

        $this->sendWorkLogsMail(
            $event->getSupervisor(),
            $workLogs,
            'Antrag auf mehrtägigen Fortbildung gewährt – %s bis %s',
            $startDate,
            $endDate,
            'notifications/multiple_training_work_log_approved.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onMultipleTrainingWorkLogRejected(MultipleTrainingWorkLogRejectedEvent $event): void
    {
        $workLogs = $event->getWorkLogs();
        $startDate = $workLogs[0]->getDate();
        $endDate = $workLogs[0]->getDate();

        if (count($workLogs) > 1 && end($workLogs) instanceof TrainingWorkLog) {
            $endDate = end($workLogs)->getDate();
        }

        $this->sendWorkLogsMail(
            $event->getSupervisor(),
            $workLogs,
            'Antrag auf mehrtägigen Fortbildung abgelehnt – %s bis %s',
            $startDate,
            $endDate,
            'notifications/multiple_training_work_log_rejected.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onMultipleVacationWorkLogApproved(MultipleVacationWorkLogApprovedEvent $event): void
    {
        $workLogs = $event->getWorkLogs();
        $startDate = $workLogs[0]->getDate();
        $endDate = $workLogs[0]->getDate();

        if (count($workLogs) > 1 && end($workLogs) instanceof VacationWorkLog) {
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
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onMultipleVacationWorkLogRejected(MultipleVacationWorkLogRejectedEvent $event): void
    {
        $workLogs = $event->getWorkLogs();
        $startDate = $workLogs[0]->getDate();
        $endDate = $workLogs[0]->getDate();

        if (count($workLogs) > 1 && end($workLogs) instanceof VacationWorkLog) {
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
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onOvertimeWorkLogApproved(OvertimeWorkLogApprovedEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Mehrarbeit gewährt – %s',
            $event->getWorkLog()->getDate(),
            'notifications/overtime_work_log_approved.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onOvertimeWorkLogCanceled(OvertimeWorkLogCanceledEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Mehrarbeit storniert – %s',
            $event->getWorkLog()->getDate(),
            'notifications/overtime_work_log_canceled.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onOvertimeWorkLogRejected(OvertimeWorkLogRejectedEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Mehrarbeit abgelehnt – %s',
            $event->getWorkLog()->getDate(),
            'notifications/overtime_work_log_rejected.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onSickDayWorkLogCreated(SickDayWorkLogCreatedEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Krankmeldung – %s',
            $event->getWorkLog()->getDate(),
            'notifications/sick_day_work_log_created.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onSpecialLeaveWorkLogApproved(SpecialLeaveWorkLogApprovedEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Sonderurlaub gewährt – %s',
            $event->getWorkLog()->getDate(),
            'notifications/special_leave_work_log_approved.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onSpecialLeaveWorkLogCanceled(SpecialLeaveWorkLogCanceledEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Sonderurlaub storniert – %s',
            $event->getWorkLog()->getDate(),
            'notifications/special_leave_work_log_canceled.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onSpecialLeaveWorkLogRejected(SpecialLeaveWorkLogRejectedEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Sonderurlaub abgelehnt – %s',
            $event->getWorkLog()->getDate(),
            'notifications/special_leave_work_log_rejected.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onTimeOffWorkLogApproved(TimeOffWorkLogApprovedEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Freizeitausgleich gewährt – %s',
            $event->getWorkLog()->getDate(),
            'notifications/time_off_work_log_approved.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onTimeOffWorkLogCanceled(TimeOffWorkLogCanceledEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Freizeitausgleich storniert – %s',
            $event->getWorkLog()->getDate(),
            'notifications/time_off_work_log_canceled.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onTimeOffWorkLogRejected(TimeOffWorkLogRejectedEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Freizeitausgleich abgelehnt – %s',
            $event->getWorkLog()->getDate(),
            'notifications/time_off_work_log_rejected.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onTrainingWorkLogApproved(TrainingWorkLogApprovedEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Fortbildung gewährt – %s',
            $event->getWorkLog()->getDate(),
            'notifications/training_work_log_approved.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onTrainingWorkLogCanceled(TrainingWorkLogCanceledEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Fortbildung storniert – %s',
            $event->getWorkLog()->getDate(),
            'notifications/training_work_log_canceled.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onTrainingWorkLogRejected(TrainingWorkLogRejectedEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Fortbildung abgelehnt – %s',
            $event->getWorkLog()->getDate(),
            'notifications/training_work_log_rejected.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onVacationWorkLogApproved(VacationWorkLogApprovedEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Urlaub gewährt – %s',
            $event->getWorkLog()->getDate(),
            'notifications/vacation_work_log_approved.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onVacationWorkLogCanceled(VacationWorkLogCanceledEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Urlaub storniert – %s',
            $event->getWorkLog()->getDate(),
            'notifications/vacation_work_log_canceled.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onVacationWorkLogRejected(VacationWorkLogRejectedEvent $event): void
    {
        $this->sendWorkLogMail(
            $event->getSupervisor(),
            $event->getWorkLog(),
            'Antrag auf Urlaub abgelehnt – %s',
            $event->getWorkLog()->getDate(),
            'notifications/vacation_work_log_rejected.html.twig'
        );
    }

    /**
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function sendWorkLogMail(
        ?User $supervisor,
        WorkLogInterface $workLog,
        string $emailSubject,
        ?\DateTimeImmutable $date,
        string $emailTemplate
    ): void {
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
            $subjectDate = $date->format('d.m.Y');
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
     * @throws EmailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function sendWorkLogsMail(
        ?User $supervisor,
        array $workLogs,
        string $emailSubject,
        ?\DateTimeImmutable $startDate,
        ?\DateTimeImmutable $endDate,
        string $emailTemplate
    ): void {
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
            $subjectStartDate = $startDate->format('d.m.Y');
        }
        if ($endDate) {
            $subjectEndDate = $endDate->format('d.m.Y');
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
