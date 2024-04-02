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
use App\Trellotrolle\Service\UtilisateurServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurUtilisateur extends ControleurGenerique
{
    
    public function __construct(private UtilisateurServiceInterface $utilisateurService)
    {
    }

    public  function afficherErreur($messageErreur = "", $statusCode = "utilisateur"): Response
    {
        return parent::afficherErreur($messageErreur, $statusCode);
    }

    #[Route(path: '/utilisateur', name: 'afficherDetail', methods: ["GET"])]
    public  function afficherDetail(): Response
    {
        try {
            $utilisateur = $this->utilisateurService->getUtilisateurCourrant();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("afficherFormulaireConnexion");
        }
        return $this->afficherTwig('utilisateur/detail.html.twig', [
            "utilisateur" => $utilisateur
        ]);
    }

    #[Route(path: '/inscription', name: 'afficherFormulaireCreation', methods: ["GET"])]
    public  function afficherFormulaireCreation(): Response
    {
        try {
            $this->utilisateurService->doitEtreDeconnecte();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("afficherListeMesTableaux");
        }
        return $this->afficherTwig(
            "utilisateur/formulaireCreation.html.twig");
    }

    #[Route(path: '/inscription', name: 'creerDepuisFormulaire', methods: ["POST"])]
    public  function creerDepuisFormulaire(): Response
    {
        $login = $_REQUEST["login"] ?? null;
        $nom = $_REQUEST["nom"] ?? null;
        $prenom = $_REQUEST["prenom"] ?? null;
        $mdp = $_REQUEST["mdp"] ?? null;
        $mdp2 = $_REQUEST["mdp2"] ?? null;
        $email = $_REQUEST["email"] ?? null;
        try {
            $this->utilisateurService->creerDepuisFormulaire($login, $nom, $prenom, $mdp, $mdp2, $email);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->afficherTwig("utilisateur/afficherFormulaireCreation.html.twig");
        }
        MessageFlash::ajouter("success", "L'utilisateur a bien été créé !");
        return self::redirection("afficherFormulaireConnexion");

    }

    #[Route(path: '/utilisateur/modification', name: 'afficherFormulaireMiseAJour', methods: ["GET"])]
    public  function afficherFormulaireMiseAJour(): Response
    {
        try {
            $utilisateur = $this->utilisateurService->getUtilisateurCourrant();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("afficherFormulaireConnexion");
        }
        return $this->afficherTwig("utilisateur/formulaireMiseAJour.html.twig", [
            "utilisateur" => $utilisateur,
        ]);
    }

    #[Route(path: '/utilisateur/modification-motdepasse', name: 'afficherFormulaireMiseAJourMdp', methods: ["GET"])]
    public  function afficherFormulaireMiseAJourMdp(): Response
    {
        try {
            $utilisateur = $this->utilisateurService->getUtilisateurCourrant();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("afficherFormulaireConnexion");
        }
        return $this->afficherTwig("utilisateur/formulaireMiseAJourMdp.html.twig", [
            "utilisateur" => $utilisateur,
        ]);
    }

    #[Route(path: '/utilisateur/modification', name: 'mettreAJour', methods: ["POST"])]
    public  function mettreAJour(): Response
    {
        $nom = $_POST["nom"] ?? null;
        $prenom = $_POST["prenom"] ?? null;
        $email = $_POST["email"] ?? null;
        $mdp = $_POST["mdp"] ?? null;
        try {
            $this->utilisateurService->miseAJour($nom , $prenom , $email , $mdp);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("afficherFormulaireConnexion");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->redirection("afficherFormulaireMiseAJour");
        }
        MessageFlash::ajouter("success", "Vos informations ont bien été modifiées !");
        return $this->redirection("afficherDetail");
    }

    #[Route(path: '/utilisateur/modification-motdepasse', name: 'mettreAJourMdp', methods: ["POST"])]
    public  function mettreAJourMdp(): Response
    {
        $mdpAncien = $_POST["mdpAncien"];
        $mdp = $_POST["mdp"];
        $mdp2 = $_POST["mdp2"];
        try {
            $this->utilisateurService->miseAJourMdp($mdpAncien, $mdp, $mdp2);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("afficherFormulaireConnexion");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->redirection("afficherFormulaireMiseAJourMdp");
        }
        MessageFlash::ajouter("success", "Le mot de passe a bien été modifié !");
        return $this->redirection("afficherDetail");

    }

    #[Route(path: '/suppression-compte', name: 'supprimer', methods: ["GET"])]
    public  function supprimer(): Response
    {
        try {
            $this->utilisateurService->suppresion();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("afficherFormulaireConnexion");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->redirection("afficherDetail");
        }
        MessageFlash::ajouter("success", "Votre compte a bien été supprimé !");
        return $this->redirection("afficherFormulaireConnexion");

    }

    #[Route(path: '/connexion', name: 'afficherFormulaireConnexion', methods: ["GET"])]
    public  function afficherFormulaireConnexion(): Response
    {
        try {
            $this->utilisateurService->doitEtreDeconnecte();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("afficherListeMesTableaux");
        }
        return $this->afficherTwig(
            "utilisateur/formulaireConnexion.html.twig"
        );
    }

    #[Route(path: '/connexion', name: 'connecter', methods: ["POST"])]
    public  function connecter(): Response
    {
        $login = $_POST["login"] ?? null;
        $mdp = $_POST["mdp"] ?? null;
        try {
            $this->utilisateurService->connexion($login, $mdp);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("afficherListeMesTableaux");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->redirection("afficherFormulaireConnexion");
        }
        MessageFlash::ajouter("success", "Connexion effectuée.");
        return $this->redirection("afficherListeMesTableaux");
    }

    #[Route(path: '/deconnexion', name: 'deconnecter', methods: ["GET"])]
    public  function deconnecter(): Response
    {
        try {
            $this->utilisateurService->deconnexion();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("accueil");
        }
        MessageFlash::ajouter("success", "L'utilisateur a bien été déconnecté.");
        return $this->redirection("accueil");
    }

    #[Route(path: '/recuperation-compte', name: 'afficherFormulaireRecuperationCompte', methods: ["GET"])]
    public  function afficherFormulaireRecuperationCompte(): Response
    {
        try {
            $this->utilisateurService->doitEtreDeconnecte();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->redirection("afficherListeMesTableaux");
        }
        return $this->afficherTwig(
            "utilisateur/resetCompte.html.twig"
        );
    }

    #[Route(path: '/recuperation-compte', name: 'recupererCompte', methods: ["POST"])]
    public  function recupererCompte(): Response
    {
        $mail = $_POST["email"] ?? null;
        try {
            $this->utilisateurService->recupererCompte($mail);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->redirection("afficherFormulaireRecuperationCompte");
        }
        return $this->afficherTwig("utilisateur/resultatResetCompte.html.twig");
    }

    #[Route(path: '/recuperation-compte/validation', name: 'changementMotDePasseRecuperation', methods: ["POST"])]
    public  function changementMotDePasseRecuperation(): Response
    {
        $nonce = $_POST["nonce"] ?? null;
        $mdp = $_POST["mdp"] ?? null;
        $mdp2 = $_POST["mdp2"] ?? null;
        try {
            $this->utilisateurService->changementMotDePasseRecuperation($nonce, $mdp, $mdp2);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->afficherTwig("utilisateur/resultatResetCompte.html.twig");
        }
        MessageFlash::ajouter("success", "Mot de passe modifié, veuillez vous connecter");
        return self::redirection("afficherFormulaireConnexion");
    }
}