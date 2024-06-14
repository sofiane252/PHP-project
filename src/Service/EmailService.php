<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private $mailer;
    private $senderEmail;
    private $senderName;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(string $to, string $subject, string $text): void
    {
        $email = (new Email())
            ->from("cocachips180@gmail.com")
            ->to("cocachips180@gmail.com")
            ->subject($subject)
            ->text($text);

        $this->mailer->send($email);
    }

    public function sendSubscriptionConfirmationEmail(string $to, string $eventName): void
    {
        $subject = 'Confirmation d\'inscription à l\'événement';
        $text = sprintf('Vous êtes maintenant inscrit à l\'événement "%s".', $eventName);
        $this->sendEmail($to, $subject, $text);
    }

    public function sendUnsubscriptionConfirmationEmail(string $to, string $eventName): void
    {
        $subject = 'Confirmation de désinscription à l\'événement';
        $text = sprintf('Vous vous êtes désinscrit de l\'événement "%s".', $eventName);
        $this->sendEmail($to, $subject, $text);
    }
}
