<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Tableau;
use Exception;

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
     * @throws Exception
     */
    public function ajouter(AbstractDataObject $object): bool
    {
        throw new Exception("Impossible d'ajouter seulement un tableau...");
    }

    /**
     * @return Tableau[]
     */
    public function recupererTableauxOuUtilisateurEstMembre(string $login): array
    {
        $sql = "SELECT DISTINCT {$this->formatNomsColonnes()}
                from app_db 
                WHERE login='$login' OR participants @> :json";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $values = array(
            "json" => json_encode(["utilisateurs" => [["login" => $login]]])
        );
        $pdoStatement->execute($values);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $objets;
    }

    /**
     * @return Tableau[]
     */
    public function recupererTableauxParticipeUtilisateur(string $login): array
    {
        $sql = "SELECT DISTINCT {$this->formatNomsColonnes()}
                from app_db 
                WHERE participants @> :json";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $values = array(
            "json" => json_encode(["utilisateurs" => [["login" => $login]]])
        );
        $pdoStatement->execute($values);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $objets;
    }

    public function getNextIdTableau() : int {
        return $this->getNextId("idtableau");
    }

    public function getNombreTableauxTotalUtilisateur(string $login) : int {
        $query = "SELECT COUNT(DISTINCT idtableau) FROM app_db WHERE login=:login";
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
}