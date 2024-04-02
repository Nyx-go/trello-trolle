<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;

interface UtilisateurServiceInterface
{
    /**
     * @throws ServiceConnexionException
     */
    public function doitEtreConnecte(): void;

    /**
     * @throws ServiceConnexionException
     */
    public function doitEtreDeconnecte(): void;

    public function __construct(UtilisateurRepositoryInterface $utilisateurRepository,);

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function recupererCompte(?string $mail): void;

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function changementMotDePasseRecuperation(?string $nonce, ?string $mdp, ?string $mdp2): void;

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function creerDepuisFormulaire(?string $login, ?string $nom, ?string $prenom, ?string $mdp, ?string $mdp2, ?string $email): void;

    /**
     * @throws ServiceConnexionException
     */
    public function deconnexion(): void;

    /**
     * @throws ServiceConnexionException|ServiceException
     */
    public function connexion(?string $login, ?string $mdp): void;

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function suppresion(): void;

    /**
     * @throws ServiceException
     */
    public function verifyDoublePasswordValidity(?string $mdp, ?string $mdp2): void;

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function miseAJourMdp(?string $mdpAncien, ?string $mdp, ?string $mdp2): void;

    /**
     * @throws ServiceConnexionException
     * @throws ServiceException
     */
    public function miseAJour(?string $nom, ?string $prenom, ?string $email, ?string $mdp): void;

    /**
     * @throws ServiceConnexionException
     */
    public function getUtilisateurCourrant(): Utilisateur;
}