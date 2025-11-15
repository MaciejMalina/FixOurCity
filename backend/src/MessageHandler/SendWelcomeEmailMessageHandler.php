<?php

namespace App\MessageHandler;

use App\Message\SendWelcomeEmailMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class SendWelcomeEmailMessageHandler
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function __invoke(SendWelcomeEmailMessage $message): void
    {
        $email = (new Email())
            ->from(new Address('macmalonet783@gmail.com', 'FixOurCity Support'))
            ->to($message->getEmail())
            ->subject('Witaj w FixOurCity!')
            ->text("Cześć,\n\nDziękujemy za rejestrację w FixOurCity.\n\nPozdrawiamy,\nZespół FixOurCity")
            ->html(<<<HTML
<p>Cześć,</p>
<p>Dziękujemy za rejestrację w <strong>FixOurCity</strong>.</p>
<p>Możesz teraz zalogować się do systemu i korzystać z aplikacji.</p>
<p>Pozdrawiamy,<br/>Zespół FixOurCity</p>
HTML);

        $this->mailer->send($email);
    }
}
