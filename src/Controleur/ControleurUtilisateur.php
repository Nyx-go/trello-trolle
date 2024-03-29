<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurUtilisateur extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $statusCode = "utilisateur"): Response
    {
        return parent::afficherErreur($messageErreur, $statusCode);
    }

    #[Route(path: '/utilisateur', name:'afficherDetail', methods:["GET"])]
    public static function afficherDetail(): Response
    {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        }
        $utilisateur = (new UtilisateurRepository())->recupererParClePrimaire(array("login"=>ConnexionUtilisateur::getLoginUtilisateurConnecte()));
        return ControleurUtilisateur::afficherTwig('utilisateur/detail.html.twig', [
            "utilisateur" => $utilisateur
        ]);
    }

    #[Route(path: '/inscription', name:'afficherFormulaireCreation', methods:["GET"])]
    public static function afficherFormulaireCreation(): Response
    {
        if(ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherListeMesTableaux");
        }
        return ControleurUtilisateur::afficherTwig(
            "utilisateur/formulaireCreation.html.twig");
    }

    #[Route(path: '/inscription', name:'creerDepuisFormulaire', methods:["POST"])]
    public static function creerDepuisFormulaire(): Response
    {
        if(ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherListeMesTableaux");
        }
        if (ControleurUtilisateur::issetAndNotNull(["login", "prenom", "nom", "mdp", "mdp2", "email"])) {
            if ($_REQUEST["mdp"] !== $_REQUEST["mdp2"]) {
                MessageFlash::ajouter("warning", "Mots de passe distincts.");
                return ControleurUtilisateur::redirection("afficherFormulaireCreation");
            }

            if (!filter_var($_REQUEST["email"], FILTER_VALIDATE_EMAIL)) {
                MessageFlash::ajouter("warning", "Email non valide");
                return ControleurUtilisateur::redirection("afficherFormulaireCreation");
            }

            $utilisateurRepository = new UtilisateurRepository();

            $checkUtilisateur = $utilisateurRepository->recupererParClePrimaire(array("login"=>$_REQUEST["login"]));
            if($checkUtilisateur) {
                MessageFlash::ajouter("warning", "Le login est déjà pris.");
                return ControleurUtilisateur::redirection("afficherFormulaireCreation");
            }

            $mdpHache = MotDePasse::hacher($_REQUEST["mdp"]);

            $utilisateurRepository = new UtilisateurRepository();

            $utilisateur = new Utilisateur($_REQUEST["login"],$_REQUEST["nom"],$_REQUEST["prenom"],$_REQUEST["email"],$mdpHache);
            $succesSauvegarde = $utilisateurRepository->ajouter($utilisateur);

            if ($succesSauvegarde) {
                MessageFlash::ajouter("success", "L'utilisateur a bien été créé !");
                return ControleurUtilisateur::redirection("afficherFormulaireConnexion");
            }
            else {
                MessageFlash::ajouter("warning", "Une erreur est survenue lors de la création de l'utilisateur.");
                return ControleurUtilisateur::redirection("afficherFormulaireCreation");
            }
        } else {
            MessageFlash::ajouter("danger", "Login, nom, prénom, email ou mot de passe manquant.");
            return ControleurUtilisateur::redirection("afficherFormulaireCreation");
        }
    }

    #[Route(path: '/utilisateur/modification', name:'afficherFormulaireMiseAJour', methods:["GET"])]
    public static function afficherFormulaireMiseAJour(): Response
    {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        }
        $login = ConnexionUtilisateur::getLoginUtilisateurConnecte();
        $repository = new UtilisateurRepository();
        $utilisateur = $repository->recupererParClePrimaire(array("login"=>$login));
        return ControleurUtilisateur::afficherTwig("utilisateur/formulaireMiseAJour.html.twig",[
            "utilisateur" => $utilisateur,
        ]);
    }

    #[Route(path: '/utilisateur/modification', name:'mettreAJour', methods:["POST"])]
    public static function mettreAJour(): Response
    {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        }
        if (ControleurUtilisateur::issetAndNotNull(["login", "prenom", "nom", "mdp", "mdp2", "email"])) {
            $login = $_REQUEST['login'];
            $repository = new UtilisateurRepository();

            /**
             * @var Utilisateur $utilisateur
             */
            $utilisateur = $repository->recupererParClePrimaire(array("login"=>$login));

            if(!$utilisateur) {
                MessageFlash::ajouter("danger", "L'utilisateur n'existe pas");
                return ControleurUtilisateur::redirection("afficherFormulaireMiseAJour");
            }

            if (!filter_var($_REQUEST["email"], FILTER_VALIDATE_EMAIL)) {
                MessageFlash::ajouter("warning", "Email non valide");
                return ControleurUtilisateur::redirection("afficherFormulaireMiseAJour");
            }

            if (!(MotDePasse::verifier($_REQUEST["mdpAncien"], $utilisateur->getMdpHache()))) {
                MessageFlash::ajouter("warning", "Ancien mot de passe erroné.");
                return ControleurUtilisateur::redirection("afficherFormulaireMiseAJour");
            }

            if ($_REQUEST["mdp"] !== $_REQUEST["mdp2"]) {
                MessageFlash::ajouter("warning", "Mots de passe distincts.");
                return ControleurUtilisateur::redirection("afficherFormulaireMiseAJour");
            }

            $utilisateur->setNom($_REQUEST["nom"]);
            $utilisateur->setPrenom($_REQUEST["prenom"]);
            $utilisateur->setEmail($_REQUEST["email"]);
            $utilisateur->setMdpHache(MotDePasse::hacher($_REQUEST["mdp"]));

            $repository->mettreAJour($utilisateur);

            MessageFlash::ajouter("success", "L'utilisateur a bien été modifié !");
            return ControleurUtilisateur::redirection("afficherListeMesTableaux");
        } else {
            MessageFlash::ajouter("danger", "Login, nom, prénom, email ou mot de passe manquant.");
            return ControleurUtilisateur::redirection("afficherFormulaireMiseAJour");
        }
    }

    #[Route(path: '/suppression-compte', name:'supprimer', methods:["GET"])]
    public static function supprimer($login1): Response
    {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        }
        if (!ControleurUtilisateur::issetAndNotNull(["login"])) {
            MessageFlash::ajouter("warning", "Login manquant");
            return ControleurUtilisateur::redirection("afficherDetail");
        }

        $repository = new UtilisateurRepository();
        $repository->supprimer($login);
        ConnexionUtilisateur::deconnecter();
        MessageFlash::ajouter("success", "Votre compte a bien été supprimé !");
        return ControleurUtilisateur::redirection("afficherFormulaireConnexion");
    }

    #[Route(path: '/connexion', name:'afficherFormulaireConnexion', methods:["GET"])]
    public static function afficherFormulaireConnexion(): Response
    {
        if(ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherListeMesTableaux");
        }
        return ControleurUtilisateur::afficherTwig(
            "utilisateur/formulaireConnexion.html.twig"
        );
    }

    #[Route(path: '/connexion', name:'connecter', methods:["POST"])]
    public static function connecter(): Response
    {
        if(ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherListeMesTableaux");
        }
        if (!ControleurUtilisateur::issetAndNotNull(["login", "mdp"])) {
            MessageFlash::ajouter("danger", "Login ou mot de passe manquant.");
            return ControleurUtilisateur::redirection("afficherFormulaireConnexion");
        }
        $utilisateurRepository = new UtilisateurRepository();
        /** @var Utilisateur $utilisateur */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire(array("login"=>$_REQUEST["login"]));

        if ($utilisateur == null) {
            MessageFlash::ajouter("warning", "Login inconnu.");
            return ControleurUtilisateur::redirection("afficherFormulaireConnexion");
        }

        if (!MotDePasse::verifier($_REQUEST["mdp"], $utilisateur->getMdpHache())) {
            MessageFlash::ajouter("warning", "Mot de passe incorrect.");
            return ControleurUtilisateur::redirection("afficherFormulaireConnexion");
        }

        ConnexionUtilisateur::connecter($utilisateur->getLogin());
        MessageFlash::ajouter("success", "Connexion effectuée.");
        return ControleurUtilisateur::redirection("afficherListeMesTableaux");
    }

    #[Route(path: '/deconnexion', name:'deconnecter', methods:["GET"])]
    public static function deconnecter(): Response
    {
        if (!ConnexionUtilisateur::estConnecte()) {
            MessageFlash::ajouter("danger", "Utilisateur non connecté.");
            return ControleurUtilisateur::redirection("accueil");
        }
        ConnexionUtilisateur::deconnecter();
        MessageFlash::ajouter("success", "L'utilisateur a bien été déconnecté.");
        return ControleurUtilisateur::redirection("accueil");
    }

    #[Route(path: '/recuperation-compte', name:'afficherFormulaireRecuperationCompte', methods:["GET"])]
    public static function afficherFormulaireRecuperationCompte(): Response {
        if(ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherListeMesTableaux");
        }
        return ControleurUtilisateur::afficherTwig(
            "utilisateur/resetCompte.html.twig"
        );
    }

    #[Route(path: '/recuperation-compte', name:'recupererCompte', methods:["POST"])]
    public static function recupererCompte(): Response {
        if(ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherListeMesTableaux");
        }
        if (!ControleurUtilisateur::issetAndNotNull(["email"])) {
            MessageFlash::ajouter("warning", "Adresse email manquante");
            return ControleurUtilisateur::redirection("afficherFormulaireConnexion");
        }
        $repository = new UtilisateurRepository();
        $utilisateurs = $repository->recupererUtilisateursParEmail($_REQUEST["email"]);
        if(empty($utilisateurs)) {
            MessageFlash::ajouter("warning", "Aucun compte associé à cette adresse email");
            return ControleurUtilisateur::redirection("afficherFormulaireConnexion");
        }
        return ControleurUtilisateur::afficherTwig(
            "utilisateur/resultatResetCompte.html.twig",[
            "utilisateurs" => $utilisateurs
        ]);
    }
}