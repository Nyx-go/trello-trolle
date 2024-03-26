<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Carte;
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

    #[Route(path: '/colonne/suppression', name:'supprimerColonne', methods:["GET"])]
    public static function supprimerColonne(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurColonne::redirection("afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("danger", "Code de colonne manquant");
            ControleurColonne::redirection("accueil");
        }
        $colonneRepository = new ColonneRepository();
        $idColonne = $_REQUEST["idColonne"];
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire(array("idColonne"=>$idColonne));
        if(!$colonne) {
            MessageFlash::ajouter("danger", "Colonne inexistante");
            ControleurColonne::redirection("accueil");
        }
        $tableauRepository = new TableauRepository();
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$colonne->getIdTableau()));

        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurColonne::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $carteRepository = new CarteRepository();

        if($carteRepository->getNombreCartesTotalUtilisateur($tableau->getLogin()) == 1) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas supprimer cette colonne car cela entrainera la supression du compte du propriétaire du tableau");
            ControleurColonne::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $colonneRepository->supprimer($idColonne);
        $colonneRepository = new ColonneRepository();
        if($colonneRepository->getNombreColonnesTotalTableau($tableau->getIdTableau()) > 0) {
            ControleurColonne::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        ControleurCarte::redirection("afficherListeMesTableaux");
    }

    #[Route(path: '/colonne/nouvelle', name:'afficherFormulaireCreationColonne', methods:["GET"])]
    public static function afficherFormulaireCreationColonne(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurColonne::redirection("afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("warning", "Identifiant du tableau manquant");
            ControleurColonne::redirection("accueil");
        }
        $tableauRepository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$_REQUEST["idTableau"]));
        if(!$tableau) {
            MessageFlash::ajouter("warning", "Tableau inexistant");
            ControleurColonne::redirection("accueil");
        }
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurColonne::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Création d'une colonne",
            "cheminVueBody" => "colonne/formulaireCreationColonne.php",
            "idTableau" => $_REQUEST["idTableau"],
        ]);
    }

    #[Route(path: '/colonne/nouvelle', name:'creerColonne', methods:["POST"])]
    public static function creerColonne(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurColonne::redirection("afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            ControleurColonne::redirection("accueil");
        }
        $tableauRepository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$_REQUEST["idTableau"]));
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            ControleurColonne::redirection("accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["nomColonne"])) {
            MessageFlash::ajouter("danger", "Nom de colonne manquant");
            ControleurColonne::redirection("afficherFormulaireCreationColonne", ["idTableau" => $_REQUEST["idTableau"]]);
        }
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurColonne::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonneRepository = new ColonneRepository();
        $colonne = new Colonne(
            $tableau->getIdTableau(),
            $colonneRepository->getNextIdColonne(),
            $_REQUEST["nomColonne"]
        );
        $colonneRepository->ajouter($colonne);
        ControleurColonne::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/colonne/mise-a-jour', name:'afficherFormulaireMiseAJourColonne', methods:["GET"])]
    public static function afficherFormulaireMiseAJourColonne(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurColonne::redirection("afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("danger", "Identifiant du colonne manquant");
            ControleurColonne::redirection("accueil");
        }
        $colonneRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire(array("idColonne"=>$_REQUEST["idColonne"]));
        if(!$colonne) {
            MessageFlash::ajouter("danger", "Colonne inexistante");
            ControleurColonne::redirection("accueil");
        }
        $tableauRepository = new TableauRepository();
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$colonne->getIdTableau()));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurColonne::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Modification d'une colonne",
            "cheminVueBody" => "colonne/formulaireMiseAJourColonne.php",
            "idColonne" => $_REQUEST["idColonne"],
            "nomColonne" => $colonne->getTitreColonne()
        ]);
    }

    #[Route(path: '/colonne/mise-a-jour', name:'mettreAJourColonne', methods:["POST"])]
    public static function mettreAJourColonne(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurColonne::redirection("afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("danger", "Identifiant du colonne manquant");
            ControleurColonne::redirection("accueil");
        }
        $colonneRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire(array("idColonne"=> $_REQUEST["idColonne"]));
        if(!$colonne) {
            MessageFlash::ajouter("danger", "Colonne inexistante");
            ControleurColonne::redirection("accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["nomColonne"])) {
            MessageFlash::ajouter("danger", "Nom de colonne manquant");
            ControleurColonne::redirection("afficherFormulaireMiseAJourColonne", ["idColonne" => $_REQUEST["idColonne"]]);
        }
        $tableauRepository = new TableauRepository();
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$colonne->getIdTableau()));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurColonne::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonne->setTitreColonne($_REQUEST["nomColonne"]);
        $colonneRepository->mettreAJour($colonne);
        ControleurColonne::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }
}