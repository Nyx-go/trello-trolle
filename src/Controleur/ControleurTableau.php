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
        $tableauRepository = new TableauRepository();

        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParCodeTableau($codeTableau);
        if(!$tableau) {
            MessageFlash::ajouter("warning", "Tableau inexistant");
            return ControleurTableau::redirection("accueil");
        }
        $colonneRepository = new ColonneRepository();

        /**
         * @var Colonne[] $colonnes
         */
        $colonnes = $colonneRepository->recupererColonnesTableau($tableau->getIdTableau());
        $data = [];
        $participants = [];

        $carteRepository = new CarteRepository();
        foreach ($colonnes as $colonne) {
            /**
             * @var Carte[] $cartes
             */
            $cartes = $carteRepository->recupererCartesColonne($colonne->getIdColonne());
            foreach ($cartes as $carte) {
                $affectations = (new AffecteRepository())->recupererParIdCarte($carte->getIdCarte());
                foreach ($affectations as $affectation) {
                    $utilisateur = (new UtilisateurRepository())->recupererParClePrimaire(array("login"=>$affectation->getLogin()));
                    if(!isset($participants[$utilisateur->getLogin()])) {
                        $participants[$utilisateur->getLogin()] = ["infos" => $utilisateur, "colonnes" => []];
                    }
                    if(!isset($participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()])) {
                        $participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()] = [$colonne->getTitreColonne(), 0];
                    }
                    $participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()][1]++;
                }
            }
            $data[] = $cartes;
        }

        if(ConnexionUtilisateur::estConnecte()) {
            $estProprietaire = $tableauRepository->estProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte());
            $estParticipantOuProprietaire = $tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte());
        }
        else {
            $estProprietaire =false;
            $estParticipantOuProprietaire = false;
        }

        $utilisateur = (new UtilisateurRepository())->recupererParClePrimaire(array("login"=>$tableau->getLogin()));

        return ControleurTableau::afficherTwig("tableau/tableau.html.twig",[
            "estProprietaire"=> $estProprietaire,
            "estParticipantOuProprietaire" => $estParticipantOuProprietaire,
            "tableau" => $tableau,
            "colonnes" => $colonnes,
            "participants" => $participants,
            "data" => $data,
            "utilisateur"=>$utilisateur
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
            "tableau" => $tableau,
            "utilisateurs" => $utilisateurs
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
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        }
        $tableauRepository = new TableauRepository();
        $login = ConnexionUtilisateur::getLoginUtilisateurConnecte();
        $tableaux = $tableauRepository->recupererTableauxOuUtilisateurEstMembre($login);
        $estProprietaire = [];
        foreach ($tableaux as $tableau){
            $estProprietaire[$tableau->getIdTableau()] = $tableauRepository->estProprietaire($tableau->getIdTableau(), $login);
        }
        return ControleurTableau::afficherTwig("tableau/listeTableauxUtilisateur.html.twig", [
            "tableaux" => $tableaux,
            "estProprietaire"=>$estProprietaire,
            "login"=>$login
        ]);
    }

    #[Route(path: '/tableau/{idTableau}/quitter', name:'quitterTableau', methods:["GET"])]
    public static function quitterTableau($idTableau): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        }
        $tableauRepository = new TableauRepository();
        $utilisateurRepository = new UtilisateurRepository();
        $participeRepository = new ParticipeRepository();
        $affecteRepository = new AffecteRepository();
        $carteRepository = new CarteRepository();

        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$idTableau));
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            return ControleurTableau::redirection("afficherListeMesTableaux");
        }


        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire(array("login"=>ConnexionUtilisateur::getLoginUtilisateurConnecte()));
        if($tableauRepository->estProprietaire($tableau->getIdTableau(),$utilisateur->getLogin())) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas quitter ce tableau");
            return ControleurTableau::redirection("afficherListeMesTableaux");
        }
        if(!$tableauRepository->estParticipant($tableau->getIdTableau(),ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'appartenez pas à ce tableau");
            return ControleurTableau::redirection("afficherListeMesTableaux");
        }

        $participeRepository->supprimer(array($tableau->getIdTableau(), $utilisateur->getLogin()));

        /**
         * @var Carte[] $cartes
         */
        $cartes = $carteRepository->recupererCartesTableau($tableau->getIdTableau());
        foreach ($cartes as $carte) {
            $affecteRepository->supprimer(array($carte->getIdCarte(), $utilisateur->getLogin()));
            $succesSuppression =  $tableauRepository->supprimer($idTableau);

            if ($succesSuppression) {
                MessageFlash::ajouter("success", "Vous avez bien quitté le tableau !");
            }
            else {
                MessageFlash::ajouter("warning", "Une erreur est survenue lorsque vous avez essayé de quitter le tableau.");
            }
        }
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