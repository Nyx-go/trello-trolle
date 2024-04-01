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

class UtilisateurService extends ServiceGenerique
{
    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function recupererCompte(?string $mail): void
    {
        if (Session::getInstance()->contient("recupMdp")) return;
        self::doitEtreDeconnecte();
        if (is_null($mail)) throw new ServiceException("Veuillez saisir un mail");
        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = (new UtilisateurRepository())->recupererUtilisateurParEmail($mail);
        if (is_null($utilisateur)) throw new ServiceException("L'email n'existe pas");
        try {
            $tab = array(
                "nonce" => MotDePasse::genererChaineAleatoire(5),
                "mail" => $mail
            );
        } catch (Exception) {
            throw new ServiceException("Erreur dans la génération du code");
        }
        if (!MailerBase::envoyerMail(
            $mail,
            "Récupération de votre compte",
            "
                <p>Bonjour,</p>" . htmlspecialchars($utilisateur->getLogin()) . "
                <p>Suite à une requête de votre part, nous vous communiquons le code de réinitialisation suivant :
                <strong>" . $tab["nonce"] . "</strong></p>
                <p>Si cette requête ne viens pas de vous, veuillez ignorer ce mail et ne communiquez ce code à quiconque</p>
            "
        )) {
            throw new ServiceConnexionException("Erreur de connexion de notre service mail, veuillez réessayez plus tard");
        }
        Session::getInstance()->enregistrer("recupMdp", $tab);
    }

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function changementMotDePasseRecuperation(?string $nonce, ?string $mdp, ?string $mdp2): void
    {
        self::doitEtreDeconnecte();
        if (!Session::getInstance()->contient("recupMdp")) {
            throw new ServiceConnexionException("Accès invalide");
        }
        $tab = Session::getInstance()->lire("recupMdp");
        if (is_null($nonce) || $nonce !== $tab["nonce"]) {
            Session::getInstance()->supprimer("recupMdp");
            throw new ServiceConnexionException("Veuillez refaire l'action de mot de passe oublié");
        }
        $this->verifyDoublePasswordValidity($mdp, $mdp2);
        Session::getInstance()->supprimer("recupMdp");
        /** @var Utilisateur $utilisateur */
        $utilisateur = (new UtilisateurRepository())->recupererUtilisateurParEmail($tab["mail"]);
        if (is_null($utilisateur)) throw new ServiceConnexionException("L'utilisateur n'existe plus");
        $utilisateur->setMdpHache(MotDePasse::hacher($mdp));
        (new UtilisateurRepository())->mettreAJour($utilisateur);
    }
}