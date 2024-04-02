<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Service\CarteService;
use App\Trellotrolle\Service\CarteServiceInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

//TODO: injection de dépendance
class ControleurCarteAPI extends ControleurGenerique
{
    public function __construct(private CarteServiceInterface $carteService)
    {
    }

    #[Route(path: 'api/carte/afficherCarte/{idCarte}', name: 'afficherCarte', methods: ["GET"])]
    public  function afficherCarte($idCarte): Response
    {
        try {
            $carte = $this->carteService->recupererCarte($idCarte);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
        return new JsonResponse($carte->jsonSerialize(), Response::HTTP_OK);
    }

    //changement uniquement titre / descriptif / couleur
    #[Route(path: 'api/carte/mettreAJourCarte', name: 'modifierCarte', methods: ["PATCH"])]
    public  function mettreAJourCarte(Request $request): Response
    {
        try {
            $content = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $idCarte = $content->idCarte ?? null;
            $titre = $content->titre ?? null;
            $descriptif = $content->descriptif ?? null;
            $couleur = $content->couleur ?? null;
            $this->carteService->mettreAJour($idCarte, $titre, $descriptif, $couleur);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        } catch (JsonException $exception) {
            return new JsonResponse(
                ["error" => "Corps de la requête mal formé"],
                Response::HTTP_BAD_REQUEST
            );
        }
        return new JsonResponse("", Response::HTTP_OK);
    }

    #[Route(path: 'api/carte/ajouterCarte/{idColonne}', name: 'ajouterCarte', methods: ["PUT"])]
    public  function ajouterCarte($idColonne): Response
    {
        try {
            $carte = $this->carteService->ajouter($idColonne);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
        return new JsonResponse($carte->jsonSerialize(), Response::HTTP_OK);
    }

    #[Route(path: 'api/carte/supprimerCarte/{idCarte}', name: 'supprimerCarte', methods: ["DELETE"])]
    public  function supprimerCarte($idCarte): Response
    {
        try {
            $this->carteService->supprimer($idCarte);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
        return new JsonResponse("", Response::HTTP_OK);
    }

    #[Route(path: 'api/carte/deplacerCarte', name: 'deplacerCarteColonne', methods: ["PATCH"])]
    public  function deplacerCarteColonne(Request $request): Response
    {
        //TODO: à faire après avoir réussi à rendre les cartes draggable
        return new Response();
    }
}