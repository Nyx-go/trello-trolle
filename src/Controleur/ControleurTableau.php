<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Participe;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\AffecteRepository;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\ParticipeRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Service\Exception\ServiceConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\TableauService;
use App\Trellotrolle\Service\UtilisateurService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurTableau extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $statusCode = "tableau"): Response
    {
        return parent::afficherErreur($messageErreur, $statusCode);
    }

    #[Route(path: '/tableau/{codeTableau}', name:'afficherTableau', methods:["GET"])]
    public static function afficherTableau($codeTableau) : Response {
        try {
            $value = (new TableauService())->afficherTableau($codeTableau);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("accueil");
        }

        return ControleurTableau::afficherTwig("tableau/tableau.html.twig",[
            "estProprietaire"=> $value["estProprietaire"],
            "estParticipantOuProprietaire" => $value["estParticipantOuProprietaire"],
            "tableau" => $value["tableau"],
            "colonnes" => $value["colonnes"],
            "affectations" => $value["affectations"],
            "participants" => $value["participants"],
            "data" => $value["data"],
            "utilisateur"=>$value["utilisateur"],
            "affectationsCartes" => $value["affectationsCartes"]
        ]);
    }

    #[Route(path: '/tableau/{idTableau}/modification', name:'afficherFormulaireMiseAJourTableau', methods:["GET"])]
    public static function afficherFormulaireMiseAJourTableau($idTableau): Response {
        try {
            $titreTableau = (new TableauService())->afficherFormulaireMiseAJourTableau($idTableau);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurGenerique::redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("afficherListeMesTableaux");
        }

        return ControleurTableau::afficherTwig("tableau/formulaireMiseAJourTableau.html.twig",[
            "idTableau" => $idTableau,
            "nomTableau" => $titreTableau
        ]);
    }

    #[Route(path: '/tableaux/nouveau', name:'afficherFormulaireCreationTableau', methods:["GET"])]
    public static function afficherFormulaireCreationTableau(): Response {
        try {
            (new UtilisateurService())->doitEtreConnecte();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        }
        return ControleurTableau::afficherTwig(
            "tableau/formulaireCreationTableau.html.twig"
        );
    }

    #[Route(path: '/tableaux/nouveau', name:'creerTableau', methods:["POST"])]
    public static function creerTableau(): Response {
        $nomTableau = $_REQUEST["nomTableau"] ?? null;
        try {
            $codeTableau = (new TableauService())->creerDepuisFormulaire($nomTableau);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurGenerique::redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return ControleurUtilisateur::afficherTwig("tableau/afficherFormulaireCreationTableau.html.twig");
        }
        MessageFlash::ajouter("success", "Le tableau a bien été créé !");
        return self::redirection("afficherTableau", ["codeTableau" => $codeTableau]);
    }

    #[Route(path: '/tableau/modification', name:'mettreAJourTableau', methods:["POST"])]
    public static function mettreAJourTableau(): Response {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $nomTableau = $_REQUEST["nomTableau"] ?? null;

        try {
            $codeTableau = (new TableauService())->mettreAJourTableau($idTableau,$nomTableau);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurGenerique::redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("afficherListeMesTableaux");
        }

        MessageFlash::ajouter("success", "Le tableau a bien été modifié !");
        return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $codeTableau]);

    }

    #[Route(path: '/tableau/{idTableau}/membres/ajout', name:'afficherFormulaireAjoutMembre', methods:["GET"])]
    public static function afficherFormulaireAjoutMembre($idTableau): Response {
        try {
            $value = (new TableauService())->afficherFormulaireAjoutMembre($idTableau);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurGenerique::redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("afficherListeMesTableaux");
        }

        return ControleurTableau::afficherTwig("tableau/formulaireAjoutMembreTableau.html.twig", [
            "tableau" => $value["tableau"],
            "utilisateurs" => $value["filtredUtilisateurs"]
        ]);
    }

    #[Route(path: '/tableau/membres/ajout', name:'ajouterMembre', methods:["POST"])]
    public static function ajouterMembre(): Response {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $login = $_REQUEST["login"] ?? null;
        try {
            $codeTableau = (new TableauService())->ajouterMembre($idTableau,$login);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurGenerique::redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return ControleurUtilisateur::afficherTwig("tableau/afficherFormulaireCreationTableau.html.twig");
        }
        MessageFlash::ajouter("success", "Le membre a bien été ajouté !");
        return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $codeTableau]);
    }

    #[Route(path: '/tableau/{idTableau}/membres/{login}/suppression', name:'supprimerMembre', methods:["GET"])]
    public static function supprimerMembre($login, $idTableau): Response {
        try {
            $codeTableau = (new TableauService())->supprimerMembre($idTableau,$login);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurGenerique::redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return ControleurUtilisateur::afficherTwig("tableau/afficherFormulaireCreationTableau.html.twig");
        }
        MessageFlash::ajouter("success", "Le membre a bien été supprimé !");
        return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $codeTableau]);
    }

    #[Route(path: '/tableaux', name:'afficherListeMesTableaux', methods:["GET"])]
    public static function afficherListeMesTableaux() : Response {
        try {
            $value = (new TableauService())->afficherListeMesTableaux();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurGenerique::redirection("accueil");
        }
        return ControleurTableau::afficherTwig("tableau/listeTableauxUtilisateur.html.twig", [
            "tableaux" => $value["tableaux"],
            "estProprietaire"=> $value["estProprietaire"],
            "login"=> $value["login"]
        ]);
    }

    #[Route(path: '/tableau/{idTableau}/quitter', name:'quitterTableau', methods:["GET"])]
    public static function quitterTableau($idTableau): Response {
        try {
            (new TableauService())->quitterTableau($idTableau);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurGenerique::redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("afficherListeMesTableaux");
        }
        MessageFlash::ajouter("success", "Vous avez bien quitté le tableau !");
        return ControleurTableau::redirection("afficherListeMesTableaux");
    }

    #[Route(path: '/tableau/{idTableau}/suppression', name:'supprimerTableau', methods:["GET"])]
    public static function supprimerTableau($idTableau): Response {
        try {
            (new TableauService())->supprimerTableau($idTableau);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurGenerique::redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("afficherListeMesTableaux");
        }

        MessageFlash::ajouter("success", "Le tableau a bien été supprimé !");
        return ControleurTableau::redirection("afficherListeMesTableaux");
    }
}