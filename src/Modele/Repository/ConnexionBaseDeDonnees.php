<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Configuration\ConfigurationBaseDeDonnees;
use PDO;


class ConnexionBaseDeDonnees
{
    private static ?ConnexionBaseDeDonnees $instance = null;

    private PDO $pdo;

    public static function getPdo(): PDO
    {
        return ConnexionBaseDeDonnees::getInstance()->pdo;
    }

    private function __construct()
    {
        $nomHote = ConfigurationBaseDeDonnees::getNomHote();
        $port = ConfigurationBaseDeDonnees::getPort();
        $login = ConfigurationBaseDeDonnees::getLogin();
        $motDePasse = ConfigurationBaseDeDonnees::getMotDePasse();
        $nomBaseDeDonnees = ConfigurationBaseDeDonnees::getNomBaseDeDonnees();

        try {
            $this->pdo = new PDO(
                "pgsql:host=$nomHote;port=$port;dbname=$nomBaseDeDonnees",
                $login,
                $motDePasse,
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch (\PDOException $e){
            echo "la connexion à échoué : ".$e->getMessage();
        }

    }

    private static function getInstance(): ConnexionBaseDeDonnees
    {
        if (is_null(ConnexionBaseDeDonnees::$instance))
            ConnexionBaseDeDonnees::$instance = new ConnexionBaseDeDonnees();
        return ConnexionBaseDeDonnees::$instance;
    }
}