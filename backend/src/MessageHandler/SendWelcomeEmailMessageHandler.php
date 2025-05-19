<?php

namespace App\MessageHandler;

use App\Message\SendWelcomeEmailMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SendWelcomeEmailMessageHandler
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(SendWelcomeEmailMessage $message): void
    {
        $email = (new Email())
            ->from('no-reply@fixourcity.com')
            ->to($message->getEmail())
            ->subject('Witaj w FixOurCity!')
            ->text("Cześć,\n\nDziękujemy za rejestrację w FixOurCity.");
        ;

        $this->mailer->send($email);
    }
}
