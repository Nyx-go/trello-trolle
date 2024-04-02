<?php

namespace App\Trellotrolle\Modele\DataObject;

use JsonSerializable;

class Carte extends AbstractDataObject implements JsonSerializable
{
    public function __construct(
        private int $idColonne,
        private ?int $idCarte,
        private string $titreCarte,
        private string $descriptifCarte,
        private string $couleurCarte,
    )
    {}

    public static function construireDepuisTableau(array $objetFormatTableau) : Carte {
        return new Carte(
            $objetFormatTableau["idcolonne"],
            $objetFormatTableau["idcarte"],
            $objetFormatTableau["titrecarte"],
            $objetFormatTableau["descriptifcarte"],
            $objetFormatTableau["couleurcarte"]
        );
    }

    public function getIdColonne(): int
    {
        return $this->idColonne;
    }

    public function setIdColonne(int $idColonne): void
    {
        $this->idColonne = $idColonne;
    }

    public function getIdCarte(): ?int
    {
        return $this->idCarte;
    }

    public function setIdCarte(?int $idCarte): void
    {
        $this->idCarte = $idCarte;
    }

    public function getTitreCarte(): ?string
    {
        return $this->titreCarte;
    }

    public function setTitreCarte(?string $titreCarte): void
    {
        $this->titreCarte = $titreCarte;
    }

    public function getDescriptifCarte(): ?string
    {
        return $this->descriptifCarte;
    }

    public function setDescriptifCarte(?string $descriptifCarte): void
    {
        $this->descriptifCarte = $descriptifCarte;
    }

    public function getCouleurCarte(): ?string
    {
        return $this->couleurCarte;
    }

    public function setCouleurCarte(?string $couleurCarte): void
    {
        $this->couleurCarte = $couleurCarte;
    }



    public function formatTableau(): array
    {
        return array(
                "idColonneTag" =>$this->idColonne,
                "idcarteTag" => $this->idCarte,
                "titrecarteTag" => $this->titreCarte,
                "descriptifcarteTag" => $this->descriptifCarte,
                "couleurcarteTag" => $this->couleurCarte,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            "idColonne" =>$this->getIdColonne(),
            "idcarte" => $this->getIdCarte(),
            "titrecarte" => $this->getTitreCarte(),
            "descriptifcarte" => $this->getDescriptifCarte(),
            "couleurcarte" => $this->getCouleurCarte(),
        ];
    }
}