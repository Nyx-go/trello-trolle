<?php

namespace App\Trellotrolle\Modele\DataObject;

class Colonne extends AbstractDataObject
{
    public function __construct(
        private int    $idTableau,
        private ?int    $idColonne,
        private string $titreColonne
    )
    {
    }

    public static function construireDepuisTableau(array $objetFormatTableau): Colonne
    {
        return new Colonne(
            $objetFormatTableau["idtableau"],
            $objetFormatTableau["idcolonne"],
            $objetFormatTableau["titrecolonne"],
        );
    }

    public function getIdColonne(): ?int
    {
        return $this->idColonne;
    }

    public function setIdColonne(?int $idColonne): void
    {
        $this->idColonne = $idColonne;
    }

    public function getTitreColonne(): ?string
    {
        return $this->titreColonne;
    }

    //TODO: Une fois la mise à jour du titre de la colonne passé en API, il ne pourra être nul
    //parait même bizarre qu'il accepte d'être null
    public function setTitreColonne(?string $titreColonne): void
    {
        $this->titreColonne = $titreColonne;
    }

    /**
     * @return int
     */
    public function getIdTableau(): int
    {
        return $this->idTableau;
    }

    /**
     * @param int $idTableau
     */
    public function setIdTableau(int $idTableau): void
    {
        $this->idTableau = $idTableau;
    }




    public function formatTableau(): array
    {
        return array(
            "idtableauTag" => $this->idTableau,
            "idcolonneTag" => $this->idColonne,
            "titrecolonneTag" => $this->titreColonne,
        );
    }
}