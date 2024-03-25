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
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
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
        $utilisateur = (new UtilisateurRepository())->recupererParClePrimaire(ConnexionUtilisateur::getLoginUtilisateurConnecte());
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

            $checkUtilisateur = $utilisateurRepository->recupererParClePrimaire($_REQUEST["login"]);
            if($checkUtilisateur) {
                MessageFlash::ajouter("warning", "Le login est déjà pris.");
                ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireCreation");
            }

            $tableauRepository = new TableauRepository();
            $colonneRepository = new ColonneRepository();
            $carteRepository = new CarteRepository();

            $mdpHache = MotDePasse::hacher($_REQUEST["mdp"]);
            $idTableau = $tableauRepository->getNextIdTableau();
            $codeTableau = hash("sha256", $_REQUEST["login"].$idTableau);
            $tableauInitial = "Mon tableau";

            $idColonne1 = $colonneRepository->getNextIdColonne();
            $colonne1 = "TODO";

            $colonne2 = "DOING";
            $idColonne2 = $idColonne1 + 1;

            $colonne3 = "DONE";
            $idColonne3 = $idColonne1 + 2;

            $carteInitiale = "Exemple";
            $descriptifInitial = "Exemple de carte";
            $idCarte1 = $carteRepository->getNextIdCarte();
            $idCarte2 = $idCarte1 + 1;
            $idCarte3 = $idCarte1 + 2;

            $tableau = new Tableau(
                new Utilisateur(
                    $_REQUEST["login"],
                    $_REQUEST["nom"],
                    $_REQUEST["prenom"],
                    $_REQUEST["email"],
                    $mdpHache,
                    $_REQUEST["mdp"],
                ),
                $idTableau,
                $codeTableau,
                $tableauInitial,
                [],
            );

            $carte1 = new Carte(
                new Colonne(
                    $tableau,
                    $idColonne1,
                    $colonne1,
                ),
                $idCarte1,
                $carteInitiale,
                $descriptifInitial,
                "#FFFFFF",
                []
            );

            $carte2 = new Carte(
                new Colonne(
                    $tableau,
                    $idColonne2,
                    $colonne2,
                ),
                $idCarte2,
                $carteInitiale,
                $descriptifInitial,
                "#FFFFFF",
                []
            );

            $carte3 = new Carte(
                new Colonne(
                    $tableau,
                    $idColonne3,
                    $colonne3,
                ),
                $idCarte3,
                $carteInitiale,
                $descriptifInitial,
                "#FFFFFF",
                []
            );

            $succesSauvegarde = $carteRepository->ajouter($carte1) && $carteRepository->ajouter($carte2) && $carteRepository->ajouter($carte3);
            if ($succesSauvegarde) {
                Cookie::enregistrer("login", $_REQUEST["login"]);
                Cookie::enregistrer("mdp", $_REQUEST["mdp"]);
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
        $utilisateur = $repository->recupererParClePrimaire($login);
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
            $utilisateur = $repository->recupererParClePrimaire($login);

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
            $utilisateur->setMdp($_REQUEST["mdp"]);

            $repository->mettreAJour($utilisateur);

            $carteRepository = new CarteRepository();
            $cartes = $carteRepository->recupererCartesUtilisateur($login);
            foreach ($cartes as $carte) {
                $participants = $carte->getAffectationsCarte();
                $participants = array_filter($participants, function ($u) use ($login) {return $u->getLogin() !== $login;});
                $participants[] = $utilisateur;
                $carte->setAffectationsCarte($participants);
                $carteRepository->mettreAJour($carte);
            }

            $tableauRepository = new TableauRepository();
            $tableaux = $tableauRepository->recupererTableauxParticipeUtilisateur($login);
            foreach ($tableaux as $tableau) {
                $participants = $tableau->getParticipants();
                $participants = array_filter($participants, function ($u) use ($login) {return $u->getLogin() !== $login;});
                $participants[] = $utilisateur;
                $tableau->setParticipants($participants);
                $tableauRepository->mettreAJour($tableau);
            }

            Cookie::enregistrer("mdp", $_REQUEST["mdp"]);

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

        $carteRepository = new CarteRepository();
        $cartes = $carteRepository->recupererCartesUtilisateur($login);
        foreach ($cartes as $carte) {
            $participants = $carte->getAffectationsCarte();
            $participants = array_filter($participants, function ($u) use ($login) {return $u->getLogin() !== $login;});
            $carte->setAffectationsCarte($participants);
            $carteRepository->mettreAJour($carte);
        }

        $tableauRepository = new TableauRepository();
        $tableaux = $tableauRepository->recupererTableauxParticipeUtilisateur($login);
        foreach ($tableaux as $tableau) {
            $participants = $tableau->getParticipants();
            $participants = array_filter($participants, function ($u) use ($login) {return $u->getLogin() !== $login;});
            $tableau->setParticipants($participants);
            $tableauRepository->mettreAJour($tableau);
        }
        $repository = new UtilisateurRepository();
        $repository->supprimer($login);
        Cookie::supprimer("login");
        Cookie::supprimer("mdp");
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
        $utilisateur = $utilisateurRepository->recupererParClePrimaire($_REQUEST["login"]);

        if ($utilisateur == null) {
            MessageFlash::ajouter("warning", "Login inconnu.");
            ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireConnexion");
        }

        if (!MotDePasse::verifier($_REQUEST["mdp"], $utilisateur->getMdpHache())) {
            MessageFlash::ajouter("warning", "Mot de passe incorrect.");
            ControleurUtilisateur::redirection("utilisateur", "afficherFormulaireConnexion");
        }

        ConnexionUtilisateur::connecter($utilisateur->getLogin());
        Cookie::enregistrer("login", $_REQUEST["login"]);
        Cookie::enregistrer("mdp", $_REQUEST["mdp"]);
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