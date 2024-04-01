<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MailerBase;
use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Participe;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\HTTP\Session;
use App\Trellotrolle\Modele\Repository\AffecteRepository;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\ParticipeRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Service\Exception\ServiceConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;
use Exception;

class TableauService extends ServiceGenerique
{

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function creerDepuisFormulaire(?string $nomTableau): String
    {
        /**
         * @throws ServiceConnexionException
         * @throws ServiceException
         */
        self::doitEtreConnecte();
        if (is_null($nomTableau)) throw new ServiceException("Nom de tableau manquant.");

        $idUtilisateur = ConnexionUtilisateur::getLoginUtilisateurConnecte();

        $tableau = new Tableau(
            $idUtilisateur,
            null,
            null,
            $nomTableau
        );

        $idTableau = (new TableauRepository())->ajouter($tableau);
        $codeTableau = hash("sha256", $idUtilisateur.$idTableau);
        $tableau->setIdTableau($idTableau);
        $tableau->setCodeTableau($codeTableau);
        $succesMiseAJour = (new TableauRepository())->mettreAJour($tableau);

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

        if (!$idTableau || !$idColonne || !$idCarte || !$succesMiseAJour) {
            throw new ServiceException("Une erreur est survenue lors de la création du tableau.");
        }
        return $tableau->getCodeTableau();
    }

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function mettreAJourTableau(?int $idTableau,?string $nomTableau): String {
        self::doitEtreConnecte();
        if (is_null($idTableau) || is_null($nomTableau)) throw new ServiceException("Identifiant ou nom de tableau manquant.");

        $tableauRepository = new TableauRepository();

        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$idTableau));
        if(!$tableau) {
            throw new ServiceException("Tableau inexistant.");
        }
        if(!$tableauRepository->estProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            throw new ServiceException("Vous n'avez pas de droits d'édition sur ce tableau");
        }
        else {
            $tableau->setTitreTableau($nomTableau);
            $succesMiseAJour = $tableauRepository->mettreAJour($tableau);

            if (!$succesMiseAJour) {
                throw new ServiceException("Une erreur est survenue lors de la modification du tableau.");
            }
        }
        return $tableau->getCodeTableau();
    }

    public function supprimerTableau(?int $idTableau): void {
        self::doitEtreConnecte();

        $tableauRepository = new TableauRepository();

        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$idTableau));
        if(!$tableau) {
            throw new ServiceException("Tableau inexistant");
        }
        if(!$tableauRepository->estProprietaire($tableau->getIdTableau(),ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            throw new ServiceException("Vous n'êtes pas propriétaire de ce tableau");
        }

        $succesSuppression =  $tableauRepository->supprimer(array("idtableau"=>$idTableau));

        if (!$succesSuppression) {
            throw new ServiceException("Une erreur est survenue lors de la suppression du tableau.");
        }
    }

    public function afficherFormulaireMiseAJourTableau(?int $idTableau): String {
        self::doitEtreConnecte();

        $tableauRepository = new TableauRepository();

        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$idTableau));
        if(!$tableau) {
            throw new ServiceException("Tableau inexistant");
        }
        if(!$tableauRepository->estProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            throw new ServiceException("Vous n'êtes pas propriétaire de ce tableau");
        }
        return $tableau->getTitreTableau();
    }

    public function afficherFormulaireAjoutMembre(?int $idTableau): array
    {
        self::doitEtreConnecte();

        $tableauRepository = new TableauRepository();

        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau" => $idTableau));
        if (!$tableau) {
            throw new ServiceException("Tableau inexistant");
        }
        if (!$tableauRepository->estProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            throw new ServiceException("Vous n'êtes pas propriétaire de ce tableau");
        }

        $utilisateurRepository = new UtilisateurRepository();


        $utilisateurs = $utilisateurRepository->recupererUtilisateursOrderedPrenomNom();
        $filtredUtilisateurs = array_filter($utilisateurs, function ($u) use ($tableau, $tableauRepository) {
            return !$tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), $u->getLogin());
        });

        if (empty($filtredUtilisateurs)) {
            throw new ServiceException("Il n'est pas possible d'ajouter plus de membre à ce tableau.");
        }
        return array("tableau" => $tableau,"filtredUtilisateurs" => $filtredUtilisateurs);
    }

    public function ajouterMembre(?int $idTableau,?string $login): String {
        self::doitEtreConnecte();

        if (is_null($idTableau) || is_null($login)) throw new ServiceException("Login ou identifiant de tableau manquant.");

        $tableauRepository = new TableauRepository();

        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$idTableau));
        if (!$tableau) {
            throw new ServiceException("Tableau inexistant");
        }
        if (!$tableauRepository->estProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            throw new ServiceException("Vous n'êtes pas propriétaire de ce tableau");
        }

        $utilisateurRepository = new UtilisateurRepository();

        $utilisateur = $utilisateurRepository->recupererParClePrimaire(array("login"=>$login));
        if(!$utilisateur) {
            throw new ServiceException("Utlisateur inexistant");
        }
        if($tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), $utilisateur->getLogin())) {
            throw new ServiceException("Ce membre est déjà membre du tableau.");
        }

        $participe = new Participe(
            $_REQUEST["idTableau"],
            $utilisateur->getLogin()
        );
        $succesSauvegarde = (new ParticipeRepository())->ajouter($participe);

        if (!$succesSauvegarde) {
            throw new ServiceException("Une erreur est survenue lors de l'ajout du membre'.");
        }
        return $tableau->getCodeTableau();
    }

    public function supprimerMembre(?int $idTableau,?string $login): String {
        self::doitEtreConnecte();


        $tableauRepository = new TableauRepository();
        $tableau = $tableauRepository->recupererParClePrimaire(array("idtableau"=>$idTableau));
        if (!$tableau) {
            throw new ServiceException("Tableau inexistant");
        }
        if (!$tableauRepository->estProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            throw new ServiceException("Vous n'êtes pas propriétaire de ce tableau");
        }

        $utilisateurRepository = new UtilisateurRepository();

        $utilisateur = $utilisateurRepository->recupererParClePrimaire(array("login"=>$login));
        if(!$utilisateur) {
            throw new ServiceException("Utlisateur inexistant");
        }
        if($tableauRepository->estProprietaire($tableau->getIdTableau(),$utilisateur->getLogin())) {
            throw new ServiceException("danger", "Vous ne pouvez pas vous supprimer du tableau.");
        }
        if(!$tableauRepository->estParticipant($tableau->getIdTableau(),$utilisateur->getLogin())) {
            throw new ServiceException("Cet utilisateur n'est pas membre du tableau");
        }

        $succesSuppression = (new ParticipeRepository())->supprimer(array("idtableau"=>$tableau->getIdTableau(), "login"=>$utilisateur->getLogin()));

        $cartesRepository = new CarteRepository();
        $cartes = $cartesRepository->recupererCartesTableau($tableau->getIdTableau());
        foreach ($cartes as $carte) {
            $affectations = (new AffecteRepository())->recupererParIdCarte($carte->getIdCarte());
            foreach ($affectations as $affectation) {
                if ($affectation->getLogin() == $utilisateur->getLogin()) {
                    (new AffecteRepository())->supprimer(array("idCarte"=>$carte->getIdCarte(), "login"=>$utilisateur->getLogin()));
                }
            }
        }

        if (!$succesSuppression) {
            throw new ServiceException( "Une erreur est survenue lors de la suppression du membre.");
        }
        return $tableau->getCodeTableau();
    }
}