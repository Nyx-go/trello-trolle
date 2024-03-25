<?php

namespace App\Trellotrolle\Controleur;

use Symfony\Component\Routing\Attribute\Route;

class ControleurBase extends ControleurGenerique
{
    #[Route(path: '/accueil', name:'default', methods:["GET"])]
    public static function accueil(): void
    {
        ControleurBase::afficherVue('vueGenerale.php', [
            "pagetitle" => "Accueil",
            "cheminVueBody" => "base/accueil.php"
        ]);
    }
}