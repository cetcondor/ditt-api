<?php

namespace App\Listener;

use App\Event\WorkMonthApprovedEvent;
use App\Exception\EmailNotSentException;
use App\Repository\UserRepository;

class WorkMonthApprovedEmailNotificationListener
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
     * @throws \Exception
     */
    public function onApprove(WorkMonthApprovedEvent $event): void
    {
        $workMonth = $event->getWorkMonth();
        $supervisor = $event->getSupervisor();
        $admins = $this->userRepository->getAllAdmins();
        $toEmails = [$workMonth->getUser()->getEmail()];
        foreach ($admins as $admin) {
            $toEmails[] = $admin->getEmail();
        }

        if ($supervisor && !in_array($supervisor->getEmail(), $toEmails)) {
            $toEmails[] = $supervisor->getEmail();
        }

        $this->sendMail(
            sprintf(
                'Arbeitsmonat angenommen – %d/%d',
                $workMonth->getMonth(),
                $workMonth->getYear()->getYear()
            ),
            $toEmails,
            $this->templating->render('notifications/work_month_approved.html.twig', [
                'supervisor' => $supervisor,
                'workMonth' => $workMonth,
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
