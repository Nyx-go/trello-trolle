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
        $mailer = new Mailer(ConfigurationSmtpBase::getTransport());
        /*$email = (new Email())
            ->from('noreply@trellotrolle.fr')
            ->to($mail)
            ->subject($sujet)
            ->text($text);*/
        $email= (new Email())
            ->from('noreply@yopmail.fr')
            ->to("test@yopmail.fr")
            ->subject("retest")
            ->text("test");
        $mailer->send($email);
    }
}