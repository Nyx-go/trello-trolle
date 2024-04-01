<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Colonne;
use Exception;

class ColonneRepository extends AbstractRepository
{

    protected function getNomTable(): string
    {
        return "colonnes";
    }

    protected function getNomCle(): array
    {
        return array("idcolonne");
    }

    protected function getNomsColonnes(): array
    {
        return ["idtableau", "idcolonne", "titrecolonne"];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
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