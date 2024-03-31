<?php

namespace App\Trellotrolle\Lib;

interface MailerInterface
{
    public static function envoyerMail(string $mail , string $sujet , string $text);
}