<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Affecte;

class AffecteRepository extends AbstractRepository{

    protected function getNomTable(): string
    {
        return "affecte";
    }


    protected function getNomCle(): array
    {
        return array("idcarte", "login");
    }

    protected function getNomsColonnes(): array
    {
        return array("idcarte","login");
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
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