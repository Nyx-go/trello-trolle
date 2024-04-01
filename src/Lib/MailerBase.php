<?php

namespace App\Trellotrolle\Lib;

use App\Trellotrolle\Configuration\ConfigurationSmtpBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

//TODO faire inversion des dépendances avec constructeur pour les tests
class MailerBase implements MailerInterface
{
    public static function envoyerMail(string $mail, string $sujet, string $text): bool
    {
        $mailer = new Mailer(ConfigurationSmtpBase::getTransport());
        $email = (new Email())
            ->from('noreply@trellotrolle.fr')
            ->to($mail)
            ->subject($sujet)
            ->text($text);

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            return false;
        }
        return true;
    }
}