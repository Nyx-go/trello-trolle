<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Affecte;

class AffecteRepository extends AbstractRepository implements AffecteRepositoryInterface
{

    public function __construct(private ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees)
    {
        parent::__construct($connexionBaseDeDonnees);
    }

    public function getNomTable(): string
    {
        return "Affecte";
    }


    public function getNomCle(): array
    {
        return array("idCarte", "login");
    }

    public function getNomsColonnes(): array
    {
        return array("idCarte","login");
    }

    public function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return new Affecte($objetFormatTableau["idCarte"], $objetFormatTableau["login"]);
    }

    public function recupererParIdCarte(int $idCarte): array
    {
        return $this->recupererPlusieursPar("idCarte", $idCarte);
    }

    public function recupererParLogin(string $login): array
    {
        return $this->recupererPlusieursPar("login", $login);
    }
}