<?php

namespace App\Trellotrolle\Lib;

class Conteneur
{
    private static array $listeServices;

    public static function ajouterService(string $nom, $service) : void {
        Conteneur::$listeServices[$nom] = $service;
    }

    public static function recupererService(string $nom) {
        return Conteneur::$listeServices[$nom];
    }
}