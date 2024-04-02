<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;

interface CarteRepositoryInterface
{
    public function myDbInt();

    public function formatNomsColonnes(): string;

    /**
     * @return AbstractDataObject[]
     */
    public function recuperer(): array;

    /**
     * @return AbstractDataObject[]
     */
    public function recupererOrdonne($attributs, $sens = "ASC"): array;

    /**
     * @return AbstractDataObject[]
     */
    public function recupererPlusieursPar(string $nomAttribut, $valeur): array;

    /**
     * @return AbstractDataObject[]
     */
    public function recupererPlusieursParOrdonne(string $nomAttribut, $valeur, $attributs, $sens = "ASC"): array;

    public function recupererPar(string $nomAttribut, $valeur): ?AbstractDataObject;

    public function recupererParClePrimaire(array $valeurClePrimaire): ?AbstractDataObject;

    public function supprimer(array $valeurClePrimaire): bool;

    public function mettreAJour(AbstractDataObject $object): bool;

    public function ajouter(AbstractDataObject $object);

    public function getNextId(string $type): int;

    public function __construct(ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees);

    public function getNomTable(): string;

    public function getNomCle(): array;

    public function getNomsColonnes(): array;

    public function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject;

    public function recupererCartesColonne(int $idcolonne): array;

    public function recupererCartesTableau(int $idTableau): array;

    /**
     * @return Carte[]
     */
    public function recupererCartesUtilisateur(string $login): array;

    public function getNombreCartesTotalUtilisateur(string $login): int;

    public function getNextIdCarte(): int;

    public function getTableauByIdCarte($idCarte);

    public function recupererCarteParId(string $idCarte): ?AbstractDataObject;
}