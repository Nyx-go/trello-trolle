<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Colonne;
use Exception;

class ColonneRepository extends AbstractRepository implements ColonneRepositoryInterface
{

    public function __construct(private ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees)
    {
        parent::__construct($connexionBaseDeDonnees);
    }

    public function getNomTable(): string
    {
        return "colonnes";
    }

    public function getNomCle(): array
    {
        return array("idcolonne");
    }

    public function getNomsColonnes(): array
    {
        return ["idtableau", "idcolonne", "titrecolonne"];
    }

    public function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Colonne::construireDepuisTableau($objetFormatTableau);
    }

    public function recupererColonnesTableau(int $idTableau): array {
        return $this->recupererPlusieursParOrdonne("idtableau", $idTableau, array("idcolonne"));
    }

    public function getNextIdColonne() : int {
        return $this->getNextId("idcolonne");
    }
}