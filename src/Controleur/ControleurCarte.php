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
use Symfony\Component\Routing\Attribute\Route;

class ControleurCarte extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $controleur = ""): void
    {
        parent::afficherErreur($messageErreur, "carte");
    }

    #[Route(path: '/carte/suppression', name:'supprimerCarte', methods:["GET"])]
    public static function supprimerCarte(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurCarte::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idCarte"])) {
            MessageFlash::ajouter("warning", "Code de carte manquant");
            ControleurCarte::redirection("base", "accueil");
        }
        $carteRepository = new CarteRepository();
        $tableauRepository = new TableauRepository();
        $idCarte = $_REQUEST["idCarte"];
        /**
         * @var Carte $carte
         */
        $carte = $carteRepository->recupererParClePrimaire(array("idCarte"=> $idCarte));
        if(!$carte) {
            MessageFlash::ajouter("danger", "Carte inexistante");
            ControleurCarte::redirection("base", "accueil");
        }
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>($carteRepository->getTableauByIdCarte($idCarte))));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $carteRepository->supprimer($idCarte);
        $cartes = $carteRepository->recupererCartesTableau($tableau->getIdTableau());
        if(count($cartes) > 0) {
            ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        else {
            ControleurCarte::redirection("tableau", "afficherListeMesTableaux");
        }
    }

    #[Route(path: '/carte/nouvelle', name:'afficherFormulaireCreationCarte', methods:["GET"])]
    public static function afficherFormulaireCreationCarte(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurCarte::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("warning", "Identifiant de colonne manquant");
            ControleurCarte::redirection("base", "accueil");
        }
        $colonneRepository = new ColonneRepository();
        $tableauRepository = new TableauRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire(array( "idColonne"=> $_REQUEST["idColonne"]));
        if(!$colonne) {
            MessageFlash::ajouter("warning", "Colonne inexistante");
            ControleurCarte::redirection("base", "accueil");
        }
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$colonne->getIdTableau()));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonnes = $colonneRepository->recupererColonnesTableau($tableau->getIdTableau());
        ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Création d'une carte",
            "cheminVueBody" => "carte/formulaireCreationCarte.php",
            "colonne" => $colonne,
            "colonnes" => $colonnes
        ]);
    }
    //TODO : changer l'appelle de recupérer par clé primaire

    //TODO : changer l'appelle de recupérer par clé primaire
    #[Route(path: '/carte/nouvelle', name:'creerCarte', methods:["POST"])]
    public static function creerCarte(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurCarte::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("warning", "Identifiant de colonne manquant");
            ControleurCarte::redirection("base", "accueil");
        }
        $colonneRepository = new ColonneRepository();
        $tableauRepository = new TableauRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire(array("idColonne"=> $_REQUEST["idColonne"]));
        if(!$colonne) {
            MessageFlash::ajouter("warning", "Colonne inexistante");
            ControleurCarte::redirection("base", "accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["titreCarte", "descriptifCarte", "couleurCarte"])) {
            MessageFlash::ajouter("danger", "Attributs manquants");
            ControleurColonne::redirection("carte", "afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
        }
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$colonne->getIdTableau()));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
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
                    ControleurCarte::redirection("carte", "afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
                }
                if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(),$utilisateur->getLogin())) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'est pas affecté au tableau.");
                    ControleurCarte::redirection("carte", "afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
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
        $carteRepository->ajouter($carte);
        ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/carte/mise-a-jour', name:'afficherFormulaireMiseAJourCarte', methods:["GET"])]
    public static function afficherFormulaireMiseAJourCarte(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurCarte::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idCarte"])) {
            MessageFlash::ajouter("warning", "Identifiant de la carte manquant");
            ControleurCarte::redirection("base", "accueil");
        }
        $carteRepository = new CarteRepository();
        $tableauRepository = new TableauRepository();
        /**
         * @var Carte $carte
         */
        $carte = $carteRepository->recupererParClePrimaire(array("idCarte" =>$_REQUEST["idCarte"]));
        if(!$carte) {
            MessageFlash::ajouter("warning", "Carte inexistante");
            ControleurCarte::redirection("base", "accueil");
        }
        $colonne = (new ColonneRepository())->recupererParClePrimaire(array("idColonne"=>$carte->getIdColonne()));
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$colonne->getIdTableau()));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonneRepository = new ColonneRepository();
        $colonnes = $colonneRepository->recupererColonnesTableau($tableau->getIdTableau());
        ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Modification d'une carte",
            "cheminVueBody" => "carte/formulaireMiseAJourCarte.php",
            "carte" => $carte,
            "colonnes" => $colonnes
        ]);
    }

    #[Route(path: '/carte/mise-a-jour', name:'mettreAJourCarte', methods:["POST"])]
    //TODO : changer l'appelle à récupérer par clé primaire


    public static function mettreAJourCarte(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurCarte::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idCarte"])) {
            MessageFlash::ajouter("warning", "Identifiant de la carte manquant");
            ControleurCarte::redirection("base", "accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("warning", "Identifiant de colonne manquant");
            ControleurCarte::redirection("base", "accueil");
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
            ControleurCarte::redirection("base", "accueil");
        }
        if(!$colonne) {
            MessageFlash::ajouter("warning", "Colonne inexistante");
            ControleurCarte::redirection("base", "accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["titreCarte", "descriptifCarte", "couleurCarte"])) {
            MessageFlash::ajouter("danger", "Attributs manquants");
            ControleurColonne::redirection("carte", "afficherFormulaireMiseAJourCarte", ["idCarte" => $_REQUEST["idCarte"]]);
        }

        $originalColonne =(new ColonneRepository())->recupererParClePrimaire(array("idColonne"=>$carte->getIdColonne()));
        if($originalColonne->getIdTableau() !== $colonne->getIdTableau()) {
            MessageFlash::ajouter("danger", "Le tableau de cette colonne n'est pas le même que celui de la colonne d'origine de la carte!");
            ControleurColonne::redirection("carte", "afficherFormulaireMiseAJourCarte", ["idCarte" => $_REQUEST["idCarte"]]);
        }
        $tableauRepository = new TableauRepository();
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$colonne->getIdTableau()));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
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
                    ControleurCarte::redirection("carte", "afficherFormulaireMiseAJourCarte", ["idCarte" => $_REQUEST["idCarte"]]);
                }
                if(!$tableauRepository->estParticipantOuProprietaire( $tableau->getIdTableau(),$utilisateur->getLogin())) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'est pas affecté au tableau.");
                    ControleurCarte::redirection("carte", "afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
                }
            }
        }
        $carteRepository->mettreAJour($carte);
        ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }
}