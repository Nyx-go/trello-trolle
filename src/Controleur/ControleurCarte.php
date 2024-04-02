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
    public function afficherErreur($messageErreur = "", $statusCode = "carte"): Response
    {
        return $this->afficherErreur($messageErreur, $statusCode);
    }

    #[Route(path: '/carte/{idCarte}/suppression', name:'NONAPIsupprimerCarte', methods:["GET"])]
    public  function supprimerCarte($idCarte): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->redirection("afficherFormulaireConnexion");
        }
        $carteRepository = new CarteRepository();
        $tableauRepository = new TableauRepository();
        /**
         * @var Carte $carte
         */
        $carte = $carteRepository->recupererParClePrimaire(array("idcarte"=> $idCarte));
        if(!$carte) {
            MessageFlash::ajouter("danger", "Carte inexistante");
            return $this->redirection("accueil");
        }
        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>($carteRepository->getTableauByIdCarte($idCarte))));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $succesSuppression =  $carteRepository->supprimer($idCarte);

        if ($succesSuppression) {
            MessageFlash::ajouter("success", "La carte a bien été supprimée !");
        }
        else {
            MessageFlash::ajouter("warning", "Une erreur est survenue lors de la suppression de la carte.");
        }
        return $this->redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/colonne/{idColonne}/carte/nouvelle', name:'NONAPIafficherFormulaireCreationCarte', methods:["GET"])]
    public  function afficherFormulaireCreationCarte($idColonne): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->redirection("afficherFormulaireConnexion");
        }
        $colonneRepository = new ColonneRepository();
        $tableauRepository = new TableauRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire(array( "idcolonne"=> $idColonne));
        if(!$colonne) {
            MessageFlash::ajouter("warning", "Colonne inexistante");
            return $this->redirection("accueil");
        }
        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$colonne->getIdTableau()));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonnes = $colonneRepository->recupererColonnesTableau($tableau->getIdTableau());
        return $this->afficherTwig("carte/formulaireCreationCarte.html.twig",[
            "colonne" => $colonne,
            "colonnes" => $colonnes
        ]);
    }

    #[Route(path: '/carte/nouvelle', name:'NONAPIcreerCarte', methods:["POST"])]
    public  function creerCarte(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->redirection("afficherFormulaireConnexion");
        }
        if(!$this->issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("warning", "Identifiant de colonne manquant");
            return $this->redirection("accueil");
        }
        $colonneRepository = new ColonneRepository();
        $tableauRepository = new TableauRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire(array("idColonne"=> $_REQUEST["idColonne"]));
        if(!$colonne) {
            MessageFlash::ajouter("warning", "Colonne inexistante");
            return $this->redirection("accueil");
        }
        if(!$this->issetAndNotNull(["titreCarte", "descriptifCarte", "couleurCarte"])) {
            MessageFlash::ajouter("danger", "Attributs manquants");
            return $this->redirection("afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
        }
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$colonne->getIdTableau()));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $utilisateurRepository = new UtilisateurRepository();
        if($this->issetAndNotNull(["affectationsCarte"])) {
            foreach ($_REQUEST["affectationsCarte"] as $affectation) {
                /**
                 * @var Utilisateur $utilisateur
                 */
                $utilisateur = $utilisateurRepository->recupererParClePrimaire(array("login"=> $affectation));
                if(!$utilisateur) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'existe pas");
                    return $this->redirection("afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
                }
                if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(),$utilisateur->getLogin())) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'est pas affecté au tableau.");
                    return $this->redirection("afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
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
        return $this->redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/carte/{idCarte}/modification', name:'NONAPIafficherFormulaireMiseAJourCarte', methods:["GET"])]
     public  function afficherFormulaireMiseAJourCarte($idCarte): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->redirection("afficherFormulaireConnexion");
        }
        $carteRepository = new CarteRepository();
        $tableauRepository = new TableauRepository();
        /**
         * @var Carte $carte
         */
        $carte = $carteRepository->recupererParClePrimaire(array("idcarte" =>$idCarte));
        if(!$carte) {
            MessageFlash::ajouter("warning", "Carte inexistante");
            return $this->redirection("accueil");
        }
        $colonne = (new ColonneRepository())->recupererParClePrimaire(array("idcolonne"=>$carte->getIdColonne()));
        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$colonne->getIdTableau()));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonneRepository = new ColonneRepository();
        $colonnes = $colonneRepository->recupererColonnesTableau($tableau->getIdTableau());

        $proprietaire = (new UtilisateurRepository())->recupererParClePrimaire(["login" => $tableau->getLogin()]);
        return $this->afficherTwig("carte/formulaireMiseAJourCarte.html.twig",[
            "carte" => $carte,
            "colonnes" => $colonnes,
            "proprietaire" => $proprietaire,
            "colonneCarte" => $colonne
        ]);
    }

    #[Route(path: '/carte/modification', name:'NONAPImettreAJourCarte', methods:["POST"])]
    public  function mettreAJourCarte(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            return $this->redirection("afficherFormulaireConnexion");
        }
        if(!$this->issetAndNotNull(["idCarte"])) {
            MessageFlash::ajouter("warning", "Identifiant de la carte manquant");
            return $this->redirection("accueil");
        }
        if(!$this->issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("warning", "Identifiant de colonne manquant");
            return $this->redirection("accueil");
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
            return $this->redirection("accueil");
        }
        if(!$colonne) {
            MessageFlash::ajouter("warning", "Colonne inexistante");
            return $this->redirection("accueil");
        }
        if(!$this->issetAndNotNull(["titreCarte", "descriptifCarte", "couleurCarte"])) {
            MessageFlash::ajouter("danger", "Attributs manquants");
            return $this->redirection("afficherFormulaireMiseAJourCarte", ["idCarte" => $_REQUEST["idCarte"]]);
        }

        $originalColonne =(new ColonneRepository())->recupererParClePrimaire(array("idColonne"=>$carte->getIdColonne()));
        if($originalColonne->getIdTableau() !== $colonne->getIdTableau()) {
            MessageFlash::ajouter("danger", "Le tableau de cette colonne n'est pas le même que celui de la colonne d'origine de la carte!");
            return $this->redirection("afficherFormulaireMiseAJourCarte", ["idCarte" => $_REQUEST["idCarte"]]);
        }
        $tableauRepository = new TableauRepository();
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$colonne->getIdTableau()));
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return $this->redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $carte->setIdColonne($colonne->getIdColonne());
        $carte->setTitreCarte($_REQUEST["titreCarte"]);
        $carte->setDescriptifCarte($_REQUEST["descriptifCarte"]);
        $carte->setCouleurCarte($_REQUEST["couleurCarte"]);
        $utilisateurRepository = new UtilisateurRepository();
        if($this->issetAndNotNull(["affectationsCarte"])) {
            foreach ($_REQUEST["affectationsCarte"] as $affectation) {
                /**
                 * @var Utilisateur $utilisateur
                 */
                $utilisateur = $utilisateurRepository->recupererParClePrimaire(array(""=>$affectation));
                if(!$utilisateur) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'existe pas");
                    return $this->redirection("afficherFormulaireMiseAJourCarte", ["idCarte" => $_REQUEST["idCarte"]]);
                }
                if(!$tableauRepository->estParticipantOuProprietaire( $tableau->getIdTableau(),$utilisateur->getLogin())) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'est pas affecté au tableau.");
                    return $this->redirection("afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
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
        return $this->redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }
}