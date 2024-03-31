<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MailerBase;
use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\HTTP\Session;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Service\Exception\ServiceConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;
use Exception;

class UtilisateurService {
    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function recupererCompte (?string $mail): void {
        //LIGNE A SUPPRIMER
        Session::getInstance()->supprimer("recupMdp");
        if (Session::getInstance()->contient("recupMdp")) return;
        if (ConnexionUtilisateur::estConnecte()) throw new ServiceConnexionException("L'utilisateur est déjà connecté");
        if (is_null($mail)) throw new ServiceException("Veuillez saisir un mail");
        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = (new UtilisateurRepository())->recupererUtilisateurParEmail($mail);
        if (is_null($utilisateur)) throw new ServiceException("L'email n'existe pas");
        //TODO: temporaire
        try {
            $tab = array(
                "nonce" => MotDePasse::genererChaineAleatoire(5),
                "mail" => $mail
            );
        } catch (Exception) {
            throw new ServiceException("Erreur dans la génération du code");
        }
        Session::getInstance()->enregistrer("recupMdp",$tab);
        echo $tab["nonce"];
        echo $tab["mail"];
        MailerBase::envoyerMail(
            $mail,
            "Récupération de votre compte",
            "
                <p>Bonjour,</p>
                <p>Suite à une requête de votre part, nous vous communiquons le code de réinitialisation suivant : 
                <strong>" . $tab["nonce"] . "</strong></p>
                <p>Si cette requête ne viens pas de vous, veuillez ignorer ce mail et ne communiquer ce code à quiconque</p>
            "
        );
    }
}