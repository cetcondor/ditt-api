<?php

namespace App\Listener;

use App\Event\UserChangedEvent;
use App\Event\WorkMonthWorkTimeCorrectionChangedEvent;
use App\Exception\EmailNotSentException;

class ChangesNotificationListener
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $mailSenderAddress;

    /**
     * @var string
     */
    private $clientNewPasswordUrl;

    /**
     * @var \Twig\Environment
     */
    private $templating;

    public function __construct(
        \Swift_Mailer $mailer,
        string $mailSenderAddress,
        string $clientNewPasswordUrl,
        \Twig\Environment $templating
    ) {
        $this->mailer = $mailer;
        $this->mailSenderAddress = $mailSenderAddress;
        $this->clientNewPasswordUrl = $clientNewPasswordUrl;
        $this->templating = $templating;
    }

    /**
     * @throws \Exception
     */
    public function onUserChangedEvent(UserChangedEvent $event): void
    {
        if ($event->getDidVacationsChanged()) {
            $this->sendMail(
                'Anzahl der Urlaubstage wurden angepasst',
                [$event->getUser()->getEmail()],
                $this->templating->render('notifications/amount_of_vacation_days_changed.html.twig')
            );
        }

        if ($event->getDidWorkHoursChanged()) {
            $this->sendMail(
                'Arbeitszeit wurde angepasst',
                [$event->getUser()->getEmail()],
                $this->templating->render('notifications/work_hours_changed.html.twig')
            );
        }
    }

    /**
     * @throws \Exception
     */
    public function onWorkMonthWorkTimeCorrectionChangedEvent(WorkMonthWorkTimeCorrectionChangedEvent $event): void
    {
        $this->sendMail(
            sprintf(
                'Arbeitszeit für %d/%d korrigiert',
                $event->getWorkMonth()->getMonth(),
                $event->getWorkMonth()->getYear()->getYear()
            ),
            [$event->getWorkMonth()->getUser()->getEmail()],
            $this->templating->render(
                'notifications/work_month_work_time_correction_changed.html.twig',
                ['workMonth' => $event->getWorkMonth()]
            )
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
