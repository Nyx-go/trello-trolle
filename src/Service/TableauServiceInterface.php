<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\Repository\AffecteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ParticipeRepositoryInterface;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;

interface TableauServiceInterface
{
    /**
     * @throws ServiceConnexionException
     */
    public function doitEtreConnecte(): void;

    /**
     * @throws ServiceConnexionException
     */
    public function doitEtreDeconnecte(): void;

    public function __construct(TableauRepositoryInterface $tableauRepository, UtilisateurRepositoryInterface $utilisateurRepository, ColonneRepositoryInterface $colonneRepository, CarteRepositoryInterface $carteRepository, AffecteRepositoryInterface $affecteRepository, ParticipeRepositoryInterface $participeRepository,);

    /**
     * @throws ServiceException
     */
    public function afficherTableau($codeTableau): array;

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function creerDepuisFormulaire(?string $nomTableau): string;

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function mettreAJourTableau(?int $idTableau, ?string $nomTableau): string;

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function supprimerTableau(?int $idTableau): void;

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function afficherFormulaireMiseAJourTableau(?int $idTableau): string;

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function afficherFormulaireAjoutMembre(?int $idTableau): array;

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function ajouterMembre(?int $idTableau, ?string $login): string;

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function supprimerMembre(?int $idTableau, ?string $login): string;

    /**
     * @throws ServiceConnexionException
     */
    public function afficherListeMesTableaux(): array;

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function quitterTableau($idTableau): void;
}