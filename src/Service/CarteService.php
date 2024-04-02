<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Service\Exception\ServiceConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

class CarteService extends ServiceGenerique
{
    /**
     * @throws ServiceException
     */
    public function recupererCarte(?string $idCarte): Carte
    {
        if (is_null($idCarte)) throw new ServiceException("Identifiant de carte non valide", Response::HTTP_BAD_REQUEST);
        $carte = (new CarteRepository())->recupererCarteParId($idCarte);
        if (is_null($carte)) throw new ServiceException("Carte inconnue", Response::HTTP_NOT_FOUND);
        /** @var Carte $carte */
        return $carte;
    }

    /**
     * @throws ServiceException
     */
    public function mettreAJour(?string $idCarte, ?string $titre, ?string $descriptif, ?string $couleur): void
    {
        if (is_null($titre)) throw new ServiceException("Le titre ne peut être nul", Response::HTTP_BAD_REQUEST);
        $carte = $this->recupererCarte($idCarte);
        /** @var Colonne $colonne */
        $colonne = (new ColonneRepository())->recupererParClePrimaire(["idcolonne" => $carte->getIdColonne()]);
        if (is_null($colonne)) throw new ServiceException("Erreur sur la colonne", Response::HTTP_BAD_REQUEST);
        if (!ConnexionUtilisateur::estConnecte()) throw new ServiceException("Erreur de connexion",Response::HTTP_UNAUTHORIZED);
        if ((new TableauRepository())->estParticipantOuProprietaire($colonne->getIdTableau(),ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            if (is_null($descriptif)) $descriptif = "";
            if (is_null($couleur) || !preg_match('/^#?(([a-f0-9]{3}){1,2})$/i', $couleur)) $couleur = "#FFFFFF";
            $carte->setDescriptifCarte($descriptif);
            $carte->setTitreCarte($titre);
            $carte->setCouleurCarte($couleur);
            if (!(new CarteRepository())->mettreAJour($carte)) throw new ServiceException("Modification échouée", Response::HTTP_NO_CONTENT);
        }
        else {
            throw new ServiceException("Erreur de connexion", Response::HTTP_UNAUTHORIZED);
        }
    }
}