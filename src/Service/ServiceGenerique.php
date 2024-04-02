<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Service\Exception\ServiceConnexionException;

class ServiceGenerique
{

    /**
     * @throws ServiceConnexionException
     */
    public function doitEtreConnecte (): void {
        if (!ConnexionUtilisateur::estConnecte()) throw new ServiceConnexionException("L'utilisateur n'est pas connecté");
    }

    /**
     * @throws ServiceConnexionException
     */
    public function doitEtreDeconnecte (): void {
        if (ConnexionUtilisateur::estConnecte()) throw new ServiceConnexionException("L'utilisateur est déjà connecté");
    }

}