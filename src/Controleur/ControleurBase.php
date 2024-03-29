<?php

namespace App\Trellotrolle\Controleur;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurBase extends ControleurGenerique
{
    #[Route(path: '/', name:'default', methods:["GET"])]
    public static function accueil(): Response
    {
        return self::afficherTwig("base/accueil.html.twig",);
    }
}