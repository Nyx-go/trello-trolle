<?php

namespace App\Trellotrolle\Configuration;

class ConfigurationBaseDeDonnees {

	//Informations de connexion pour le serveur PostgreSQL SAE de l'IUT
    static private array $configurationBaseDeDonnees = array(
        'nomHote' => 'bd',
        'nomBaseDeDonnees' => 'bd',
        'port' => '5673',
        'login' => 'admin',
        'motDePasse' => 'admin'
    );

    static public function getLogin() : string {
        return ConfigurationBaseDeDonnees::$configurationBaseDeDonnees['login'];
    }

    static public function getNomBaseDeDonnees() : string {
        return ConfigurationBaseDeDonnees::$configurationBaseDeDonnees['nomBaseDeDonnees'];
    }

    static public function getPort() : string {
        return ConfigurationBaseDeDonnees::$configurationBaseDeDonnees['port'];
    }

    static public function getNomHote() : string {
        return ConfigurationBaseDeDonnees::$configurationBaseDeDonnees['nomHote'];
    }

    static public function getMotDePasse() : string {
        return ConfigurationBaseDeDonnees::$configurationBaseDeDonnees['motDePasse'];
    }

}