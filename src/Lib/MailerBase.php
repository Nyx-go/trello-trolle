<?php

namespace App\Trellotrolle\Lib;

use App\Trellotrolle\Configuration\ConfigurationSmtpBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

//TODO faire inversion des dépendances avec constructeur pour les tests
class MailerBase implements MailerInterface
{
    public static function envoyerMail(string $mail, string $sujet, string $text)
    {
        // TODO: Implement envoyerMail() method.
        $mailer = new Mailer(ConfigurationSmtpBase::getTransport());
        $email = (new Email())
            ->from('no-reply@trellotrolle.fr')
            ->to($mail)
            ->subject($sujet)
            ->text($text);
        $mailer->send($email);
        echo "test worked";
    }
}