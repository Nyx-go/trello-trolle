<?php

namespace App\Trellotrolle\Modele\DataObject;

class Tableau extends AbstractDataObject
{
    public function __construct(
        private string $login,
        private ?int $idTableau,
        private ?string $codeTableau,
        private string $titreTableau,
    )
    {}

    public static function construireDepuisTableau(array $objetFormatTableau) : Tableau {
        return new Tableau(
            $objetFormatTableau["login"],
            $objetFormatTableau["idtableau"],
            $objetFormatTableau["codetableau"],
            $objetFormatTableau["titretableau"],
        );
    }

    public function getIdUtilisateur(): string
    {
        return $this->login;
    }

    public function setIdUtilisateur(string $idUtilisateur): void
    {
        $this->login = $idUtilisateur;
    }

    public function getIdTableau(): ?int
    {
        return $this->idTableau;
    }

    public function setIdTableau(?int $idTableau): void
    {
        $this->idTableau = $idTableau;
    }

    public function getTitreTableau(): ?string
    {
        return $this->titreTableau;
    }

    public function setTitreTableau(?string $titreTableau): void
    {
        $this->titreTableau = $titreTableau;
    }

    public function getCodeTableau(): ?string
    {
        return $this->codeTableau;
    }

    public function setCodeTableau(?string $codeTableau): void
    {
        $this->codeTableau = $codeTableau;
    }

    public function formatTableau(): array
    {
        return array(
            "idtableauTag" => $this->idTableau,
            "codetableauTag" => $this->codeTableau,
            "titretableauTag" => $this->titreTableau,
            "loginTag"=>$this->login
        );
    }
}