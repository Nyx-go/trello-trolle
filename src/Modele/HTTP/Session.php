<?php

namespace App\Trellotrolle\Modele\HTTP;

use App\Trellotrolle\Configuration\ConfigurationSite;
use App\Trellotrolle\Lib\ConnexionUtilisateur;
use Exception;

class Session
{
    private static ?Session $instance = null;

    private function __construct()
    {
        if (session_start() === false) {
            throw new Exception("La session n'a pas réussi à démarrer.");
        }
    }

    public function verifierDerniereActivite(int $dureeExpiration) : void
    {
        if ($dureeExpiration == 0)
            return;

        if (isset($_SESSION['derniereActivite']) && (time() - $_SESSION['derniereActivite'] > ($dureeExpiration)))
            session_unset();

        $_SESSION['derniereActivite'] = time();

    }

    public static function getInstance(): Session
    {
        if (is_null(static::$instance)) {
            static::$instance = new Session();

            // Durée d'expiration des sessions en secondes
            $dureeExpiration = ConfigurationSite::getDureeExpirationSession();
            static::$instance->verifierDerniereActivite($dureeExpiration);
        }
        return static::$instance;
    }

    public function contient($nom): bool
    {
        return isset($_SESSION[$nom]);
    }

    public function enregistrer(string $nom, mixed $valeur): void
    {
        $_SESSION[$nom] = $valeur;
    }

    public function lire(string $nom): mixed
    {
        return $_SESSION[$nom];
    }

    public function supprimer($nom): void
    {
        unset($_SESSION[$nom]);
    }

    public function detruire() : void
    {
        session_unset();
        session_destroy();
        Cookie::supprimer(session_name());
        Session::$instance = null;
    }
}