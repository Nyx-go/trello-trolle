<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Participe;

class ParticipeRepository extends AbstractRepository implements ParticipeRepositoryInterface
{

    public function __construct(private ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees)
    {
        parent::__construct($connexionBaseDeDonnees);
    }

    public function getNomTable(): string
    {
        return "Participe";
    }

    public function getNomCle(): array
    {
        return array("idtableau", "login");
    }

    public function getNomsColonnes(): array
    {
        return array("idtableau", "login");
    }

    public function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return new Participe($objetFormatTableau["idtableau"], $objetFormatTableau["login"]);
    }

    public function recupererParLogin(string $login): array
    {
        return $this->recupererPlusieursPar("login", $login);
    }

    public function recupererParIdTableau(int $idTableau): array
    {
        return $this->recupererPlusieursPar("idtableau", $idTableau);
    }

}