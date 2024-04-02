<?php

namespace App\Trellotrolle\Controleur;


use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\Exception\ServiceConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\TableauServiceInterface;
use App\Trellotrolle\Service\UtilisateurServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurTableau extends ControleurGenerique
{

    public function __construct(
        private TableauServiceInterface $tableauService,
        private UtilisateurServiceInterface $utilisateurService
    )
    {
    }

    public  function afficherErreur($messageErreur = "", $statusCode = "tableau"): Response
    {
        return parent::afficherErreur($messageErreur, $statusCode);
    }

    #[Route(path: '/tableau/{codeTableau}', name:'afficherTableau', methods:["GET"])]
    public  function afficherTableau($codeTableau) : Response {
        try {
            $value = $this->tableauService->afficherTableau($codeTableau);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->redirection("accueil");
        }

        return $this->afficherTwig("tableau/tableau.html.twig",[
            "estProprietaire"=> $value["estProprietaire"],
            "estParticipantOuProprietaire" => $value["estParticipantOuProprietaire"],
            "tableau" => $value["tableau"],
            "colonnes" => $value["colonnes"],
            "affectations" => $value["affectations"],
            "participants" => $value["participants"],
            "data" => $value["data"],
            "utilisateur"=>$value["utilisateur"],
            "affectationsCartes" => $value["affectationsCartes"]
        ]);
    }

    #[Route(path: '/tableau/{idTableau}/modification', name:'afficherFormulaireMiseAJourTableau', methods:["GET"])]
    public  function afficherFormulaireMiseAJourTableau($idTableau): Response {
        try {
            $titreTableau = $this->tableauService->afficherFormulaireMiseAJourTableau($idTableau);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->redirection("afficherListeMesTableaux");
        }

        return $this->afficherTwig("tableau/formulaireMiseAJourTableau.html.twig",[
            "idTableau" => $idTableau,
            "nomTableau" => $titreTableau
        ]);
    }

    #[Route(path: '/tableaux/nouveau', name:'afficherFormulaireCreationTableau', methods:["GET"])]
    public  function afficherFormulaireCreationTableau(): Response {
        try {
            $this->utilisateurService->doitEtreConnecte();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("afficherFormulaireConnexion");
        }
        return $this->afficherTwig(
            "tableau/formulaireCreationTableau.html.twig"
        );
    }

    #[Route(path: '/tableaux/nouveau', name:'creerTableau', methods:["POST"])]
    public  function creerTableau(): Response {
        $nomTableau = $_REQUEST["nomTableau"] ?? null;
        try {
            $codeTableau = $this->tableauService->creerDepuisFormulaire($nomTableau);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->afficherTwig("tableau/afficherFormulaireCreationTableau.html.twig");
        }
        MessageFlash::ajouter("success", "Le tableau a bien été créé !");
        return $this->redirection("afficherTableau", ["codeTableau" => $codeTableau]);
    }

    #[Route(path: '/tableau/modification', name:'mettreAJourTableau', methods:["POST"])]
    public  function mettreAJourTableau(): Response {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $nomTableau = $_REQUEST["nomTableau"] ?? null;

        try {
            $codeTableau = $this->tableauService->mettreAJourTableau($idTableau,$nomTableau);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->redirection("afficherListeMesTableaux");
        }

        MessageFlash::ajouter("success", "Le tableau a bien été modifié !");
        return $this->redirection("afficherTableau", ["codeTableau" => $codeTableau]);

    }

    #[Route(path: '/tableau/{idTableau}/membres/ajout', name:'afficherFormulaireAjoutMembre', methods:["GET"])]
    public  function afficherFormulaireAjoutMembre($idTableau): Response {
        try {
            $value = $this->tableauService->afficherFormulaireAjoutMembre($idTableau);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->redirection("afficherListeMesTableaux");
        }

        return $this->afficherTwig("tableau/formulaireAjoutMembreTableau.html.twig", [
            "tableau" => $value["tableau"],
            "utilisateurs" => $value["filtredUtilisateurs"]
        ]);
    }

    #[Route(path: '/tableau/membres/ajout', name:'ajouterMembre', methods:["POST"])]
    public  function ajouterMembre(): Response {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $login = $_REQUEST["login"] ?? null;
        try {
            $codeTableau = $this->tableauService->ajouterMembre($idTableau,$login);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->afficherTwig("tableau/afficherFormulaireCreationTableau.html.twig");
        }
        MessageFlash::ajouter("success", "Le membre a bien été ajouté !");
        return $this->redirection("afficherTableau", ["codeTableau" => $codeTableau]);
    }

    #[Route(path: '/tableau/{idTableau}/membres/{login}/suppression', name:'supprimerMembre', methods:["GET"])]
    public  function supprimerMembre($login, $idTableau): Response {
        try {
            $codeTableau = $this->tableauService->supprimerMembre($idTableau,$login);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->afficherTwig("tableau/afficherFormulaireCreationTableau.html.twig");
        }
        MessageFlash::ajouter("success", "Le membre a bien été supprimé !");
        return $this->redirection("afficherTableau", ["codeTableau" => $codeTableau]);
    }

    #[Route(path: '/tableaux', name:'afficherListeMesTableaux', methods:["GET"])]
    public  function afficherListeMesTableaux() : Response {
        try {
            $value = $this->tableauService->afficherListeMesTableaux();
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("accueil");
        }
        return $this->afficherTwig("tableau/listeTableauxUtilisateur.html.twig", [
            "tableaux" => $value["tableaux"],
            "estProprietaire"=> $value["estProprietaire"],
            "login"=> $value["login"]
        ]);
    }

    #[Route(path: '/tableau/{idTableau}/quitter', name:'quitterTableau', methods:["GET"])]
    public  function quitterTableau($idTableau): Response {
        try {
            $this->tableauService->quitterTableau($idTableau);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->redirection("afficherListeMesTableaux");
        }
        MessageFlash::ajouter("success", "Vous avez bien quitté le tableau !");
        return $this->redirection("afficherListeMesTableaux");
    }

    #[Route(path: '/tableau/{idTableau}/suppression', name:'supprimerTableau', methods:["GET"])]
    public  function supprimerTableau($idTableau): Response {
        try {
            $this->tableauService->supprimerTableau($idTableau);
        } catch (ServiceConnexionException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return $this->redirection("afficherListeMesTableaux");
        }

        MessageFlash::ajouter("success", "Le tableau a bien été supprimé !");
        return $this->redirection("afficherListeMesTableaux");
    }
}