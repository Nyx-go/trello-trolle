<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Tableau;
use Exception;
use PDO;
use PDOException;

class TableauRepository extends AbstractRepository
{
    protected function getNomTable(): string
    {
        return "Tableaux";
    }

    protected function getNomCle(): array
    {
        return array("idtableau");
    }

    protected function getNomsColonnes(): array
    {
        return ["login", "idtableau", "codetableau", "titretableau"];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Tableau::construireDepuisTableau($objetFormatTableau);
    }

    public function recupererTableauxUtilisateur(string $login): array {
        return $this->recupererPlusieursPar("login", $login);
    }

    public function recupererParCodeTableau(string $codeTableau): ?AbstractDataObject {
        return $this->recupererPar("codetableau", $codeTableau);
    }

    /**
     * @return Tableau[]
     */
    public function recupererTableauxOuUtilisateurEstMembre(string $login): array
    {
        $sql = "SELECT DISTINCT t.idtableau, t.login, codetableau, titretableau
                FROM tableaux t JOIN participe p ON t.idtableau = p.idtableau 
                WHERE p.login = :loginTag OR t.login = :loginTag";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $pdoStatement->execute(["loginTag" => $login]);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $objets;
    }

    public function getNombreTableauxTotalUtilisateur(string $login) : int {
        $query = "SELECT COUNT(DISTINCT idtableau) FROM tableaux WHERE login=:login";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($query);
        $pdoStatement->execute(["login" => $login]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    public function estParticipant($idTableau, $login) : bool{
        $sql = "SELECT login FROM participe WHERE idtableau =:idTableau";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $obj = $pdoStatement->fetch();
        foreach ($obj as $item) {
            if ($item === $login) return true;
        }
        return false;
    }

    public function estProprietaire($idTableau, $login) : bool{
        $sql = "SELECT login FROM tableaux WHERE idtableau =:idTableau";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $obj = $pdoStatement->fetch();
        foreach ($obj as $item) {
            if ($item === $login) return true;
        }
        return false;
    }

    public function estParticipantOuProprietaire($idTableau, $login){
        return $this->estParticipant($idTableau, $login) || $this->estProprietaire($idTableau, $login);
    }

    public function supprimer(array $valeurClePrimaire): bool
    {
        // c ok ca ou je fait de la d ???????????????????????????????
        //genre on a le droit de creer des repository dans d'autre répository ou pas ?
        //et logiquement il faut faire la mm pour utilisateur, carte et colonne
        //j'ai fait la mm pour les autres du coup

        //suppression des colonnes du tableau
        $colonneRepository = new ColonneRepository();
        $colonnes = $colonneRepository->recupererColonnesTableau($valeurClePrimaire['idtableau']);
        foreach ($colonnes as $colonne) {
            $colonneRepository->supprimer($colonne->getNomCle());
        }

        //suppression des participations du tableau
        $participeRepository = new ParticipeRepository();
        $participations = $participeRepository->recupererParLogin($valeurClePrimaire['login']);
        foreach ($participations as $participation) {
            $participeRepository->supprimer($participation->getNomCle());
        }

        return AbstractRepository::supprimer($valeurClePrimaire);
    }

    public function ajouter(AbstractDataObject $object)
    {
        $sql = "INSERT INTO tableaux (login, titretableau, codetableau) VALUES (:login, :titretableau, :codetableau) RETURNING idtableau;";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);

        try {
            $pdoStatement->execute(array("login"=>$object->getLogin(), "titretableau"=> $object-> getTitreTableau(),"codetableau"=>$object->getCodeTableau()));
            $result = $pdoStatement->fetch(PDO::FETCH_ASSOC);
            return $result["idtableau"];
        } catch (PDOException $exception) {
            if ($pdoStatement->errorCode() === "23000") {
                return false;
            } else {
                throw $exception;
            }
        }
    }
}