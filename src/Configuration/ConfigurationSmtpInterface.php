<?php

namespace App\Trellotrolle\Configuration;

use Symfony\Component\Mailer\Transport;

interface ConfigurationSmtpInterface
{
    static public function getTransport(): Transport\TransportInterface;
}