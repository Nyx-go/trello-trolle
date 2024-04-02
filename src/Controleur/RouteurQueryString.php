<?php

namespace App\Trellotrolle\Controleur;

class RouteurQueryString {
    public static function traiterRequete() : void {
        $action = $_REQUEST['action'] ?? 'accueil';

        $controleur = "base";
        if (isset($_REQUEST['controleur']))
            $controleur = $_REQUEST['controleur'];

        $nomDeClasseControleur = 'App\Trellotrolle\Controleur\Controleur' . ucfirst($controleur);

        if (class_exists($nomDeClasseControleur)) {
            $controleur = new $nomDeClasseControleur();
            if (in_array($action, get_class_methods($nomDeClasseControleur))) {
                $nomDeClasseControleur::$action();
            } else {
                $nomDeClasseControleur::afficherErreur("Erreur d'action");
            }
        } else {
            ControleurGenerique::afficherErreur("Erreur de contrôleur");
        }
    }
}