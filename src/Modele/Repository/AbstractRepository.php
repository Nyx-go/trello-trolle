<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Configuration\ConfigurationBaseDeDonnees;
use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use PDO;
use PDOException;

abstract class AbstractRepository
{
    protected abstract function getNomTable(): string;
    protected abstract function getNomCle(): array;
    protected abstract function getNomsColonnes(): array;
    protected abstract function construireDepuisTableau(array $objetFormatTableau) : AbstractDataObject;

    public function myDbInt() {
        //TO-DO ! Important !
    }

    public function __construct(private ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees)
    {
    }

    public function formatNomsColonnes() : string {
        return join(",",$this->getNomsColonnes());
    }

    /**
     * @return AbstractDataObject[]
     */
    public function recuperer(): array
    {
        $nomTable = $this->getNomTable();
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->query("SELECT DISTINCT {$this->formatNomsColonnes()} FROM $nomTable");

        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    /**
     * @return AbstractDataObject[]
     */
    public function recupererOrdonne($attributs, $sens = "ASC"): array
    {
        $nomTable = $this->getNomTable();
        $attributsTexte = join(",", $attributs);
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->query("SELECT DISTINCT {$this->formatNomsColonnes()} FROM $nomTable ORDER BY $attributsTexte $sens");
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    /**
     * @return AbstractDataObject[]
     */
    public function recupererPlusieursPar(string $nomAttribut, $valeur): array
    {
        $nomTable = $this->getNomTable();
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare("SELECT DISTINCT {$this->formatNomsColonnes()} FROM $nomTable WHERE $nomAttribut=:valeur");
        $pdoStatement->execute(["valeur"=>$valeur]);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    /**
     * @return AbstractDataObject[]
     */
    public function recupererPlusieursParOrdonne(string $nomAttribut, $valeur, $attributs, $sens = "ASC"): array
    {
        $nomTable = $this->getNomTable();
        $attributsTexte = join(",", $attributs);
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare("SELECT DISTINCT {$this->formatNomsColonnes()} FROM $nomTable WHERE $nomAttribut=:valeur ORDER BY $attributsTexte $sens");
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

    public function recupererPar(string $nomAttribut, $valeur): ?AbstractDataObject
    {
        $nomTable = $this->getNomTable();
        $sql = "SELECT DISTINCT {$this->formatNomsColonnes()} from $nomTable WHERE $nomAttribut=:valeur";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
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

        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
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
            $sql.= "$cle = :$cle"."Tag";
            $values[$cle.'Tag']= $valeurClePrimaire[$cle];
            $sql.= " AND ";
        }
        $sql = rtrim($sql, " AND ");
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute($values);
        $deleteCount = $pdoStatement->rowCount();

        return ($deleteCount > 0);
    }

    public function mettreAJour(AbstractDataObject $object): bool
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
        $req_prep = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);

        $objetFormatTableau = $object->formatTableau();
        $req_prep->execute($objetFormatTableau);
        $updateCount = $req_prep->rowCount();

        return ($updateCount > 0);
    }

    public function ajouter(AbstractDataObject $object)
    {
        $nomTable = $this->getNomTable();
        $nomsColonnes = $this->getNomsColonnes();
        $nomCle = $this->getNomCle()[0];
        $objetFormatTableau = $object->formatTableau();
        if (get_class($this) == get_class(new TableauRepository(new ConnexionBaseDeDonnees(new ConfigurationBaseDeDonnees()))) ||
            get_class($this) == get_class(new CarteRepository(new ConnexionBaseDeDonnees(new ConfigurationBaseDeDonnees()))) ||
            get_class($this) == get_class(new ColonneRepository(new ConnexionBaseDeDonnees(new ConfigurationBaseDeDonnees())))) {
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
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);


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

    public function getNextId(string $type) : int {
        $query = $this->connexionBaseDeDonnees->getPdo()->query("SELECT MAX($type) FROM app_db");
        $query->execute();
        $obj = $query->fetch();
        return $obj[0] === null ? 0 : $obj[0] + 1;
    }

}
