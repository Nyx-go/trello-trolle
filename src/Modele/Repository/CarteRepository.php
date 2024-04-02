<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use Exception;

class CarteRepository extends AbstractRepository implements CarteRepositoryInterface
{
    
    public function __construct(private ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees)
    {
        parent::__construct($connexionBaseDeDonnees);
    }

    public function getNomTable(): string
    {
        return "Cartes";
    }

    public function getNomCle(): array
    {
        return array("idcarte");
    }

    public function getNomsColonnes(): array
    {
        return [
            "idColonne","idcarte", "titrecarte", "descriptifcarte", "couleurcarte",
        ];
    }

    public function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Carte::construireDepuisTableau($objetFormatTableau);
    }

    public function recupererCartesColonne(int $idcolonne): array {
        return $this->recupererPlusieursPar("idcolonne", $idcolonne);
    }

    public function recupererCartesTableau(int $idTableau): array {
        $sql = "SELECT idCarte,c.idcolonne,titrecarte,descriptifcarte,couleurcarte FROM Cartes c 
            JOIN Colonnes co ON c.idColonne = co.idColonne 
            WHERE co.idTableau = :idtableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idtableau" => $idTableau]);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $objets;
    }

    /**
     * @return Carte[]
     */
    public function recupererCartesUtilisateur(string $login): array
    {
        $sql = "SELECT * from Cartes c left Join affecte a on c.idCarte=a.idCarte WHERE login =:login ";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
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
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["login" => $login]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    public function getNextIdCarte() : int {
        return $this->getNextId("idcarte");
    }

    public function getTableauByIdCarte($idCarte){
        $sql = "SELECT idTableau FROM cartes c JOIN colonnes co ON c.idCarte = co.idCarte WHERE c.idCarte =:idcarte;";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idCarte" => $idCarte]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    public function recupererCarteParId (string $idCarte): ?AbstractDataObject
    {
        return $this->recupererPar("idCarte",$idCarte);
    }
}