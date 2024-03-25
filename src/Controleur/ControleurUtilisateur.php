<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\HTTP\Cookie;
use App\Trellotrolle\Modele\Repository\AffecteRepository;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\ParticipeRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use Symfony\Component\Routing\Attribute\Route;

class ControleurUtilisateur extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $controleur = ""): void
    {
        parent::afficherErreur($messageErreur, "utilisateur");
    }

    #[Route(path: '/utilisateur', name:'afficherDetail', methods:["GET"])]
    public static function afficherDetail(): void
    {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        $utilisateur = (new UtilisateurRepository())->recupererParClePrimaire(array("login"=>ConnexionUtilisateur::getLoginUtilisateurConnecte()));
        ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "utilisateur" => $utilisateur,
            "pagetitle" => "Détail de l'utilisateur {$utilisateur->getLogin()}",
            "cheminVueBody" => "utilisateur/detail.php"
        ]);
    }

    #[Route(path: '/inscription', name:'afficherFormulaireCreation', methods:["GET"])]
    public static function afficherFormulaireCreation(): void
    {
        if(ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherListeMesTableaux");
        }
        ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "pagetitle" => "Création d'un utilisateur",
            "cheminVueBody" => "utilisateur/formulaireCreation.php"
        ]);
    }

    #[Route(path: '/inscription', name:'creerDepuisFormulaire', methods:["POST"])]
    public static function creerDepuisFormulaire(): void
    {
        if(ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherListeMesTableaux");
        }
        if (ControleurUtilisateur::issetAndNotNull(["login", "prenom", "nom", "mdp", "mdp2", "email"])) {
            if ($_REQUEST["mdp"] !== $_REQUEST["mdp2"]) {
                MessageFlash::ajouter("warning", "Mots de passe distincts.");
                ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireCreation");
            }

            if (!filter_var($_REQUEST["email"], FILTER_VALIDATE_EMAIL)) {
                MessageFlash::ajouter("warning", "Email non valide");
                ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireCreation");
            }

            $utilisateurRepository = new UtilisateurRepository();

            $checkUtilisateur = $utilisateurRepository->recupererParClePrimaire(array("login"=>$_REQUEST["login"]));
            if($checkUtilisateur) {
                MessageFlash::ajouter("warning", "Le login est déjà pris.");
                ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireCreation");
            }

            $mdpHache = MotDePasse::hacher($_REQUEST["mdp"]);

            $utilisateurRepository = new UtilisateurRepository();

            $utilisateur = new Utilisateur($_REQUEST["login"],$_REQUEST["nom"],$_REQUEST["prenom"],$_REQUEST["email"],$mdpHache);
            $succesSauvegarde = $utilisateurRepository->ajouter($utilisateur);

            if ($succesSauvegarde) {
                MessageFlash::ajouter("success", "L'utilisateur a bien été créé !");
                ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireConnexion");
            }
            else {
                MessageFlash::ajouter("warning", "Une erreur est survenue lors de la création de l'utilisateur.");
                ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireCreation");
            }
        } else {
            MessageFlash::ajouter("danger", "Login, nom, prenom, email ou mot de passe manquant.");
            ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireCreation");
        }
    }

    #[Route(path: '/utilisateur/modification', name:'afficherFormulaireMiseAJour', methods:["GET"])]
    public static function afficherFormulaireMiseAJour(): void
    {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        $login = ConnexionUtilisateur::getLoginUtilisateurConnecte();
        $repository = new UtilisateurRepository();
        $utilisateur = $repository->recupererParClePrimaire(array("login"=>$login));
        ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "pagetitle" => "Mise à jour du profil",
            "cheminVueBody" => "utilisateur/formulaireMiseAJour.php",
            "utilisateur" => $utilisateur,
        ]);
    }

    #[Route(path: '/utilisateur/modification', name:'mettreAJour', methods:["POST"])]
    public static function mettreAJour(): void
    {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
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
                ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireMiseAJour");
            }

            if (!filter_var($_REQUEST["email"], FILTER_VALIDATE_EMAIL)) {
                MessageFlash::ajouter("warning", "Email non valide");
                ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireMiseAJour");
            }

            if (!(MotDePasse::verifier($_REQUEST["mdpAncien"], $utilisateur->getMdpHache()))) {
                MessageFlash::ajouter("warning", "Ancien mot de passe erroné.");
                ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireMiseAJour");
            }

            if ($_REQUEST["mdp"] !== $_REQUEST["mdp2"]) {
                MessageFlash::ajouter("warning", "Mots de passe distincts.");
                ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireMiseAJour");
            }

            $utilisateur->setNom($_REQUEST["nom"]);
            $utilisateur->setPrenom($_REQUEST["prenom"]);
            $utilisateur->setEmail($_REQUEST["email"]);
            $utilisateur->setMdpHache(MotDePasse::hacher($_REQUEST["mdp"]));

            $repository->mettreAJour($utilisateur);

            MessageFlash::ajouter("success", "L'utilisateur a bien été modifié !");
            ControleurUtilisateur::redirection("tableau", "afficherListeMesTableaux");
        } else {
            MessageFlash::ajouter("danger", "Login, nom, prenom, email ou mot de passe manquant.");
            ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireMiseAJour");
        }
    }

    #[Route(path: '/suppression-compte', name:'supprimer', methods:["GET"])]
    public static function supprimer(): void
    {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if (!ControleurUtilisateur::issetAndNotNull(["login"])) {
            MessageFlash::ajouter("warning", "Login manquant");
            ControleurUtilisateur::redirection("utilisateur", "afficherDetail");
        }
        $login = $_REQUEST["login"];

        $repository = new UtilisateurRepository();
        $repository->supprimer($login);
        ConnexionUtilisateur::deconnecter();
        MessageFlash::ajouter("success", "Votre compte a bien été supprimé !");
        ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireConnexion");
    }

    #[Route(path: '/connexion', name:'afficherFormulaireConnexion', methods:["GET"])]
    public static function afficherFormulaireConnexion(): void
    {
        if(ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherListeMesTableaux");
        }
        ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "pagetitle" => "Formulaire de connexion",
            "cheminVueBody" => "utilisateur/formulaireConnexion.php"
        ]);
    }

    #[Route(path: '/connexion', name:'connecter', methods:["POST"])]
    public static function connecter(): void
    {
        if(ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherListeMesTableaux");
        }
        if (!ControleurUtilisateur::issetAndNotNull(["login", "mdp"])) {
            MessageFlash::ajouter("danger", "Login ou mot de passe manquant.");
            ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        $utilisateurRepository = new UtilisateurRepository();
        /** @var Utilisateur $utilisateur */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire(array("login"=>$_REQUEST["login"]));

        if ($utilisateur == null) {
            MessageFlash::ajouter("warning", "Login inconnu.");
            ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireConnexion");
        }

        if (!MotDePasse::verifier($_REQUEST["mdp"], $utilisateur->getMdpHache())) {
            MessageFlash::ajouter("warning", "Mot de passe incorrect.");
            ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireConnexion");
        }

        ConnexionUtilisateur::connecter($utilisateur->getLogin());
        MessageFlash::ajouter("success", "Connexion effectuée.");
        ControleurUtilisateur::redirection("tableau", "afficherListeMesTableaux");
    }

    #[Route(path: '/deconnexion', name:'deconnecter', methods:["GET"])]
    public static function deconnecter(): void
    {
        if (!ConnexionUtilisateur::estConnecte()) {
            MessageFlash::ajouter("danger", "Utilisateur non connecté.");
            ControleurUtilisateur::redirection("base", "accueil");
        }
        ConnexionUtilisateur::deconnecter();
        MessageFlash::ajouter("success", "L'utilisateur a bien été déconnecté.");
        ControleurUtilisateur::redirection("base", "accueil");
    }

    #[Route(path: '/recuperation-compte', name:'afficherFormulaireRecuperationCompte', methods:["GET"])]
    public static function afficherFormulaireRecuperationCompte(): void {
        if(ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherListeMesTableaux");
        }
        ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "pagetitle" => "Récupérer mon compte",
            "cheminVueBody" => "utilisateur/resetCompte.php"
        ]);
    }

    #[Route(path: '/recuperation-compte', name:'recupererCompte', methods:["POST"])]
    public static function recupererCompte(): void {
        if(ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherListeMesTableaux");
        }
        if (!ControleurUtilisateur::issetAndNotNull(["email"])) {
            MessageFlash::ajouter("warning", "Adresse email manquante");
            ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        $repository = new UtilisateurRepository();
        $utilisateurs = $repository->recupererUtilisateursParEmail($_REQUEST["email"]);
        if(empty($utilisateurs)) {
            MessageFlash::ajouter("warning", "Aucun compte associé à cette adresse email");
            ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "pagetitle" => "Récupérer mon compte",
            "cheminVueBody" => "utilisateur/resultatResetCompte.php",
            "utilisateurs" => $utilisateurs
        ]);
    }
}