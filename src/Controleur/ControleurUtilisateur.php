<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Service\Exception\ServiceConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\UtilisateurService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurUtilisateur extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $statusCode = "utilisateur"): Response
    {
        return parent::afficherErreur($messageErreur, $statusCode);
    }

    #[Route(path: '/utilisateur', name: 'afficherDetail', methods: ["GET"])]
    public static function afficherDetail(): Response
    {
        try {
            $utilisateur = (new UtilisateurService())->getUtilisateurCourrant();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        }
        return ControleurUtilisateur::afficherTwig('utilisateur/detail.html.twig', [
            "utilisateur" => $utilisateur
        ]);
    }

    #[Route(path: '/inscription', name: 'afficherFormulaireCreation', methods: ["GET"])]
    public static function afficherFormulaireCreation(): Response
    {
        try {
            (new UtilisateurService())->doitEtreDeconnecte();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurTableau::redirection("afficherListeMesTableaux");
        }
        return ControleurUtilisateur::afficherTwig(
            "utilisateur/formulaireCreation.html.twig");
    }

    #[Route(path: '/inscription', name: 'creerDepuisFormulaire', methods: ["POST"])]
    public static function creerDepuisFormulaire(): Response
    {
        $login = $_REQUEST["login"] ?? null;
        $nom = $_REQUEST["nom"] ?? null;
        $prenom = $_REQUEST["prenom"] ?? null;
        $mdp = $_REQUEST["mdp"] ?? null;
        $mdp2 = $_REQUEST["mdp2"] ?? null;
        $email = $_REQUEST["email"] ?? null;
        try {
            (new UtilisateurService())->creerDepuisFormulaire($login, $nom, $prenom, $mdp, $mdp2, $email);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurGenerique::redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return ControleurUtilisateur::afficherTwig("utilisateur/afficherFormulaireCreation.html.twig");
        }
        MessageFlash::ajouter("success", "L'utilisateur a bien été créé !");
        return self::redirection("afficherFormulaireConnexion");

    }

    #[Route(path: '/utilisateur/modification', name: 'afficherFormulaireMiseAJour', methods: ["GET"])]
    public static function afficherFormulaireMiseAJour(): Response
    {
        try {
            $utilisateur = (new UtilisateurService())->getUtilisateurCourrant();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        }
        return ControleurUtilisateur::afficherTwig("utilisateur/formulaireMiseAJour.html.twig", [
            "utilisateur" => $utilisateur,
        ]);
    }

    #[Route(path: '/utilisateur/modification-motdepasse', name: 'afficherFormulaireMiseAJourMdp', methods: ["GET"])]
    public static function afficherFormulaireMiseAJourMdp(): Response
    {
        try {
            $utilisateur = (new UtilisateurService())->getUtilisateurCourrant();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        }
        return ControleurUtilisateur::afficherTwig("utilisateur/formulaireMiseAJourMdp.html.twig", [
            "utilisateur" => $utilisateur,
        ]);
    }

    #[Route(path: '/utilisateur/modification', name: 'mettreAJour', methods: ["POST"])]
    public static function mettreAJour(): Response
    {
        $nom = $_POST["nom"] ?? null;
        $prenom = $_POST["prenom"] ?? null;
        $email = $_POST["email"] ?? null;
        $mdp = $_POST["mdp"] ?? null;
        try {
            (new UtilisateurService())->miseAJour($nom , $prenom , $email , $mdp);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return ControleurUtilisateur::redirection("afficherFormulaireMiseAJour");
        }
        MessageFlash::ajouter("success", "Vos informations ont bien été modifiées !");
        return ControleurUtilisateur::redirection("afficherDetail");
    }

    #[Route(path: '/utilisateur/modification-motdepasse', name: 'mettreAJourMdp', methods: ["POST"])]
    public static function mettreAJourMdp(): Response
    {
        $mdpAncien = $_POST["mdpAncien"];
        $mdp = $_POST["mdp"];
        $mdp2 = $_POST["mdp2"];
        try {
            (new UtilisateurService())->miseAJourMdp($mdpAncien, $mdp, $mdp2);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return ControleurUtilisateur::redirection("afficherFormulaireMiseAJourMdp");
        }
        MessageFlash::ajouter("success", "Le mot de passe a bien été modifié !");
        return ControleurUtilisateur::redirection("afficherDetail");

    }

    #[Route(path: '/suppression-compte', name: 'supprimer', methods: ["GET"])]
    public static function supprimer(): Response
    {
        try {
            (new UtilisateurService())->suppresion();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::redirection("afficherFormulaireConnexion");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return ControleurUtilisateur::redirection("afficherDetail");
        }
        MessageFlash::ajouter("success", "Votre compte a bien été supprimé !");
        return ControleurUtilisateur::redirection("afficherFormulaireConnexion");

    }

    #[Route(path: '/connexion', name: 'afficherFormulaireConnexion', methods: ["GET"])]
    public static function afficherFormulaireConnexion(): Response
    {
        try {
            (new UtilisateurService())->doitEtreDeconnecte();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurTableau::redirection("afficherListeMesTableaux");
        }
        return ControleurUtilisateur::afficherTwig(
            "utilisateur/formulaireConnexion.html.twig"
        );
    }

    #[Route(path: '/connexion', name: 'connecter', methods: ["POST"])]
    public static function connecter(): Response
    {
        $login = $_POST["login"] ?? null;
        $mdp = $_POST["mdp"] ?? null;
        try {
            (new UtilisateurService())->connexion($login, $mdp);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurTableau::redirection("afficherListeMesTableaux");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return ControleurUtilisateur::redirection("afficherFormulaireConnexion");
        }
        MessageFlash::ajouter("success", "Connexion effectuée.");
        return ControleurUtilisateur::redirection("afficherListeMesTableaux");
    }

    #[Route(path: '/deconnexion', name: 'deconnecter', methods: ["GET"])]
    public static function deconnecter(): Response
    {
        try {
            (new UtilisateurService())->deconnexion();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::redirection("accueil");
        }
        MessageFlash::ajouter("success", "L'utilisateur a bien été déconnecté.");
        return ControleurUtilisateur::redirection("accueil");
    }

    #[Route(path: '/recuperation-compte', name: 'afficherFormulaireRecuperationCompte', methods: ["GET"])]
    public static function afficherFormulaireRecuperationCompte(): Response
    {
        try {
            (new UtilisateurService())->doitEtreDeconnecte();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return ControleurTableau::redirection("afficherListeMesTableaux");
        }
        return ControleurUtilisateur::afficherTwig(
            "utilisateur/resetCompte.html.twig"
        );
    }

    #[Route(path: '/recuperation-compte', name: 'recupererCompte', methods: ["POST"])]
    public static function recupererCompte(): Response
    {
        $mail = $_POST["email"] ?? null;
        try {
            (new UtilisateurService())->recupererCompte($mail);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return ControleurUtilisateur::redirection("afficherFormulaireRecuperationCompte");
        }
        return ControleurUtilisateur::afficherTwig("utilisateur/resultatResetCompte.html.twig");
    }

    #[Route(path: '/recuperation-compte/validation', name: 'changementMotDePasseRecuperation', methods: ["POST"])]
    public static function changementMotDePasseRecuperation(): Response
    {
        $nonce = $_POST["nonce"] ?? null;
        $mdp = $_POST["mdp"] ?? null;
        $mdp2 = $_POST["mdp2"] ?? null;
        try {
            (new UtilisateurService())->changementMotDePasseRecuperation($nonce, $mdp, $mdp2);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurGenerique::redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return ControleurUtilisateur::afficherTwig("utilisateur/resultatResetCompte.html.twig");
        }
        MessageFlash::ajouter("success", "Mot de passe modifié, veuillez vous connecter");
        return self::redirection("afficherFormulaireConnexion");
    }
}