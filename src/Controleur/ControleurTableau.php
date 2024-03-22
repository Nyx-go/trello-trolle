<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
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
                foreach ($carte->getAffectationsCarte() as $utilisateur) {
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

        ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "{$tableau->getTitreTableau()}",
            "cheminVueBody" => "tableau/tableau.php",
            "tableau" => $tableau,
            "colonnes" => $colonnes,
            "participants" => $participants,
            "data" => $data,
        ]);
    }

    #[Route(path: '/tableau', name:'afficherTableau', methods:["GET"])]
    public static function afficherFormulaireMiseAJourTableau(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            ControleurTableau::redirection("base", "accueil");
        }
        $repository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            ControleurTableau::redirection("base", "accueil");
        }
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
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

    public static function afficherFormulaireCreationTableau(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Ajout d'un tableau",
            "cheminVueBody" => "tableau/formulaireCreationTableau.php",
        ]);
    }

    public static function creerTableau(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        $utilisateurRepository = new UtilisateurRepository();

        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire(ConnexionUtilisateur::getLoginUtilisateurConnecte());
        if(!ControleurCarte::issetAndNotNull(["nomTableau"])) {
            MessageFlash::ajouter("danger", "Nom de tableau manquant");
            ControleurTableau::redirection("tableau", "afficherFormulaireCreationTableau");
        }
        $tableauRepository = new TableauRepository();
        $idTableau = $tableauRepository->getNextIdTableau();
        $codeTableau = hash("sha256", $utilisateur->getLogin().$idTableau);

        $colonneRepository = new ColonneRepository();
        $idColonne1 = $colonneRepository->getNextIdColonne();
        $nomColonne1 = "TODO";

        $nomColonne2 = "DOING";
        $idColonne2 = $idColonne1 + 1;

        $nomColonne3 = "DONE";
        $idColonne3 = $idColonne1 + 2;

        $carteInitiale = "Exemple";
        $descriptifInitial = "Exemple de carte";

        $carteRepository = new CarteRepository();

        $idCarte1 = $carteRepository->getNextIdCarte();
        $idCarte2 = $idCarte1 + 1;
        $idCarte3 = $idCarte1 + 2;

        $tableau = new Tableau(
            $utilisateur,
            $idTableau,
            $codeTableau,
            $_REQUEST["nomTableau"],
            []
        );

        $carte1 = new Carte(
            new Colonne(
                $tableau,
                $idColonne1,
                $nomColonne1
            ),
            $idCarte1,
            $carteInitiale,
            $descriptifInitial,
            "#FFFFFF",
            []
        );

        $carte2 = new Carte(
            new Colonne(
                $tableau,
                $idColonne2,
                $nomColonne2
            ),
            $idCarte2,
            $carteInitiale,
            $descriptifInitial,
            "#FFFFFF",
            []
        );

        $carte3 = new Carte(
            new Colonne(
                $tableau,
                $idColonne3,
                $nomColonne3
            ),
            $idCarte3,
            $carteInitiale,
            $descriptifInitial,
            "#FFFFFF",
            []
        );

        $carteRepository->ajouter($carte1);
        $carteRepository->ajouter($carte2);
        $carteRepository->ajouter($carte3);

        ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    public static function mettreAJourTableau(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant de tableau manquant");
            ControleurTableau::redirection("base", "accueil");
        }
        $repository = new TableauRepository();

        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            ControleurTableau::redirection("base", "accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["nomTableau"])) {
            MessageFlash::ajouter("danger", "Nom de tableau manquant");
            ControleurTableau::redirection("tableau", "afficherFormulaireMiseAJourTableau", ["idTableau" => $_REQUEST["idTableau"]]);
        }
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
        }
        else {
            $tableau->setTitreTableau($_REQUEST["nomTableau"]);
            $repository->mettreAJour($tableau);
        }
        ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    public static function afficherFormulaireAjoutMembre(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            ControleurTableau::redirection("base", "accueil");
        }
        $repository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            ControleurTableau::redirection("base", "accueil");
        }
        if(!$tableau->estProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $utilisateurRepository = new UtilisateurRepository();

        /**
         * @var Utilisateur[] $utilisateurs
         */
        $utilisateurs = $utilisateurRepository->recupererUtilisateursOrderedPrenomNom();
        $filtredUtilisateurs = array_filter($utilisateurs, function ($u) use ($tableau) {return !$tableau->estParticipantOuProprietaire($u->getLogin());});

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

    public static function ajouterMembre(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            ControleurTableau::redirection("base", "accueil");
        }
        $repository = new TableauRepository();

        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            ControleurTableau::redirection("base", "accueil");
        }
        if(!$tableau->estProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
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
        $utilisateur = $utilisateurRepository->recupererParClePrimaire($_REQUEST["login"]);
        if(!$utilisateur) {
            MessageFlash::ajouter("danger", "Utlisateur inexistant");
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if($tableau->estParticipantOuProprietaire($utilisateur->getLogin())) {
            MessageFlash::ajouter("warning", "Ce membre est déjà membre du tableau.");
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $participants = $tableau->getParticipants();
        $participants[] = $utilisateur;
        $tableau->setParticipants($participants);
        $repository->mettreAJour($tableau);

        ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    public static function supprimerMembre(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            ControleurTableau::redirection("base", "accueil");
        }
        $repository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            ControleurTableau::redirection("base", "accueil");
        }
        if(!$tableau->estProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
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
        $utilisateur = $utilisateurRepository->recupererParClePrimaire($_REQUEST["login"]);
        if(!$utilisateur) {
            MessageFlash::ajouter("danger", "Utlisateur inexistant");
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if($tableau->estProprietaire($utilisateur->getLogin())) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas vous supprimer du tableau.");
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if(!$tableau->estParticipant($utilisateur->getLogin())) {
            MessageFlash::ajouter("danger", "Cet utilisateur n'est pas membre du tableau");
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $participants = array_filter($tableau->getParticipants(), function ($u) use ($utilisateur) {return $u->getLogin() !== $utilisateur->getLogin();});
        $tableau->setParticipants($participants);
        $repository->mettreAJour($tableau);

        $cartesRepository = new CarteRepository();
        $cartes = $cartesRepository->recupererCartesTableau($tableau->getIdTableau());
        foreach ($cartes as $carte) {
            $affectations = array_filter($carte->getAffectationsCarte(), function ($u) use ($utilisateur) {return $u->getLogin() != $utilisateur->getLogin();});
            $carte->setAffectationsCarte($affectations);
            $cartesRepository->mettreAJour($carte);
        }
        ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    public static function afficherListeMesTableaux() : void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        $repository = new TableauRepository();
        $login = ConnexionUtilisateur::getLoginUtilisateurConnecte();
        $tableaux = $repository->recupererTableauxOuUtilisateurEstMembre($login);
        ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Liste des tableaux de $login",
            "cheminVueBody" => "tableau/listeTableauxUtilisateur.php",
            "tableaux" => $tableaux
        ]);
    }

    public static function quitterTableau(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        $repository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }

        $utilisateurRepository = new UtilisateurRepository();

        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire(ConnexionUtilisateur::getLoginUtilisateurConnecte());
        if($tableau->estProprietaire($utilisateur->getLogin())) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas quitter ce tableau");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        if(!$tableau->estParticipant(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'appartenez pas à ce tableau");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        $participants = array_filter($tableau->getParticipants(), function ($u) use ($utilisateur) {return $u->getLogin() !== $utilisateur->getLogin();});
        $tableau->setParticipants($participants);
        $repository->mettreAJour($tableau);

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

    public static function supprimerTableau(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant de tableau manquant");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        $repository = new TableauRepository();
        $idTableau = $_REQUEST["idTableau"];
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($idTableau);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        if(!$tableau->estProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        if($repository->getNombreTableauxTotalUtilisateur(ConnexionUtilisateur::getLoginUtilisateurConnecte()) == 1) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas supprimer ce tableau car cela entrainera la supression du compte");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        $repository->supprimer($idTableau);
        ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
    }
}