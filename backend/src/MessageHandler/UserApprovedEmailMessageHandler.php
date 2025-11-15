<?php

namespace App\MessageHandler;

use App\Message\UserApprovedEmailMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class UserApprovedEmailMessageHandler
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function __invoke(UserApprovedEmailMessage $msg): void
    {
        $email = (new Email())
            ->from(new Address('macmalonet783@gmail.com', 'FixOurCity Support'))
            ->to($msg->getEmail())
            ->subject('Twoje konto w FixOurCity zostało zatwierdzone')
            ->text(
                "Cześć {$msg->getFirstName()},\n\n".
                "Twoje konto w FixOurCity zostało właśnie zatwierdzone.\n".
                "Możesz już logować się i dodawać zgłoszenia.\n\n".
                "Pozdrawiamy,\nZespół FixOurCity"
            )
            ->html(<<<HTML
<p>Cześć <strong>{$msg->getFirstName()}</strong>,</p>
<p>Twoje konto w <strong>FixOurCity</strong> zostało właśnie zatwierdzone.</p>
<p>Możesz teraz logować się i dodawać zgłoszenia.</p>
<p>Pozdrawiamy,<br/>Zespół FixOurCity</p>
HTML);

        $this->mailer->send($email);
    }
}
