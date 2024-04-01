<?php

namespace App\Trellotrolle\Modele\DataObject;

class Participe extends AbstractDataObject {

    public function __construct(private int $idTableau,
    private string $login)
    {
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
            "idtableauTag"=>$this->idTableau,
            "loginTag"=>$this->login
            );
    }
}