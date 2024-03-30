<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use Exception;
use PDO;
use PDOException;

class CarteRepository extends AbstractRepository
{

    protected function getNomTable(): string
    {
        return "Cartes";
    }

    protected function getNomCle(): array
    {
        return array("idcarte");
    }

    protected function getNomsColonnes(): array
    {
        return [
            "idColonne","idcarte", "titrecarte", "descriptifcarte", "couleurcarte",
        ];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Carte::construireDepuisTableau($objetFormatTableau);
    }

    public function recupererCartesColonne(int $idcolonne): array {
        return $this->recupererPlusieursPar("idcolonne", $idcolonne);
    }

    public function recupererCartesTableau(int $idTableau): array {
        return $this->recupererPlusieursPar("idtableau", $idTableau);
    }

    /**
     * @return Carte[]
     */
    public function recupererCartesUtilisateur(string $login): array
    {
        $sql = "SELECT * from Cartes c left Join affecte a on c.idCarte=a.idCarte WHERE login =:login ";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $values = array(
            "login" => $login
        );
        $pdoStatement->execute($values);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $objets;
    }

    public function getNombreCartesTotalUtilisateur(string $login) : int {
        $query = "SELECT COUNT(*) FROM Cartes c JOIN affecte a ON a.idCarte = c.idCarte WHERE login=:login";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($query);
        $pdoStatement->execute(["login" => $login]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    public function getNextIdCarte() : int {
        return $this->getNextId("idcarte");
    }

    public function getTableauByIdCarte($idCarte){
        $sql = "SELECT idTableau FROM cartes c JOIN colonnes co ON c.idCarte = co.idCarte WHERE c.idCarte =:idcarte;";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $pdoStatement->execute(["idCarte" => $idCarte]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    public function supprimer(array $valeurClePrimaire): bool
    {
        //suppression des affectations de la carte
        $affecteRepository = new AffecteRepository();
        $affectations = $affecteRepository->recupererParIdCarte($valeurClePrimaire['idcarte']);
        foreach ($affectations as $affectation) {
            $affecteRepository->supprimer($affectation->getNomCle());
        }

        return AbstractRepository::supprimer($valeurClePrimaire);
    }

//    public function ajouter(AbstractDataObject $object)
//    {
//        $sql = "INSERT INTO cartes (titrecarte, descriptifcarte, couleurcarte, idcolonne) VALUES (:titrecarte, :descriptifcarte, :couleurcarte, :idcolonne) RETURNING idcarte;";
//        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
//
//        try {
//            $pdoStatement->execute(array("titrecarte"=>$object->getTitreCarte(), "descriptifcarte"=> $object-> getDescriptifCarte(),"couleurcarte"=>$object->getCouleurCarte(), "idcolonne"=>$object->getIdColonne()));
//            $result = $pdoStatement->fetch(PDO::FETCH_ASSOC);
//            return $result["idcarte"];
//        } catch (PDOException $exception) {
//            if ($pdoStatement->errorCode() === "23000") {
//                return false;
//            } else {
//                throw $exception;
//            }
//        }
//    }
}