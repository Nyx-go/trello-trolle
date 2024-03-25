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
use Symfony\Component\Routing\Attribute\Route;

class ControleurTableau extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $controleur = ""): void
    {
        parent::afficherErreur($messageErreur, "tableau");
    }

    #[Route(path: '/tableau', name:'afficherTableau', methods:["GET"])]
    public static function afficherTableau() : void {
        if(!ControleurTableau::issetAndNotNull(["codeTableau"])) {
            MessageFlash::ajouter("warning", "Code de tableau manquant");
            ControleurTableau::redirection("base", "accueil");
        }
        $code = $_REQUEST["codeTableau"];
        $tableauRepository = new TableauRepository();

        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParCodeTableau($code);
        if(!$tableau) {
            MessageFlash::ajouter("warning", "Tableau inexistant");
            ControleurTableau::redirection("base", "accueil");
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
                    $utilisateur = (new UtilisateurRepository())->recupererParClePrimaire($affectation->getLogin());
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

        ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "{$tableau->getTitreTableau()}",
            "cheminVueBody" => "tableau/tableau.php",
            "estProprietaire"=> $estProprietaire,
            "estParticipantOuProprietaire" => $estParticipantOuProprietaire,
            "tableau" => $tableau,
            "colonnes" => $colonnes,
            "participants" => $participants,
            "data" => $data,
        ]);
    }

    #[Route(path: '/tableau/mise-a-jour', name:'afficherFormulaireMiseAJourTableau', methods:["GET"])]
    public static function afficherFormulaireMiseAJourTableau(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            ControleurTableau::redirection("base", "accueil");
        }
        $tableauRepository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$_REQUEST["idTableau"]));
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            ControleurTableau::redirection("base", "accueil");
        }
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Modification d'un tableau",
            "cheminVueBody" => "tableau/formulaireMiseAJourTableau.php",
            "idTableau" => $_REQUEST["idTableau"],
            "nomTableau" => $tableau->getTitreTableau()
        ]);
    }

    #[Route(path: '/tableau/nouveau', name:'afficherFormulaireCreationTableau', methods:["GET"])]
    public static function afficherFormulaireCreationTableau(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Ajout d'un tableau",
            "cheminVueBody" => "tableau/formulaireCreationTableau.php",
        ]);
    }

    #[Route(path: '/tableau/nouveau', name:'creerTableau', methods:["POST"])]
    public static function creerTableau(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }

        if(!ControleurCarte::issetAndNotNull(["nomTableau"])) {
            MessageFlash::ajouter("danger", "Nom de tableau manquant");
            ControleurTableau::redirection("tableau", "afficherFormulaireCreationTableau");
        }

        $tableau = new Tableau(
            ConnexionUtilisateur::getLoginUtilisateurConnecte(),
            null,
            null,
            $_REQUEST["nomTableau"]
        );

        $idTableau = (new TableauRepository())->ajouter($tableau);
        $codeTableau = hash("sha256", $utilisateur->getLogin().$idTableau); // Je ne sais pas d'où sort le "Unreachable statement", probablement un bug d'affichage
        $tableau->setCodeTableau($codeTableau);
        $tableau->setIdTableau($idTableau);
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
        (new CarteRepository())->ajouter($carte);

        ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/tableau/mise-a-jour', name:'mettreAJourTableau', methods:["POST"])]
    public static function mettreAJourTableau(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant de tableau manquant");
            ControleurTableau::redirection("base", "accueil");
        }
        $tableauRepository = new TableauRepository();

        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTabeau"=>$_REQUEST["idTableau"]));
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            ControleurTableau::redirection("base", "accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["nomTableau"])) {
            MessageFlash::ajouter("danger", "Nom de tableau manquant");
            ControleurTableau::redirection("tableau", "afficherFormulaireMiseAJourTableau", ["idTableau" => $_REQUEST["idTableau"]]);
        }
        if(!$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
        }
        else {
            $tableau->setTitreTableau($_REQUEST["nomTableau"]);
            $tableauRepository->mettreAJour($tableau);
        }
        ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/tableau/membres/ajout', name:'afficherFormulaireAjoutMembre', methods:["GET"])]
    public static function afficherFormulaireAjoutMembre(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            ControleurTableau::redirection("base", "accueil");
        }
        $tableauRepository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTabeau"=>$_REQUEST["idTableau"]));
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            ControleurTableau::redirection("base", "accueil");
        }
        if(!$tableauRepository->estProprietaire($tableau->getIdTableau(),ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $utilisateurRepository = new UtilisateurRepository();

        /**
         * @var Utilisateur[] $utilisateurs
         */
        $utilisateurs = $utilisateurRepository->recupererUtilisateursOrderedPrenomNom();
        $filtredUtilisateurs = array_filter($utilisateurs, function ($u) use ($tableau, $tableauRepository) {return !$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), $u->getLogin());});

        if(empty($filtredUtilisateurs)) {
            MessageFlash::ajouter("warning", "Il n'est pas possible d'ajouter plus de membre à ce tableau.");
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Ajout d'un membre",
            "cheminVueBody" => "tableau/formulaireAjoutMembreTableau.php",
            "tableau" => $tableau,
            "utilisateurs" => $filtredUtilisateurs
        ]);
    }

    #[Route(path: '/tableau/membres/ajout', name:'ajouterMembre', methods:["POST"])]
    public static function ajouterMembre(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            ControleurTableau::redirection("base", "accueil");
        }
        $tableauRepository = new TableauRepository();

        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTabeau"=>$_REQUEST["idTableau"]));
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            ControleurTableau::redirection("base", "accueil");
        }
        if(!$tableauRepository->estProprietaire($tableau->getIdTableau(),ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if(!ControleurCarte::issetAndNotNull(["login"])) {
            MessageFlash::ajouter("danger", "Login du membre à ajouter manquant");
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $utilisateurRepository = new UtilisateurRepository();
        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire(array("login"=>$_REQUEST["login"]));
        if(!$utilisateur) {
            MessageFlash::ajouter("danger", "Utlisateur inexistant");
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if($tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), $utilisateur->getLogin())) {
            MessageFlash::ajouter("warning", "Ce membre est déjà membre du tableau.");
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $participe = new Participe(
            $_REQUEST["idTableau"],
            $utilisateur->getLogin()
        );
        (new ParticipeRepository())->ajouter($participe);

        ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/tableau/membres/suppression', name:'supprimerMembre', methods:["GET"])]
    public static function supprimerMembre(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            ControleurTableau::redirection("base", "accueil");
        }
        $tableauRepository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTabeau"=>$_REQUEST["idTableau"]));
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            ControleurTableau::redirection("base", "accueil");
        }
        if(!$tableauRepository->estProprietaire($tableau->getIdTableau(),ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if(!ControleurCarte::issetAndNotNull(["login"])) {
            MessageFlash::ajouter("danger", "Login du membre à supprimer manquant");
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $utilisateurRepository = new UtilisateurRepository();
        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire(array("login"=>$_REQUEST["login"]));
        if(!$utilisateur) {
            MessageFlash::ajouter("danger", "Utlisateur inexistant");
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if($tableauRepository->estProprietaire($tableau->getIdTableau(),$utilisateur->getLogin())) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas vous supprimer du tableau.");
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if(!$tableauRepository->estParticipant($tableau->getIdTableau(),$utilisateur->getLogin())) {
            MessageFlash::ajouter("danger", "Cet utilisateur n'est pas membre du tableau");
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        (new ParticipeRepository())->supprimer(array($tableau->getIdTableau(), $utilisateur->getLogin()));

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
        ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route(path: '/tableaux', name:'afficherListeMesTableaux', methods:["GET"])]
    public static function afficherListeMesTableaux() : void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        $tableauRepository = new TableauRepository();
        $login = ConnexionUtilisateur::getLoginUtilisateurConnecte();
        $tableaux = $tableauRepository->recupererTableauxOuUtilisateurEstMembre($login);
        $estProprietaire = [];
        foreach ($tableaux as $tableau){
            $estProprietaire[$tableau->getIdTableau()] = $tableauRepository->estProprietaire($tableau->getIdTableau(), $login);
        }
        ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Liste des tableaux de $login",
            "cheminVueBody" => "tableau/listeTableauxUtilisateur.php",
            "tableaux" => $tableaux,
            "estProprietaire"=>$estProprietaire
        ]);
    }

    #[Route(path: '/tableau/quitter', name:'quitterTableau', methods:["GET"])]
    public static function quitterTableau(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        $tableauRepository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$_REQUEST["idTableau"]));
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }

        $utilisateurRepository = new UtilisateurRepository();

        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire(array("login"=>ConnexionUtilisateur::getLoginUtilisateurConnecte()));
        if($tableauRepository->estProprietaire($tableau->getIdTableau(),$utilisateur->getLogin())) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas quitter ce tableau");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        if(!$tableauRepository->estParticipant($tableau->getIdTableau(),ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'appartenez pas à ce tableau");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        $participants = array_filter($tableau->getParticipants(), function ($u) use ($utilisateur) {return $u->getLogin() !== $utilisateur->getLogin();});
        $tableau->setParticipants($participants);
        $tableauRepository->mettreAJour($tableau);

        $carteRepository = new CarteRepository();

        /**
         * @var Carte[] $cartes
         */
        $cartes = $carteRepository->recupererCartesTableau($tableau->getIdTableau());
        foreach ($cartes as $carte) {
            $affectations = array_filter($carte->getAffectationsCarte(), function ($u) use ($utilisateur) {return $u->getLogin() != $utilisateur->getLogin();});
            $carte->setAffectationsCarte($affectations);
            $carteRepository->mettreAJour($carte);
        }
        ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
    }

    #[Route(path: '/tableau/suppression', name:'supprimerTableau', methods:["GET"])]
    public static function supprimerTableau(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant de tableau manquant");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        $tableauRepository = new TableauRepository();
        $idTableau = $_REQUEST["idTableau"];
        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParClePrimaire(array("idTableau"=>$idTableau));
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        if(!$tableauRepository->estProprietaire($tableau->getIdTableau(),ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        if($tableauRepository->getNombreTableauxTotalUtilisateur(ConnexionUtilisateur::getLoginUtilisateurConnecte()) == 1) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas supprimer ce tableau car cela entrainera la supression du compte");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        $tableauRepository->supprimer($idTableau);
        ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
    }
}