<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Colonne;
use Exception;
use PDO;
use PDOException;

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

    public function supprimer(array $valeurClePrimaire): bool
    {
        //suppression des cartes de la colonne
        $carteRepository = new CarteRepository();
        $cartes = $carteRepository->recupererCartesColonne($valeurClePrimaire['idcolonne']);
        foreach ($cartes as $carte) {
            $carteRepository->supprimer($carte->getNomCle());
        }

        return AbstractRepository::supprimer($valeurClePrimaire);
    }

    public function ajouter(AbstractDataObject $object)
    {
        $sql = "INSERT INTO colonnes (idtableau ,titrecolonne) VALUES (:idtableau, :titrecolonne) RETURNING idcolonne;";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);

        try {
            $pdoStatement->execute(array("idtableau"=>$object->getIdTableau(), "titrecolonne"=> $object-> getTitreColonne(),"codetableau"=>$object->getCodetableau()));
            $result = $pdoStatement->fetch(PDO::FETCH_ASSOC);
            return $result["idColonne"];
        } catch (PDOException $exception) {
            if ($pdoStatement->errorCode() === "23000") {
                return false;
            } else {
                throw $exception;
            }
        }
    }

}