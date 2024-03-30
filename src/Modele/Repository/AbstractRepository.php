<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use PDO;
use PDOException;

abstract class AbstractRepository
{
    protected abstract function getNomTable(): string;
    protected abstract function getNomCle(): array;
    protected abstract function getNomsColonnes(): array;
    protected abstract function construireDepuisTableau(array $objetFormatTableau) : AbstractDataObject;

    private function myDbInt() {
        //TO-DO ! Important !
    }

    protected function formatNomsColonnes() : string {
        return join(",",$this->getNomsColonnes());
    }

    /**
     * @return AbstractDataObject[]
     */
    public function recuperer(): array
    {
        $nomTable = $this->getNomTable();
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->query("SELECT DISTINCT {$this->formatNomsColonnes()} FROM $nomTable");

        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    /**
     * @return AbstractDataObject[]
     */
    protected function recupererOrdonne($attributs, $sens = "ASC"): array
    {
        $nomTable = $this->getNomTable();
        $attributsTexte = join(",", $attributs);
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->query("SELECT DISTINCT {$this->formatNomsColonnes()} FROM $nomTable ORDER BY $attributsTexte $sens");
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    /**
     * @return AbstractDataObject[]
     */
    protected function recupererPlusieursPar(string $nomAttribut, $valeur): array
    {
        $nomTable = $this->getNomTable();
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare("SELECT DISTINCT {$this->formatNomsColonnes()} FROM $nomTable WHERE $nomAttribut='$valeur'");
        $pdoStatement->execute();
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    /**
     * @return AbstractDataObject[]
     */
    protected function recupererPlusieursParOrdonne(string $nomAttribut, $valeur, $attributs, $sens = "ASC"): array
    {
        $nomTable = $this->getNomTable();
        $attributsTexte = join(",", $attributs);
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare("SELECT DISTINCT {$this->formatNomsColonnes()} FROM $nomTable WHERE $nomAttribut=:valeur ORDER BY $attributsTexte $sens");
        $values = array(
            "valeur" => $valeur,
        );
        $pdoStatement->execute($values);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    protected function recupererPar(string $nomAttribut, $valeur): ?AbstractDataObject
    {
        $nomTable = $this->getNomTable();
        $sql = "SELECT DISTINCT {$this->formatNomsColonnes()} from $nomTable WHERE $nomAttribut=:valeur";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $pdoStatement->execute(["valeur"=>$valeur]);
        $objetFormatTableau = $pdoStatement->fetch();

        if ($objetFormatTableau !== false) {
            return $this->construireDepuisTableau($objetFormatTableau);
        }
        return null;
    }

    // prérequis : valeurClePrimaire est sous la forme de $valeurClePrimaire[nomCle] = valeurCle

    public function recupererParClePrimaire(array $valeurClePrimaire): ?AbstractDataObject
    {
        $nomTable = $this->getNomTable();
        $clePrimaires = $this->getNomCle();
        $values = [];
        $sql = "SELECT {$this->formatNomsColonnes()} from $nomTable WHERE ";
        foreach ($clePrimaires as $cle){
            $sql.= "$cle = :$cle"."Tag";
            $values[$cle.'Tag']= $valeurClePrimaire[$cle];
        }

        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
        $pdoStatement->execute($values);
        $objetFormatTableau = $pdoStatement->fetch();

        if ($objetFormatTableau !== false) {
            return $this->construireDepuisTableau($objetFormatTableau);
        }
        return null;
    }
    // prérequis : valeurClePrimaire est sous la forme de $valeurClePrimaire[nomCle] = valeurCle
    public function supprimer(array $valeurClePrimaire): bool
    {
        $nomTable = $this->getNomTable();
        $clePrimaires = $this->getNomCle();
        $values =[];

        $sql = "DELETE FROM $nomTable WHERE ";
        foreach ($clePrimaires as $cle){
            $sql.= "$cle =: $cle"."Tag";
            $values[$cle.'Tag']= $valeurClePrimaire[$cle];
        }
        $pdoStatement = ConnexionBaseDeDonnees::getPDO()->prepare($sql);
        $pdoStatement->execute($values);
        $deleteCount = $pdoStatement->rowCount();

        return ($deleteCount > 0);
    }
//TODO : pb potentiel à vérifier plus tard
    public function mettreAJour(AbstractDataObject $object): void
    {
        $nomTable = $this->getNomTable();
        $clePrimaires = $this->getNomCle();
        $nomsColonnes = $this->getNomsColonnes();

        $partiesSet = array_map(function ($nomcolonne) {
            return "$nomcolonne = :{$nomcolonne}Tag";
        }, $nomsColonnes);
        $setString = join(',', $partiesSet);

        $sql = "UPDATE $nomTable SET $setString WHERE ";
        foreach ($clePrimaires as $cle){
            $sql.= "$cle = :$cle"."Tag";
        }
        $req_prep = ConnexionBaseDeDonnees::getPDO()->prepare($sql);

        $objetFormatTableau = $object->formatTableau();
        $req_prep->execute($objetFormatTableau);

    }

    public function ajouter(AbstractDataObject $object)
    {
        $nomTable = $this->getNomTable();
        $nomsColonnes = $this->getNomsColonnes();
        $nomCle = $this->getNomCle()[0];
        $objetFormatTableau = $object->formatTableau();
        if (get_class($this) == get_class(new TableauRepository()) ||
            get_class($this) == get_class(new CarteRepository()) ||
            get_class($this) == get_class(new ColonneRepository())) {
            $key = array_search($nomCle, $nomsColonnes);
            unset($nomsColonnes[$key]); // enlève la clé primaire de la liste des noms de colonnes de la table afin qu'il n'y ait pas de problème lors de l'insertion à cause du SERIAL
            unset($objetFormatTableau[$nomCle."Tag"]);
        }

        $insertString = '(' . join(', ', $nomsColonnes) . ')';

        $partiesValues = array_map(function ($nomcolonne) {
            return ":{$nomcolonne}Tag";
        }, $nomsColonnes);
        $valueString = '(' . join(', ', $partiesValues) . ')';

        $sql = "INSERT INTO $nomTable $insertString VALUES $valueString RETURNING $nomCle";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);


        try {
            $pdoStatement->execute($objetFormatTableau);
            $result = $pdoStatement->fetch(PDO::FETCH_ASSOC);
            return $result[$nomCle];
        } catch (PDOException $exception) {
            if ($pdoStatement->errorCode() === "23000") {
                return false;
            } else {
                throw $exception;
            }
        }
    }

    protected function getNextId(string $type) : int {
        $query = ConnexionBaseDeDonnees::getPdo()->query("SELECT MAX($type) FROM app_db");
        $query->execute();
        $obj = $query->fetch();
        return $obj[0] === null ? 0 : $obj[0] + 1;
    }

}
