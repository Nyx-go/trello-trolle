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
                <p>Si cette requête ne vient pas de vous, veuillez ignorer ce mail et ne communiquez ce code à quiconque</p>
            "
        )) {
            throw new ServiceConnexionException("Erreur de connexion de notre service mail, veuillez réessayer plus tard");
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

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function creerDepuisFormulaire(?string $login, ?string $nom, ?string $prenom, ?string $mdp, ?string $mdp2, ?string $email): void
    {
        self::doitEtreDeconnecte();
        if (is_null($login) || is_null($nom) || is_null($prenom) || is_null($email)) throw new ServiceException("Login, nom, prénom, email ou mot de passe manquant.");

        self::verifyDoublePasswordValidity($mdp,$mdp2);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ServiceException("Email non valide.");
        }

        $utilisateurRepository = new UtilisateurRepository();

        $checkUtilisateur = $utilisateurRepository->recupererParClePrimaire(array("login" => $login));
        if ($checkUtilisateur) {
            throw new ServiceException("Le login est déjà pris.");
        }

        $mdpHache = MotDePasse::hacher($mdp);

        $utilisateurRepository = new UtilisateurRepository();

        $utilisateur = new Utilisateur($login, $nom, $prenom, $email, $mdpHache);
        $succesSauvegarde = $utilisateurRepository->ajouter($utilisateur);

        if (!$succesSauvegarde) {
            throw new ServiceException("Une erreur est survenue lors de la création de l'utilisateur.");
        }
    }

    /**
     * @throws ServiceConnexionException
     */
    public function deconnexion(): void
    {
        self::doitEtreConnecte();
        ConnexionUtilisateur::deconnecter();
    }

    /**
     * @throws ServiceConnexionException|ServiceException
     */
    public function connexion(?string $login, ?string $mdp): void
    {
        self::doitEtreDeconnecte();
        if (is_null($login) || is_null($mdp)) throw new ServiceException("Login ou mot de passe manquant");
        $utilisateurRepository = new UtilisateurRepository();
        /** @var Utilisateur $utilisateur */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire(array("login" => $login));
        if (is_null($utilisateur) || !MotDePasse::verifier($mdp, $utilisateur->getMdpHache())) throw new ServiceException("Login ou mot de passe incorrect");
        ConnexionUtilisateur::connecter($utilisateur->getLogin());
    }

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function suppresion(): void
    {
        self::doitEtreConnecte();
        $login = ConnexionUtilisateur::getLoginUtilisateurConnecte();
        $repository = new UtilisateurRepository();
        $succesSuppression = $repository->supprimer(array("login" => $login));
        if (!$succesSuppression) throw new ServiceException("Une erreur est survenue lors de la suppression de l'utilisateur.");
        ConnexionUtilisateur::deconnecter();
    }

    /**
     * @throws ServiceException
     */
    private function verifyDoublePasswordValidity(?string $mdp, ?string $mdp2): void
    {
        if (is_null($mdp) || is_null($mdp2)) throw new ServiceException("Veuillez saisir 2 mots de passe");
        if ($mdp2 !== $mdp) throw new ServiceException("Veuillez saisir 2 fois le même mot de passe");
        if (preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*_=+\-]).{6,50}$/", $mdp) !== 1) {
            throw new ServiceException("Veuillez saisir un paterne valide");
        }
    }

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function miseAJourMdp(?string $mdpAncien, ?string $mdp, ?string $mdp2): void
    {
        self::doitEtreConnecte();
        $login = ConnexionUtilisateur::getLoginUtilisateurConnecte();
        $repository = new UtilisateurRepository();
        /** @var Utilisateur $utilisateur */
        $utilisateur = $repository->recupererParClePrimaire(array("login" => $login));
        //uniquement là comme fail safe au cas où il y a un problème de deconnexion à un moment donné
        if (is_null($utilisateur)) {
            $this->deconnexion();
            throw new ServiceConnexionException("Erreur sur l'utilisateur");
        }
        if (is_null($mdpAncien)) throw new ServiceException("Mot de passe manquant");
        if (!(MotDePasse::verifier($mdpAncien, $utilisateur->getMdpHache()))) throw  new ServiceException("Ancien mot de passe erroné");
        $this->verifyDoublePasswordValidity($mdp, $mdp2);
        $utilisateur->setMdpHache(MotDePasse::hacher($mdp));
        $succesMiseAJour = $repository->mettreAJour($utilisateur);
        if (!$succesMiseAJour) throw new ServiceException("Une erreur est survenue lors de la modification des informations.");
    }

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function miseAJour(?string $nom, ?string $prenom, ?string $email, ?string $mdp): void
    {
        self::doitEtreConnecte();
        $login = ConnexionUtilisateur::getLoginUtilisateurConnecte();
        if (is_null($nom) || is_null($prenom) || is_null($email) || is_null($mdp)) throw new ServiceException("Information manquante");
        $repository = new UtilisateurRepository();
        /** @var Utilisateur $utilisateur */
        $utilisateur = $repository->recupererParClePrimaire(array("login" => $login));
        //uniquement là comme fail safe au cas où il y a un problème de deconnexion à un moment donné
        if (is_null($utilisateur)) {
            $this->deconnexion();
            throw new ServiceConnexionException("Erreur sur l'utilisateur");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new ServiceException("Email non valide");
        if (!(MotDePasse::verifier($mdp, $utilisateur->getMdpHache()))) throw  new ServiceException("Mot de passe erroné");
        $utilisateur->setNom($nom);
        $utilisateur->setPrenom($prenom);
        $utilisateur->setEmail($email);
        $succesMiseAJour = $repository->mettreAJour($utilisateur);
        if (!$succesMiseAJour) throw new ServiceException("Une erreur est survenue lors de la modification des informations.");
    }

    /**
     * @throws ServiceConnexionException
     */
    public function getUtilisateurCourrant(): Utilisateur
    {
        self::doitEtreConnecte();
        $login = ConnexionUtilisateur::getLoginUtilisateurConnecte();
        $repository = new UtilisateurRepository();
        $utilisateur = $repository->recupererParClePrimaire(array("login" => $login));
        //uniquement là comme fail safe au cas où il y a un problème de deconnexion à un moment donné ou quelqu'un supprime le compte sur un autre navigateur
        if (is_null($utilisateur)) {
            $this->deconnexion();
            throw new ServiceConnexionException("Erreur sur l'utilisateur");
        }

        /** @var Utilisateur $utilisateur */
        return $utilisateur;
    }
}