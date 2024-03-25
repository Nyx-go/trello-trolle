<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;

class UtilisateurRepository extends AbstractRepository
{

    protected function getNomTable(): string
    {
        return "Utilisateurs";
    }

    protected function getNomCle(): array
    {
        return array("login");
    }

    protected function getNomsColonnes(): array
    {
        return ["login", "nom", "prenom", "email", "mdphache"];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Utilisateur::construireDepuisTableau($objetFormatTableau);
    }

    /**
     * @throws Exception
     */
    //TODO NE DEVRAIT PAS JETER D'EXCEPTION ALORS QUE SON PARENT A UNE FONCTION: LISKOV
//    public function ajouter(AbstractDataObject $object): bool
//    {
//        throw new Exception("Impossible d'ajouter seulement un utilisateur...");
//    }

    public function recupererUtilisateursParEmail(string $email): array {
        return $this->recupererPlusieursPar("email", $email);
    }

    public function recupererUtilisateursOrderedPrenomNom() : array {
        return $this->recupererOrdonne(["prenom", "nom"]);
    }

    public function supprimer(array $valeurClePrimaire): bool
    {
        //suppression des affectations de l'utilisateur
        $affecteRepository = new AffecteRepository();
        $affectations = $affecteRepository->recupererParLogin($valeurClePrimaire['login']);
        foreach ($affectations as $affectation) {
            $affecteRepository->supprimer($affectation->getNomCle());
        }

        //suppression des participations de l'utilisateur
        $participeRepository = new ParticipeRepository();
        $participations = $participeRepository->recupererParLogin($valeurClePrimaire['login']);
        foreach ($participations as $participation) {
            $participeRepository->supprimer($participation->getNomCle());
        }

        //suppression des tableaux de l'utilisateur
        $tableauRepository = new TableauRepository();
        $tableaux = $tableauRepository->recupererTableauxUtilisateur($valeurClePrimaire['login']);
        foreach ($tableaux as $tableau) {
            $tableauRepository->supprimer($tableau->getNomCle());
        }

        return AbstractRepository::supprimer($valeurClePrimaire);
    }
}