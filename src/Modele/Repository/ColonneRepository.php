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

    //TODO: modifier et vérifier la requête
    public function getNombreColonnesTotalTableau(int $idTableau) : int {
        $query = "SELECT COUNT(idcolonne) FROM colonnes WHERE idtableau=:idTableau";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($query);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    /**
     * @throws Exception
     */
    //TODO: Comprendre pourquoi il jette une exception et ne fait rien
//    public function ajouter(AbstractDataObject $object): bool
//    {
//        throw new Exception("Impossible d'ajouter seulement une colonne...");
//    }


}