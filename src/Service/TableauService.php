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
use App\Trellotrolle\Modele\Repository\AffecteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ParticipeRepository;
use App\Trellotrolle\Modele\Repository\ParticipeRepositoryInterface;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;
use Exception;

class TableauService extends ServiceGenerique implements TableauServiceInterface
{
    
    public function __construct(
        private TableauRepositoryInterface $tableauRepository,
        private UtilisateurRepositoryInterface $utilisateurRepository,
        private ColonneRepositoryInterface $colonneRepository,
        private CarteRepositoryInterface $carteRepository,
        private AffecteRepositoryInterface $affecteRepository,
        private ParticipeRepositoryInterface $participeRepository,
    )
    {
    }

    /**
     * @throws ServiceException
     */
    public function afficherTableau($codeTableau) : array {
        if (is_null($codeTableau)) throw new ServiceException("Code de tableau manquant.");
        

        $tableau = $this->tableauRepository->recupererParCodeTableau($codeTableau);
        if(!$tableau) {
            throw new ServiceException("Tableau inexistant.");
        }

        $colonnes = $this->colonneRepository->recupererColonnesTableau($tableau->getIdTableau());
        $data = [];
        $affectations = [];
        $affectationsCartes = [];
        
        foreach ($colonnes as $colonne) {
            $cartes = $this->carteRepository->recupererCartesColonne($colonne->getIdColonne());
            foreach ($cartes as $carte) {
                $affectationsCartes[$carte->getIdCarte()] = [];
                $affectationsCarte = $this->affecteRepository->recupererParIdCarte($carte->getIdCarte());
                foreach ($affectationsCarte as $affectation) {
                    $utilisateur = $this->utilisateurRepository->recupererParClePrimaire(array("login"=>$affectation->getLogin()));
                    if(!isset($affectations[$utilisateur->getLogin()])) {
                        $affectations[$utilisateur->getLogin()] = ["infos" => $utilisateur, "colonnes" => []];
                    }
                    if(!isset($affectations[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()])) {
                        $affectations[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()] = [$colonne->getTitreColonne(), 0];
                    }
                    $affectations[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()][1]++;
                    $affectationsCartes[$carte->getIdCarte()][] = $utilisateur;
                }
            }
            $data[] = $cartes;
        }

        if(ConnexionUtilisateur::estConnecte()) {
            $estProprietaire = $this->tableauRepository->estProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte());
            $estParticipantOuProprietaire = $this->tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte());
        }
        else {
            $estProprietaire =false;
            $estParticipantOuProprietaire = false;
        }

        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire(array("login"=>$tableau->getLogin()));
        $participes = $this->participeRepository->recupererParIdTableau($tableau->getIdTableau());

        $participants = [];

        foreach ($participes as $participe) {
            $participants[] = $this->utilisateurRepository->recupererParClePrimaire(["login" => $participe->getLogin()]);
        }

        return array(
            "estProprietaire"=> $estProprietaire,
            "estParticipantOuProprietaire" => $estParticipantOuProprietaire,
            "tableau" => $tableau,
            "colonnes" => $colonnes,
            "affectations" => $affectations,
            "participants" => $participants,
            "data" => $data,
            "utilisateur"=>$utilisateur,
            "affectationsCartes" => $affectationsCartes
        );
    }

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function creerDepuisFormulaire(?string $nomTableau): String
    {
        self::doitEtreConnecte();
        if (is_null($nomTableau)) throw new ServiceException("Nom de tableau manquant.");

        $idUtilisateur = ConnexionUtilisateur::getLoginUtilisateurConnecte();

        $tableau = new Tableau(
            $idUtilisateur,
            null,
            null,
            $nomTableau
        );

        $idTableau = $this->tableauRepository->ajouter($tableau);
        $codeTableau = hash("sha256", $idUtilisateur.$idTableau);
        $tableau->setIdTableau($idTableau);
        $tableau->setCodeTableau($codeTableau);
        $succesMiseAJour = $this->tableauRepository->mettreAJour($tableau);

        $colonne = new Colonne(
            $idTableau,
            null,
            "Colonne 1"
        );
        $idColonne = $this->colonneRepository->ajouter($colonne);

        $carte = new Carte(
            $idColonne,
            null,
            "Carte 1",
            "Exemple de carte",
            "#FFFFFF"
        );

        $idCarte = $this->carteRepository->ajouter($carte);

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


        $tableau = $this->tableauRepository->recupererParClePrimaire(array("idtableau"=>$idTableau));
        if(!$tableau) {
            throw new ServiceException("Tableau inexistant.");
        }
        if(!$this->tableauRepository->estProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            throw new ServiceException("Vous n'avez pas de droits d'édition sur ce tableau");
        }
        else {
            $tableau->setTitreTableau($nomTableau);
            $succesMiseAJour = $this->tableauRepository->mettreAJour($tableau);

            if (!$succesMiseAJour) {
                throw new ServiceException("Une erreur est survenue lors de la modification du tableau.");
            }
        }
        return $tableau->getCodeTableau();
    }

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function supprimerTableau(?int $idTableau): void {
        self::doitEtreConnecte();
        if (is_null($idTableau)) throw new ServiceException("Identifiant de tableau manquant.");


        $tableau = $this->tableauRepository->recupererParClePrimaire(array("idtableau"=>$idTableau));
        if(!$tableau) {
            throw new ServiceException("Tableau inexistant");
        }
        if(!$this->tableauRepository->estProprietaire($tableau->getIdTableau(),ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            throw new ServiceException("Vous n'êtes pas propriétaire de ce tableau");
        }

        $succesSuppression =  $this->tableauRepository->supprimer(array("idtableau"=>$idTableau));

        if (!$succesSuppression) {
            throw new ServiceException("Une erreur est survenue lors de la suppression du tableau.");
        }
    }

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function afficherFormulaireMiseAJourTableau(?int $idTableau): String {
        self::doitEtreConnecte();

        if (is_null($idTableau)) throw new ServiceException("Identifiant de tableau manquant.");

        $tableau = $this->tableauRepository->recupererParClePrimaire(array("idtableau"=>$idTableau));
        if(!$tableau) {
            throw new ServiceException("Tableau inexistant");
        }
        if(!$this->tableauRepository->estProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            throw new ServiceException("Vous n'êtes pas propriétaire de ce tableau");
        }
        return $tableau->getTitreTableau();
    }

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function afficherFormulaireAjoutMembre(?int $idTableau): array
    {
        self::doitEtreConnecte();

        if (is_null($idTableau)) throw new ServiceException("Identifiant de tableau manquant.");

        $tableau = $this->tableauRepository->recupererParClePrimaire(array("idtableau" => $idTableau));
        if (!$tableau) {
            throw new ServiceException("Tableau inexistant");
        }
        if (!$this->tableauRepository->estProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            throw new ServiceException("Vous n'êtes pas propriétaire de ce tableau");
        }



        $utilisateurs = $this->utilisateurRepository->recupererUtilisateursOrderedPrenomNom();
        $filtredUtilisateurs = array_filter($utilisateurs, function ($u) use ($tableau) {
            return !$this->tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), $u->getLogin());
        });

        if (empty($filtredUtilisateurs)) {
            throw new ServiceException("Il n'est pas possible d'ajouter plus de membre à ce tableau.");
        }
        return array("tableau" => $tableau,"filtredUtilisateurs" => $filtredUtilisateurs);
    }

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function ajouterMembre(?int $idTableau,?string $login): String {
        self::doitEtreConnecte();

        if (is_null($idTableau) || is_null($login)) throw new ServiceException("Login ou identifiant de tableau manquant.");

        $tableau = $this->tableauRepository->recupererParClePrimaire(array("idtableau"=>$idTableau));
        if (!$tableau) {
            throw new ServiceException("Tableau inexistant");
        }
        if (!$this->tableauRepository->estProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            throw new ServiceException("Vous n'êtes pas propriétaire de ce tableau");
        }


        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire(array("login"=>$login));
        if(!$utilisateur) {
            throw new ServiceException("Utlisateur inexistant");
        }
        if($this->tableauRepository->estParticipantOuProprietaire($tableau->getIdTableau(), $utilisateur->getLogin())) {
            throw new ServiceException("Ce membre est déjà membre du tableau.");
        }

        $participe = new Participe(
            $_REQUEST["idTableau"],
            $utilisateur->getLogin()
        );
        $succesSauvegarde = $this->participeRepository->ajouter($participe);

        if (!$succesSauvegarde) {
            throw new ServiceException("Une erreur est survenue lors de l'ajout du membre'.");
        }
        return $tableau->getCodeTableau();
    }

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function supprimerMembre(?int $idTableau,?string $login): String {
        self::doitEtreConnecte();

        if (is_null($idTableau) || is_null($login)) throw new ServiceException("Login ou identifiant de tableau manquant.");

        $tableau = $this->tableauRepository->recupererParClePrimaire(array("idtableau"=>$idTableau));
        if (!$tableau) {
            throw new ServiceException("Tableau inexistant");
        }
        if (!$this->tableauRepository->estProprietaire($tableau->getIdTableau(), ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            throw new ServiceException("Vous n'êtes pas propriétaire de ce tableau");
        }


        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire(array("login"=>$login));
        if(!$utilisateur) {
            throw new ServiceException("Utlisateur inexistant");
        }
        if($this->tableauRepository->estProprietaire($tableau->getIdTableau(),$utilisateur->getLogin())) {
            throw new ServiceException("danger", "Vous ne pouvez pas vous supprimer du tableau.");
        }
        if(!$this->tableauRepository->estParticipant($tableau->getIdTableau(),$utilisateur->getLogin())) {
            throw new ServiceException("Cet utilisateur n'est pas membre du tableau");
        }

        $succesSuppression = $this->participeRepository->supprimer(array("idtableau"=>$tableau->getIdTableau(), "login"=>$utilisateur->getLogin()));

        $cartes = $this->carteRepository->recupererCartesTableau($tableau->getIdTableau());

        foreach ($cartes as $carte) {
            $affectations = $this->affecteRepository->recupererParIdCarte($carte->getIdCarte());
            foreach ($affectations as $affectation) {
                if ($affectation->getLogin() == $utilisateur->getLogin()) {
                    $succesSuppressionCarte = $this->affecteRepository->supprimer(array("idCarte"=>$carte->getIdCarte(), "login"=>$utilisateur->getLogin()));
                    if (!$succesSuppressionCarte) {
                        throw new ServiceException( "Une erreur est survenue lors de la suppression du membre.");
                    }
                }
            }
        }

        if (!$succesSuppression) {
            throw new ServiceException( "Une erreur est survenue lors de la suppression du membre.");
        }
        return $tableau->getCodeTableau();
    }

    /**
     * @throws ServiceConnexionException
     */
    public function afficherListeMesTableaux() : array {
        self::doitEtreConnecte();

        $login = ConnexionUtilisateur::getLoginUtilisateurConnecte();
        $tableaux = $this->tableauRepository->recupererTableauxOuUtilisateurEstMembre($login);
        $estProprietaire = [];
        foreach ($tableaux as $tableau){
            $estProprietaire[$tableau->getIdTableau()] = $this->tableauRepository->estProprietaire($tableau->getIdTableau(), $login);
        }
        return array("tableaux" => $tableaux,
            "estProprietaire"=>$estProprietaire,
            "login"=>$login);
    }

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function quitterTableau($idTableau): void {
        self::doitEtreConnecte();

        if (is_null($idTableau)) throw new ServiceException("Identifiant de tableau manquant.");


        $tableau = $this->tableauRepository->recupererParClePrimaire(array("idtableau"=>$idTableau));
        if (!$tableau) {
            throw new ServiceException("Tableau inexistant");
        }

        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire(array("login"=>ConnexionUtilisateur::getLoginUtilisateurConnecte()));

        if($this->tableauRepository->estProprietaire($tableau->getIdTableau(),$utilisateur->getLogin())) {
            throw new ServiceException("Vous ne pouvez pas quitter votre propre tableau");
        }
        if(!$this->tableauRepository->estParticipant($tableau->getIdTableau(),ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            throw new ServiceException("Vous n'appartenez pas à ce tableau");
        }

        $succesSuppression = $this->participeRepository->supprimer(array("idtableau"=>$tableau->getIdTableau(), "login"=>$utilisateur->getLogin()));

        $cartes = $this->carteRepository->recupererCartesTableau($tableau->getIdTableau());

        foreach ($cartes as $carte) {
            $affectations = $this->affecteRepository->recupererParIdCarte($carte->getIdCarte());
            foreach ($affectations as $affectation) {
                $succesSuppressionCarte = $this->affecteRepository->supprimer(array("idCarte"=>$carte->getIdCarte(), "login"=>$utilisateur->getLogin()));
                if (!$succesSuppressionCarte) {
                    throw new ServiceException( "Une erreur est survenue lorsque vous avez essayé de quitter le tableau.");
                }
            }
        }
        if (!$succesSuppression) {
            throw new ServiceException( "Une erreur est survenue lorsque vous avez essayé de quitter le tableau.");
        }
    }
}