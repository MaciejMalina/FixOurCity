<?php

namespace App\MessageHandler;

use App\Message\AdminNewUserRegisteredMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class AdminNewUserRegisteredMessageHandler
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function __invoke(AdminNewUserRegisteredMessage $msg): void
    {
        $adminEmails = [
            'macmalonet783@gmail.com',
        ];

        $email = (new Email())
            ->from(new Address('macmalonet783@gmail.com', 'FixOurCity System'))
            ->to(...$adminEmails)
            ->subject('Nowy użytkownik oczekuje na zatwierdzenie')
            ->text(
                "Nowy użytkownik zarejestrował się w FixOurCity:\n".
                "ID: {$msg->getUserId()}\n".
                "Email: {$msg->getEmail()}\n".
                "Imię i nazwisko: {$msg->getFirstName()} {$msg->getLastName()}\n\n".
                "Zaloguj się do panelu administratora, aby zatwierdzić konto."
            )
            ->html(<<<HTML
<p>Nowy użytkownik zarejestrował się w <strong>FixOurCity</strong>:</p>
<ul>
  <li><strong>ID:</strong> {$msg->getUserId()}</li>
  <li><strong>Email:</strong> {$msg->getEmail()}</li>
  <li><strong>Imię i nazwisko:</strong> {$msg->getFirstName()} {$msg->getLastName()}</li>
</ul>
<p>Zaloguj się do panelu administratora, aby zatwierdzić konto.</p>
HTML);

        $this->mailer->send($email);
    }
}
