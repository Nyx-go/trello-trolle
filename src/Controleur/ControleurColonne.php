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
    public function afficherErreur($messageErreur = "", $statusCode = "colonne"): Response
    {
        return $this->afficherErreur($messageErreur, $statusCode);
    }

    #[Route(path: '/colonne/{idColonne}/suppression', name:'supprimerColonne', methods:["GET"])]
    public function supprimerColonne($idColonne): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->redirection("afficherFormulaireConnexion");
        }
        $colonneRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire(array("idcolonne"=>$idColonne));
        if(!$colonne) {
            MessageFlash::ajouter("danger", "Colonne inexistante");
            return $this->redirection("accueil");
        }
        $tableauRepository = new TableauRepository();
        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$colonne->getIdTableau()));

        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $succesSuppression =  $colonneRepository->supprimer($idColonne);

        if ($succesSuppression) {
            MessageFlash::ajouter("success", "La colonne a bien été supprimée !");
        }
        else {
            MessageFlash::ajouter("warning", "Une erreur est survenue lors de la suppression de la colonne.");
        }
        return $this->redirection("afficherTableau", ["codetableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/tableau/{idTableau}/colonne/nouvelle', name:'afficherFormulaireCreationColonne', methods:["GET"])]
    public function afficherFormulaireCreationColonne($idTableau): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->redirection("afficherFormulaireConnexion");
        }
        $tableauRepository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$idTableau));
        if(!$tableau) {
            MessageFlash::ajouter("warning", "Tableau inexistant");
            return $this->redirection("accueil");
        }
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        return $this->afficherTwig("colonne/formulaireCreationColonne.html.twig",[
            "idTableau" => $idTableau
        ]);
    }

    #[Route(path: '/colonne/nouvelle', name:'creerColonne', methods:["POST"])]
    public function creerColonne(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->redirection("afficherFormulaireConnexion");
        }
        if(!$this->issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            return $this->redirection("accueil");
        }
        $tableauRepository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$_REQUEST["idTableau"]));
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            return $this->redirection("accueil");
        }
        if(!$this->issetAndNotNull(["nomColonne"])) {
            MessageFlash::ajouter("danger", "Nom de colonne manquant");
            return $this->redirection("afficherFormulaireCreationColonne", ["idTableau" => $_REQUEST["idTableau"]]);
        }
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
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
        return $this->redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/colonne/{idColonne}/modification', name:'afficherFormulaireMiseAJourColonne', methods:["GET"])]
    public function afficherFormulaireMiseAJourColonne($idColonne): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->redirection("afficherFormulaireConnexion");
        }
        $colonneRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire(array("idcolonne"=>$idColonne));
        if(!$colonne) {
            MessageFlash::ajouter("danger", "Colonne inexistante");
            return $this->redirection("accueil");
        }
        $tableauRepository = new TableauRepository();
        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$colonne->getIdTableau()));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        return $this->afficherTwig("colonne/formulaireMiseAJourColonne.html.twig",[
            "idColonne" => $idColonne,
            "nomColonne" => $colonne->getTitreColonne()
        ]);
    }

    #[Route(path: '/colonne/modification', name:'mettreAJourColonne', methods:["POST"])]
    public function mettreAJourColonne(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->redirection("afficherFormulaireConnexion");
        }
        if(!$this->issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("danger", "Identifiant du colonne manquant");
            return $this->redirection("accueil");
        }
        $colonneRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire(array("idColonne"=> $_REQUEST["idColonne"]));
        if(!$colonne) {
            MessageFlash::ajouter("danger", "Colonne inexistante");
            return $this->redirection("accueil");
        }
        if(!$this->issetAndNotNull(["nomColonne"])) {
            MessageFlash::ajouter("danger", "Nom de colonne manquant");
            return $this->redirection("afficherFormulaireMiseAJourColonne", ["idColonne" => $_REQUEST["idColonne"]]);
        }
        $tableauRepository = new TableauRepository();
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$colonne->getIdTableau()));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonne->setTitreColonne($_REQUEST["nomColonne"]);

        $succesMiseAJour = $colonneRepository->mettreAJour($colonne);

        if ($succesMiseAJour) {
            MessageFlash::ajouter("success", "Votre colonne a bien été modifiée !");
        }
        else {
            MessageFlash::ajouter("warning", "Une erreur est survenue lors de la modification de la colonne.");
        }
        return $this->redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }
}