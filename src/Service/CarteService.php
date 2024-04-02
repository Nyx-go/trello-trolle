<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use MongoDB\Driver\Exception\ServerException;
use Symfony\Component\HttpFoundation\Response;


//TODO: factoriser le code pouvant être factorisé si on a le temps
class CarteService extends ServiceGenerique implements CarteServiceInterface
{
    
    public function __construct(
        private CarteRepositoryInterface $carteRepository,
        private ColonneRepositoryInterface $colonneRepository,
        private TableauRepositoryInterface $tableauRepository
    )
    {
    }

    /**
     * @throws ServiceException
     */
    public function recupererCarte(?string $idCarte): Carte
    {
        if (is_null($idCarte)) throw new ServiceException("Identifiant de carte non valide", Response::HTTP_BAD_REQUEST);
        $carte = $this->carteRepository->recupererCarteParId($idCarte);
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
        $colonne = $this->colonneRepository->recupererParClePrimaire(["idcolonne" => $carte->getIdColonne()]);
        if (is_null($colonne)) throw new ServiceException("Erreur sur la colonne", Response::HTTP_BAD_REQUEST);
        if (!ConnexionUtilisateur::estConnecte()) throw new ServiceException("Erreur de connexion", Response::HTTP_UNAUTHORIZED);
        if ($this->tableauRepository->estParticipantOuProprietaire($colonne->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            if (is_null($descriptif)) $descriptif = "";
            if (is_null($couleur) || !preg_match('/^#?(([a-f0-9]{3}){1,2})$/i', $couleur)) $couleur = "#FFFFFF";
            $carte->setDescriptifCarte($descriptif);
            $carte->setTitreCarte($titre);
            $carte->setCouleurCarte($couleur);
            if (!$this->carteRepository->mettreAJour($carte)) throw new ServiceException("Modification échouée", Response::HTTP_NO_CONTENT);
        } else {
            throw new ServiceException("Erreur de connexion", Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @throws ServiceException
     */
    public function ajouter(string $idColonne): Carte
    {
        /** @var Colonne $colonne */
        $colonne = $this->colonneRepository->recupererParClePrimaire(["idcolonne" => $idColonne]);
        if (is_null($colonne)) throw new ServiceException("Erreur sur la colonne", Response::HTTP_BAD_REQUEST);
        if (!ConnexionUtilisateur::estConnecte()) throw new ServiceException("Erreur de connexion", Response::HTTP_UNAUTHORIZED);
        if ($this->tableauRepository->estParticipantOuProprietaire($colonne->getIdTableau() , ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            $carte = new Carte($idColonne,$this->carteRepository->getNextIdCarte(),"titre de la carte","","#FFFFFF");
            if (!$this->carteRepository->ajouter($carte)) throw new ServiceException("Erreur, impossible d'ajouter la carte",Response::HTTP_CONFLICT);
            return $carte;
        }
        else {
            throw new ServiceException("Erreur de connexion", Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @throws ServiceException
     */
    public function supprimer(string $idCarte): void {
        $carte = $this->recupererCarte($idCarte);
        /** @var Colonne $colonne */
        $colonne = $this->colonneRepository->recupererParClePrimaire(["idcolonne" => $carte->getIdColonne()]);
        if (is_null($colonne)) throw new ServiceException("Erreur sur la colonne", Response::HTTP_BAD_REQUEST);
        if (!ConnexionUtilisateur::estConnecte()) throw new ServiceException("Erreur de connexion", Response::HTTP_UNAUTHORIZED);
        if ($this->tableauRepository->estParticipantOuProprietaire($colonne->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            if (!$this->carteRepository->supprimer(["idcarte" => $idCarte])) throw new ServiceException("Suppression échouée", Response::HTTP_NO_CONTENT);
        } else {
            throw new ServiceException("Erreur de connexion", Response::HTTP_FORBIDDEN);
        }
    }
}