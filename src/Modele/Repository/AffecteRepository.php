<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Affecte;

class AffecteRepository extends AbstractRepository{

    protected function getNomTable(): string
    {
        return "Affecte";
    }


    protected function getNomCle(): array
    {
        return array("idCarte", "login");
    }

    protected function getNomsColonnes(): array
    {
        return array("idCarte","login");
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return new Affecte($objetFormatTableau["idCarte"], $objetFormatTableau["login"]);
    }
}