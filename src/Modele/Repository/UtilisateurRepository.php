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

    public function recupererUtilisateurParEmail(string $email): ?AbstractDataObject {
        return $this->recupererPar("email", $email);
    }

    public function recupererUtilisateursOrderedPrenomNom() : array {
        return $this->recupererOrdonne(["prenom", "nom"]);
    }
}