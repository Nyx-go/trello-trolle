<?php

namespace App\Trellotrolle\Configuration;

interface ConfigurationBaseDeDonneesInterface
{
    public static function getLogin(): string;

    public static function getNomBaseDeDonnees(): string;

    public static function getPort(): string;

    public static function getNomHote(): string;

    public static function getMotDePasse(): string;

    public static function getDSN(): string;

}