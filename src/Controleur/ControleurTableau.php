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

        $carteRepository = new CarteRepository();
        foreach ($colonnes as $colonne) {
            /**
             * @var Carte[] $cartes
             */
            $cartes = $carteRepository->recupererCartesColonne($colonne->getIdColonne());
//            foreach ($cartes as $carte) {
//                $affectations = (new AffecteRepository())->recupererParIdCarte($carte->getIdCarte());
//                foreach ($affectations as $affectation) {
//                    $utilisateur = (new UtilisateurRepository())->recupererParClePrimaire(array("login"=>$affectation->getLogin()));
//                    if(!isset($participants[$utilisateur->getLogin()])) {
//                        $participants[$utilisateur->getLogin()] = ["infos" => $utilisateur, "colonnes" => []];
//                    }
//                    if(!isset($participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()])) {
//                        $participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()] = [$colonne->getTitreColonne(), 0];
//                    }
//                    $participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()][1]++;
//                }
//            }
            $data[] = $cartes;
        }

        $participeRepository = new ParticipeRepository();
        $participants = $participeRepository->recupererParIdTableau($tableau->getIdTableau());

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
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        }
        $tableauRepository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$idTableau));
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            return ControleurTableau::redirection("accueil");
        }
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return ControleurTableau::redirection("afficherTableau", ["codetableau" => $tableau->getCodeTableau()]);
        }

        return ControleurTableau::afficherTwig("tableau/formulaireMiseAJourTableau.html.twig",[
            "idTableau" => $idTableau,
            "nomTableau" => $tableau->getTitreTableau()
        ]);
    }

    #[Route(path: '/tableaux/nouveau', name:'afficherFormulaireCreationTableau', methods:["GET"])]
    public static function afficherFormulaireCreationTableau(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        }
        return ControleurTableau::afficherTwig(
            "tableau/formulaireCreationTableau.html.twig"
        );
    }

    #[Route(path: '/tableaux/nouveau', name:'creerTableau', methods:["POST"])]
    public static function creerTableau(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        }

        if(!ControleurCarte::issetAndNotNull(["nomTableau"])) {
            MessageFlash::ajouter("danger", "Nom de tableau manquant");
            return ControleurTableau::redirection("afficherFormulaireCreationTableau");
        }

        $idUtilisateur = ConnexionUtilisateur::getLoginUtilisateurConnecte();

        $tableau = new Tableau(
            $idUtilisateur,
            null,
            null,
            $_REQUEST["nomTableau"]
        );

        $idTableau = (new TableauRepository())->ajouter($tableau);
        $codeTableau = hash("sha256", $idUtilisateur.$idTableau);
        $tableau->setIdTableau($idTableau);
        $tableau->setCodeTableau($codeTableau);
        (new TableauRepository())->mettreAJour($tableau);

        $colonne = new Colonne(
            $idTableau,
            null,
            "Colonne 1"
        );
        $idColonne = (new ColonneRepository())->ajouter($colonne);

        $carte = new Carte(
            $idColonne,
            null,
            "Carte 1",
            "Exemple de carte",
            "#FFFFFF"
        );

        $idCarte = (new CarteRepository())->ajouter($carte);

        if ($idTableau && $idColonne && $idCarte) {
            MessageFlash::ajouter("success", "Le tableau a bien été créé !");
        }
        else {
            MessageFlash::ajouter("warning", "Une erreur est survenue lors de la création du tableau.");
        }
        return ControleurTableau::redirection("afficherTableau", ["code" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/tableau/modification', name:'mettreAJourTableau', methods:["POST"])]
    public static function mettreAJourTableau(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant de tableau manquant");
            return ControleurTableau::redirection("accueil");
        }
        $tableauRepository = new TableauRepository();

        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTabeau"=>$_REQUEST["idTableau"]));
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            return ControleurTableau::redirection("accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["nomTableau"])) {
            MessageFlash::ajouter("danger", "Nom de tableau manquant");
            return ControleurTableau::redirection("afficherFormulaireMiseAJourTableau", ["idTableau" => $_REQUEST["idTableau"]]);
        }
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
        }
        else {
            $tableau->setTitreTableau($_REQUEST["nomTableau"]);
            $succesMiseAJour = $tableauRepository->mettreAJour($tableau);

            if ($succesMiseAJour) {
                MessageFlash::ajouter("success", "Le tableau a bien été modifié !");
            }
            else {
                MessageFlash::ajouter("warning", "Une erreur est survenue lors de la modification du tableau.");
            }
        }
        return ControleurTableau::redirection("afficherTableau", ["codetableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/tableau/{idTableau}/membres/ajout', name:'afficherFormulaireAjoutMembre', methods:["GET"])]
    public static function afficherFormulaireAjoutMembre($idTableau): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        }
        $tableauRepository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$idTableau));
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            return ControleurTableau::redirection("accueil");
        }
        if(!$tableauRepository->estProprietaire($tableau->getIdTableau(),ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
            return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $utilisateurRepository = new UtilisateurRepository();

        /**
         * @var Utilisateur[] $utilisateurs
         */
        $utilisateurs = $utilisateurRepository->recupererUtilisateursOrderedPrenomNom();
        $filtredUtilisateurs = array_filter($utilisateurs, function ($u) use ($tableau, $tableauRepository) {return !$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), $u->getLogin());});

        if(empty($filtredUtilisateurs)) {
            MessageFlash::ajouter("warning", "Il n'est pas possible d'ajouter plus de membre à ce tableau.");
            return ControleurTableau::redirection("afficherTableau", ["codetableau" => $tableau->getCodeTableau()]);
        }

        return ControleurTableau::afficherTwig("tableau/formulaireAjoutMembreTableau.html.twig", [
            "tableau" => $tableau,
            "utilisateurs" => $utilisateurs
        ]);
    }

    #[Route(path: '/tableau/membres/ajout', name:'ajouterMembre', methods:["POST"])]
    public static function ajouterMembre(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            return ControleurTableau::redirection("accueil");
        }
        $tableauRepository = new TableauRepository();

        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$_REQUEST["idTableau"]));
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            return ControleurTableau::redirection("accueil");
        }
        if(!$tableauRepository->estProprietaire($tableau->getIdTableau(),ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
            return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if(!ControleurCarte::issetAndNotNull(["login"])) {
            MessageFlash::ajouter("danger", "Login du membre à ajouter manquant");
            return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $utilisateurRepository = new UtilisateurRepository();
        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire(array("login"=>$_REQUEST["login"]));
        if(!$utilisateur) {
            MessageFlash::ajouter("danger", "Utlisateur inexistant");
            return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if($tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), $utilisateur->getLogin())) {
            MessageFlash::ajouter("warning", "Ce membre est déjà membre du tableau.");
            return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $participe = new Participe(
            $_REQUEST["idTableau"],
            $utilisateur->getLogin()
        );
        $succesSauvegarde = (new ParticipeRepository())->ajouter($participe);

        if ($succesSauvegarde) {
            MessageFlash::ajouter("success", "Le membre a bien été ajouté !");
        }
        else {
            MessageFlash::ajouter("warning", "Une erreur est survenue lors de l'ajout du membre'.");
        }
        return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/tableau/{idTableau}/membres/{login}/suppression', name:'supprimerMembre', methods:["GET"])]
    public static function supprimerMembre($login, $idTableau): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        }
        $tableauRepository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTabeau"=>$idTableau));
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            return ControleurTableau::redirection("accueil");
        }
        if(!$tableauRepository->estProprietaire($tableau->getIdTableau(),ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
            return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $utilisateurRepository = new UtilisateurRepository();
        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire(array("login"=>$login));
        if(!$utilisateur) {
            MessageFlash::ajouter("danger", "Utlisateur inexistant");
            return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if($tableauRepository->estProprietaire($tableau->getIdTableau(),$utilisateur->getLogin())) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas vous supprimer du tableau.");
            return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if(!$tableauRepository->estParticipant($tableau->getIdTableau(),$utilisateur->getLogin())) {
            MessageFlash::ajouter("danger", "Cet utilisateur n'est pas membre du tableau");
            return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $succesSauvegarde = (new ParticipeRepository())->supprimer(array($tableau->getIdTableau(), $utilisateur->getLogin()));

        $cartesRepository = new CarteRepository();
        $cartes = $cartesRepository->recupererCartesTableau($tableau->getIdTableau());
        foreach ($cartes as $carte) {
            $affectations = (new AffecteRepository())->recupererParIdCarte($carte->getIdCarte());
            foreach ($affectations as $affectation) {
                if ($affectation->getLogin() == $utilisateur->getLogin()) {
                    (new AffecteRepository())->supprimer(array($carte->getIdCarte(), $utilisateur->getLogin()));
                }
            }
        }

        if ($succesSauvegarde) {
            MessageFlash::ajouter("success", "Le membre a bien été supprimé !");
        }
        else {
            MessageFlash::ajouter("warning", "Une erreur est survenue lors de la suppression du membre'.");
        }
        return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
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
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurTableau::redirection("afficherFormulaireConnexion");
        }

        $tableauRepository = new TableauRepository();

        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$idTableau));
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            return ControleurTableau::redirection("afficherListeMesTableaux");
        }
        if(!$tableauRepository->estProprietaire($tableau->getIdTableau(),ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
            return ControleurTableau::redirection("afficherListeMesTableaux");
        }

        $succesSuppression =  $tableauRepository->supprimer(array("idtableau"=>$idTableau));

        if ($succesSuppression) {
            MessageFlash::ajouter("success", "Le tableau a bien été supprimé !");
        }
        else {
            MessageFlash::ajouter("warning", "Une erreur est survenue lors de la suppression du tableau.");
        }
        return ControleurTableau::redirection("afficherListeMesTableaux");
    }
}