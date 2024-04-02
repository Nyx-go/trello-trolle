<?php

namespace App\Trellotrolle\Configuration;

use Symfony\Component\Mailer\Transport;

class ConfigurationSmtpBase implements ConfigurationSmtpInterface
{
    private static string $login = 'a181634a-3f38-4a92-bef6-bf901cdd7936';
    private static string $password = 'c71d90ef-f5cd-4928-8b51-b13aba8f829c';
    private static string $smtpUrl = 'app.debugmail.io';
    private static string $port = '9025';

    static public function getTransport(): Transport\TransportInterface
    {
        return (Transport::fromDsn('smtp://' . self::$login . ':' . self::$password . '@' . self::$smtpUrl . ':' . self::$port));
    }

}