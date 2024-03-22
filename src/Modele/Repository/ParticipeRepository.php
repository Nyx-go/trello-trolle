<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Participe;

class ParticipeRepository extends AbstractRepository{

    protected function getNomTable(): string
    {
        return "ParticipeRepository";
    }

    protected function getNomCle(): array
    {
        return array("idTableau", "login");
    }

    protected function getNomsColonnes(): array
    {
        return array("idTableau", "login");
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return new Participe($objetFormatTableau["idTableau"], $objetFormatTableau["login"]);
    }

}