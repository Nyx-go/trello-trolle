<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;

interface CarteServiceInterface
{
    public function __construct(CarteRepositoryInterface $carteRepository, ColonneRepositoryInterface $colonneRepository, TableauRepositoryInterface $tableauRepository);

    /**
     * @throws ServiceException
     */
    public function recupererCarte(?string $idCarte): Carte;

    /**
     * @throws ServiceException
     */
    public function mettreAJour(?string $idCarte, ?string $titre, ?string $descriptif, ?string $couleur): void;

    /**
     * @throws ServiceException
     */
    public function ajouter(string $idColonne): Carte;

    /**
     * @throws ServiceException
     */
    public function supprimer(string $idCarte): void;

    /**
     * @throws ServiceConnexionException
     */
    public function doitEtreConnecte(): void;

    /**
     * @throws ServiceConnexionException
     */
    public function doitEtreDeconnecte(): void;
}