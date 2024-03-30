<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurColonne extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $statusCode = "colonne"): Response
    {
        return parent::afficherErreur($messageErreur, $statusCode);
    }

    #[Route(path: '/colonne/{idColonne}/suppression', name:'supprimerColonne', methods:["GET"])]
    public static function supprimerColonne($idColonne): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurColonne::redirection("afficherFormulaireConnexion");
        }
        $colonneRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire(array("idcolonne"=>$idColonne));
        if(!$colonne) {
            MessageFlash::ajouter("danger", "Colonne inexistante");
            return ControleurColonne::redirection("accueil");
        }
        $tableauRepository = new TableauRepository();
        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$colonne->getIdTableau()));

        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return ControleurColonne::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $succesSuppression =  $colonneRepository->supprimer($idColonne);

        if ($succesSuppression) {
            MessageFlash::ajouter("success", "La colonne a bien été supprimée !");
        }
        else {
            MessageFlash::ajouter("warning", "Une erreur est survenue lors de la suppression de la colonne.");
        }
        return ControleurColonne::redirection("afficherTableau", ["codetableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/tableau/{idTableau}/colonne/nouvelle', name:'afficherFormulaireCreationColonne', methods:["GET"])]
    public static function afficherFormulaireCreationColonne($idTableau): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurColonne::redirection("afficherFormulaireConnexion");
        }
        $tableauRepository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$idTableau));
        if(!$tableau) {
            MessageFlash::ajouter("warning", "Tableau inexistant 9");
            return ControleurColonne::redirection("accueil");
        }
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return ControleurColonne::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Création d'une colonne",
            "cheminVueBody" => "colonne/formulaireCreationColonne.php",
            "idTableau" => $_REQUEST["idTableau"],
        ]);
    }

    #[Route(path: '/colonne/nouvelle', name:'creerColonne', methods:["POST"])]
    public static function creerColonne(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurColonne::redirection("afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            return ControleurColonne::redirection("accueil");
        }
        $tableauRepository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$_REQUEST["idTableau"]));
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant 10");
            return ControleurColonne::redirection("accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["nomColonne"])) {
            MessageFlash::ajouter("danger", "Nom de colonne manquant");
            return ControleurColonne::redirection("afficherFormulaireCreationColonne", ["idTableau" => $_REQUEST["idTableau"]]);
        }
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return ControleurColonne::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonneRepository = new ColonneRepository();
        $colonne = new Colonne(
            $tableau->getIdTableau(),
            $colonneRepository->getNextIdColonne(),
            $_REQUEST["nomColonne"]
        );

        $succesSauvegarde =  $colonneRepository->ajouter($colonne);

        if ($succesSauvegarde) {
            MessageFlash::ajouter("success", "La colonne a bien été créée !");
        }
        else {
            MessageFlash::ajouter("warning", "Une erreur est survenue lors de la création de la colonne.");
        }
        return ControleurColonne::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/colonne/{idColonne}/modification', name:'afficherFormulaireMiseAJourColonne', methods:["GET"])]
    public static function afficherFormulaireMiseAJourColonne($idColonne): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurColonne::redirection("afficherFormulaireConnexion");
        }
        $colonneRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire(array("idcolonne"=>$idColonne));
        if(!$colonne) {
            MessageFlash::ajouter("danger", "Colonne inexistante");
            return ControleurColonne::redirection("accueil");
        }
        $tableauRepository = new TableauRepository();
        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$colonne->getIdTableau()));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return ControleurColonne::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Modification d'une colonne",
            "cheminVueBody" => "colonne/formulaireMiseAJourColonne.php",
            "idColonne" => $_REQUEST["idColonne"],
            "nomColonne" => $colonne->getTitreColonne()
        ]);
    }

    #[Route(path: '/colonne/modification', name:'mettreAJourColonne', methods:["POST"])]
    public static function mettreAJourColonne(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return ControleurColonne::redirection("afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("danger", "Identifiant du colonne manquant");
            return ControleurColonne::redirection("accueil");
        }
        $colonneRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire(array("idColonne"=> $_REQUEST["idColonne"]));
        if(!$colonne) {
            MessageFlash::ajouter("danger", "Colonne inexistante");
            return ControleurColonne::redirection("accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["nomColonne"])) {
            MessageFlash::ajouter("danger", "Nom de colonne manquant");
            return ControleurColonne::redirection("afficherFormulaireMiseAJourColonne", ["idColonne" => $_REQUEST["idColonne"]]);
        }
        $tableauRepository = new TableauRepository();
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$colonne->getIdTableau()));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return ControleurColonne::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonne->setTitreColonne($_REQUEST["nomColonne"]);

        $succesMiseAJour = $colonneRepository->mettreAJour($colonne);

        if ($succesMiseAJour) {
            MessageFlash::ajouter("success", "Votre colonne a bien été modifiée !");
        }
        else {
            MessageFlash::ajouter("warning", "Une erreur est survenue lors de la modification de la colonne.");
        }
        return ControleurColonne::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }
}