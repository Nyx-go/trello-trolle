<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use Exception;

class CarteRepository extends AbstractRepository
{

    protected function getNomTable(): string
    {
        return "app_db";
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
        $sql = "SELECT {$this->formatNomsColonnes()} from app_db WHERE affectationscarte @> :json";
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

    public function getNombreCartesTotalUtilisateur(string $login) : int {
        $query = "SELECT COUNT(*) FROM app_db WHERE login=:login";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($query);
        $pdoStatement->execute(["login" => $login]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    public function getNextIdCarte() : int {
        return $this->getNextId("idcarte");
    }
}