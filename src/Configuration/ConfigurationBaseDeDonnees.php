<?php

namespace App\Trellotrolle\Configuration;

use PDO;

class ConfigurationBaseDeDonnees implements ConfigurationBaseDeDonneesInterface
{

	//Informations de connexion pour le serveur PostgreSQL SAE de l'IUT
    static private array $configurationBaseDeDonnees = array(
        'nomHote' => '162.38.222.151',
        'nomBaseDeDonnees' => 'iut',
        'port' => '5673',
        'login' => 'gilletp',
        'motDePasse' => 'applied'
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

    public static function getDSN() : string{
        $nomHote = ConfigurationBaseDeDonnees::getNomHote();
        $port = ConfigurationBaseDeDonnees::getPort();
        $nomBaseDeDonnees = ConfigurationBaseDeDonnees::getNomBaseDeDonnees();
        return "pgsql:host=$nomHote;port=$port;dbname=$nomBaseDeDonnees";
    }

    public static function getOptions(): array
    {
        return array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
    }

}