<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Tableau;

class TableauRepository extends AbstractRepository implements TableauRepositoryInterface
{
    public function __construct(private ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees)
    {
        parent::__construct($connexionBaseDeDonnees);
    }

    public function getNomTable(): string
    {
        return "Tableaux";
    }

    public function getNomCle(): array
    {
        return array("idtableau");
    }

    public function getNomsColonnes(): array
    {
        return ["login", "idtableau", "codetableau", "titretableau"];
    }

    public function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
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
        $sql = "SELECT t.idtableau, t.login, codetableau, titretableau
                FROM tableaux t LEFT JOIN participe p ON t.idtableau = p.idtableau 
                WHERE p.login = :loginTag OR t.login = :loginTag";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["loginTag" => $login]);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $objets;
    }

    public function getNombreTableauxTotalUtilisateur(string $login) : int {
        $query = "SELECT COUNT(DISTINCT idtableau) FROM tableaux WHERE login=:login";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["login" => $login]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    public function estParticipant($idTableau, $login) : bool{
        $sql = "SELECT login FROM participe WHERE idtableau =:idTableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $obj = $pdoStatement->fetch();
        if ($obj) {
            foreach ($obj as $item) {
                if ($item === $login) return true;
            }
        }

        return false;
    }

    public function estProprietaire($idTableau, $login) : bool{
        $sql = "SELECT login FROM tableaux WHERE idtableau =:idTableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $obj = $pdoStatement->fetch();
        foreach ($obj as $item) {
            if ($item === $login) return true;
        }
        return false;
    }

    public function estParticipantOuProprietaire($idTableau, $login) : bool{
        return $this->estParticipant($idTableau, $login) || $this->estProprietaire($idTableau, $login);
    }
}