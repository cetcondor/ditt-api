<?php

namespace App\Listener;

use App\Event\UserPasswordResetEvent;
use App\Exception\EmailNotSentException;

class UserPasswordResetEmailNotificationListener
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
    public function onReset(UserPasswordResetEvent $event): void
    {
        $user = $event->getUser();

        $this->sendMail(
            'Passwort zurücksetzen',
            [$event->getUser()->getEmail()],
            $this->templating->render('notifications/password_reset.html.twig', [
                'user' => $user,
                'url' => sprintf($this->clientNewPasswordUrl, $user->getResetPasswordToken()),
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
