<?php

namespace App\Trellotrolle\Modele\DataObject;

class Utilisateur extends AbstractDataObject
{
    public function __construct(
        private string $login,
        private string $nom,
        private string $prenom,
        private string $email,
        private string $mdpHache
    )
    {}

    public static function construireDepuisTableau(array $objetFormatTableau) : Utilisateur {
        return new Utilisateur(
            $objetFormatTableau["login"],
            $objetFormatTableau["nom"],
            $objetFormatTableau["prenom"],
            $objetFormatTableau["email"],
            $objetFormatTableau["mdphache"]
        );
    }

    //TODO: on ne devrait normalement plus construire d'utilisateurs depuis du json, à vérifier puis supprimer ou modifier en ajustement
    public static function construireUtilisateursDepuisJson(?string $jsonList) : array {
        $users = [];
        if($jsonList != null) {
            $aff = json_decode($jsonList, true);
            $utilisateurs = $aff["utilisateurs"] ?? [];
            foreach ($utilisateurs as $utilisateur) {
                $users[] = Utilisateur::construireDepuisTableau($utilisateur);
            }
        }
        return $users;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): void
    {
        $this->prenom = $prenom;
    }

    public function getMdpHache(): string
    {
        return $this->mdpHache;
    }

    public function setMdpHache(string $mdpHache): void
    {
        $this->mdpHache = $mdpHache;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function formatTablleauUtilisateurPourJson() : array {
        return [
            "login" => $this->login,
            "nom" => $this->nom,
            "prenom" => $this->prenom,
            "email" => $this->email,
            "mdphache" => $this->mdpHache
        ];
    }

    public static function formatJsonListeUtilisateurs($utilisateurs) : string {
        $utilisateursToJson = [];
        foreach ($utilisateurs as $utilisateur) {
            $utilisateursToJson[] = $utilisateur->formatTablleauUtilisateurPourJson();
        };
        return json_encode(["utilisateurs" => $utilisateursToJson]);
    }

    public function formatTableau(): array
    {
        return array(
            "loginTag" => $this->login,
            "nomTag" => $this->nom,
            "prenomTag" => $this->prenom,
            "emailTag" => $this->email,
            "mdphacheTag" => $this->mdpHache
        );
    }
}