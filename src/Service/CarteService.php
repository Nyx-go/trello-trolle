<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

class CarteService extends ServiceGenerique
{
    /**
     * @throws ServiceException
     */
    public function afficherCarte(?string $idCarte) : Carte {
        if (is_null($idCarte)) throw new ServiceException("Identifiant de carte non valide" , Response::HTTP_BAD_REQUEST);
        $carte = (new CarteRepository())->recupererCarteParId($idCarte);
        if (is_null($carte)) throw new ServiceException("Carte inconnue" , Response::HTTP_NOT_FOUND);
        /** @var Carte $carte */
        return $carte;
    }
}