<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurCarte extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $statusCode = "carte"): Response
    {
        return parent::afficherErreur($messageErreur, $statusCode);
    }

    #[Route(path: '/carte/{idCarte}/suppression', name:'supprimerCarte', methods:["GET"])]
    public static function supprimerCarte($idCarte): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurCarte::redirection("afficherFormulaireConnexion");
        }
        $carteRepository = new CarteRepository();
        $tableauRepository = new TableauRepository();
        /**
         * @var Carte $carte
         */
        $carte = $carteRepository->recupererParClePrimaire(array("idcarte"=> $idCarte));
        if(!$carte) {
            MessageFlash::ajouter("danger", "Carte inexistante");
            return ControleurCarte::redirection("accueil");
        }
        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>($carteRepository->getTableauByIdCarte($idCarte))));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return ControleurCarte::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $succesSuppression =  $carteRepository->supprimer($idCarte);

        if ($succesSuppression) {
            MessageFlash::ajouter("success", "La carte a bien été supprimée !");
        }
        else {
            MessageFlash::ajouter("warning", "Une erreur est survenue lors de la suppression de la carte.");
        }
        return ControleurCarte::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/colonne/{idColonne}/carte/nouvelle', name:'afficherFormulaireCreationCarte', methods:["GET"])]
    public static function afficherFormulaireCreationCarte($idColonne): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurCarte::redirection("afficherFormulaireConnexion");
        }
        $colonneRepository = new ColonneRepository();
        $tableauRepository = new TableauRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire(array( "idcolonne"=> $idColonne));
        if(!$colonne) {
            MessageFlash::ajouter("warning", "Colonne inexistante");
            return ControleurCarte::redirection("accueil");
        }
        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$colonne->getIdTableau()));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return ControleurCarte::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonnes = $colonneRepository->recupererColonnesTableau($tableau->getIdTableau());
        return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Création d'une carte",
            "cheminVueBody" => "carte/formulaireCreationCarte.php",
            "colonne" => $colonne,
            "colonnes" => $colonnes
        ]);
    }

    #[Route(path: '/carte/nouvelle', name:'creerCarte', methods:["POST"])]
    public static function creerCarte(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurCarte::redirection("afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("warning", "Identifiant de colonne manquant");
            return ControleurCarte::redirection("accueil");
        }
        $colonneRepository = new ColonneRepository();
        $tableauRepository = new TableauRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire(array("idColonne"=> $_REQUEST["idColonne"]));
        if(!$colonne) {
            MessageFlash::ajouter("warning", "Colonne inexistante");
            return ControleurCarte::redirection("accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["titreCarte", "descriptifCarte", "couleurCarte"])) {
            MessageFlash::ajouter("danger", "Attributs manquants");
            return ControleurColonne::redirection("afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
        }
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$colonne->getIdTableau()));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return ControleurCarte::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $utilisateurRepository = new UtilisateurRepository();
        if(ControleurCarte::issetAndNotNull(["affectationsCarte"])) {
            foreach ($_REQUEST["affectationsCarte"] as $affectation) {
                /**
                 * @var Utilisateur $utilisateur
                 */
                $utilisateur = $utilisateurRepository->recupererParClePrimaire(array("login"=> $affectation));
                if(!$utilisateur) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'existe pas");
                    return ControleurCarte::redirection("afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
                }
                if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(),$utilisateur->getLogin())) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'est pas affecté au tableau.");
                    return ControleurCarte::redirection("afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
                }
            }
        }
        $carteRepository = new CarteRepository();
        $carte = new Carte(
            $colonne->getIdColonne(),
            $carteRepository->getNextIdCarte(),
            $_REQUEST["titreCarte"],
            $_REQUEST["descriptifCarte"],
            $_REQUEST["couleurCarte"],
        );

        $succesSauvegarde = $carteRepository->ajouter($carte);

        if ($succesSauvegarde) {
            MessageFlash::ajouter("success", "La carte a bien été créée !");
        }
        else {
            MessageFlash::ajouter("warning", "Une erreur est survenue lors de la création de la carte.");
        }
        return ControleurCarte::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/carte/{idCarte}/modification', name:'afficherFormulaireMiseAJourCarte', methods:["GET"])]
    public static function afficherFormulaireMiseAJourCarte($idCarte): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurCarte::redirection("afficherFormulaireConnexion");
        }
        $carteRepository = new CarteRepository();
        $tableauRepository = new TableauRepository();
        /**
         * @var Carte $carte
         */
        $carte = $carteRepository->recupererParClePrimaire(array("idcarte" =>$idCarte));
        if(!$carte) {
            MessageFlash::ajouter("warning", "Carte inexistante");
            return ControleurCarte::redirection("accueil");
        }
        $colonne = (new ColonneRepository())->recupererParClePrimaire(array("idcolonne"=>$carte->getIdColonne()));
        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$colonne->getIdTableau()));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return ControleurCarte::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonneRepository = new ColonneRepository();
        $colonnes = $colonneRepository->recupererColonnesTableau($tableau->getIdTableau());
        return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Modification d'une carte",
            "cheminVueBody" => "carte/formulaireMiseAJourCarte.php",
            "carte" => $carte,
            "colonnes" => $colonnes
        ]);
    }

    #[Route(path: '/carte/modification', name:'mettreAJourCarte', methods:["POST"])]
    public static function mettreAJourCarte(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurCarte::redirection("afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idCarte"])) {
            MessageFlash::ajouter("warning", "Identifiant de la carte manquant");
            return ControleurCarte::redirection("accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("warning", "Identifiant de colonne manquant");
            return ControleurCarte::redirection("accueil");
        }
        $carteRepository = new CarteRepository();
        /**
         * @var Carte $carte
         */
        $carte = $carteRepository->recupererParClePrimaire(array("idCarte"=>$_REQUEST["idCarte"]));

        $colonnesRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonnesRepository->recupererParClePrimaire( array("idColonne"=> $_REQUEST["idColonne"]));
        if(!$carte) {
            MessageFlash::ajouter("warning", "Carte inexistante");
            return ControleurCarte::redirection("accueil");
        }
        if(!$colonne) {
            MessageFlash::ajouter("warning", "Colonne inexistante");
            return ControleurCarte::redirection("accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["titreCarte", "descriptifCarte", "couleurCarte"])) {
            MessageFlash::ajouter("danger", "Attributs manquants");
            return ControleurColonne::redirection("afficherFormulaireMiseAJourCarte", ["idCarte" => $_REQUEST["idCarte"]]);
        }

        $originalColonne =(new ColonneRepository())->recupererParClePrimaire(array("idColonne"=>$carte->getIdColonne()));
        if($originalColonne->getIdTableau() !== $colonne->getIdTableau()) {
            MessageFlash::ajouter("danger", "Le tableau de cette colonne n'est pas le même que celui de la colonne d'origine de la carte!");
            return ControleurColonne::redirection("afficherFormulaireMiseAJourCarte", ["idCarte" => $_REQUEST["idCarte"]]);
        }
        $tableauRepository = new TableauRepository();
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$colonne->getIdTableau()));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return ControleurCarte::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $carte->setIdColonne($colonne->getIdColonne());
        $carte->setTitreCarte($_REQUEST["titreCarte"]);
        $carte->setDescriptifCarte($_REQUEST["descriptifCarte"]);
        $carte->setCouleurCarte($_REQUEST["couleurCarte"]);
        $utilisateurRepository = new UtilisateurRepository();
        if(ControleurCarte::issetAndNotNull(["affectationsCarte"])) {
            foreach ($_REQUEST["affectationsCarte"] as $affectation) {
                /**
                 * @var Utilisateur $utilisateur
                 */
                $utilisateur = $utilisateurRepository->recupererParClePrimaire(array(""=>$affectation));
                if(!$utilisateur) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'existe pas");
                    return ControleurCarte::redirection("afficherFormulaireMiseAJourCarte", ["idCarte" => $_REQUEST["idCarte"]]);
                }
                if(!$tableauRepository->estParticipantOuProprietaire( $tableau->getIdTableau(),$utilisateur->getLogin())) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'est pas affecté au tableau.");
                    return ControleurCarte::redirection("afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
                }
            }
        }

        $succesMiseAJour = $carteRepository->mettreAJour($carte);

        if ($succesMiseAJour) {
            MessageFlash::ajouter("success", "La carte a bien été modifiée !");
        }
        else {
            MessageFlash::ajouter("warning", "Une erreur est survenue lors de la modification de la carte.");
        }
        return ControleurCarte::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }
}