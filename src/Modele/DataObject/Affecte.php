<?php

namespace App\Trellotrolle\Modele\DataObject;

class Affecte extends AbstractDataObject{

    public function __construct(private int $idCarte,
                                private string $login)
    {
    }

    /**
     * @return int
     */
    public function getIdCarte(): int
    {
        return $this->idCarte;
    }

    /**
     * @param int $idCarte
     */
    public function setIdCarte(int $idCarte): void
    {
        $this->idCarte = $idCarte;
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin(string $login): void
    {
        $this->login = $login;
    }



    public function formatTableau(): array
    {
        return array(
            "idcarteTag"=>$this->idCarte,
            "loginTag"=>$this->login
        );
    }
}