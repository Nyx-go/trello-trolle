<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Participe;

class ParticipeRepository extends AbstractRepository{

    protected function getNomTable(): string
    {
        return "Participe";
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

    public function recupererParLogin(string $login): array
    {
        return $this->recupererPlusieursPar("login", $login);
    }

    public function recupererParIdTableau(int $idTableau): array
    {
        return $this->recupererPlusieursPar("idTableau", $idTableau);
    }

}