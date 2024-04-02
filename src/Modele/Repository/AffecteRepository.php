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
        return "affecte";
    }


    public function getNomCle(): array
    {
        return array("idcarte", "login");
    }

    public function getNomsColonnes(): array
    {
        return array("idcarte","login");
    }

    public function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return new Affecte($objetFormatTableau["idcarte"], $objetFormatTableau["login"]);
    }

    public function recupererParIdCarte(int $idCarte): array
    {
        return $this->recupererPlusieursPar("idcarte", $idCarte);
    }

    public function recupererParLogin(string $login): array
    {
        return $this->recupererPlusieursPar("login", $login);
    }
}