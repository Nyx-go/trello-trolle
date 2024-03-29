<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Colonne;
use Exception;

class ColonneRepository extends AbstractRepository
{

    protected function getNomTable(): string
    {
        return "Colonnes";
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
        return $this->recupererPlusieursParOrdonne("idtableau", $idTableau, "idcolonne");
    }

    public function getNextIdColonne() : int {
        return $this->getNextId("idcolonne");
    }
}