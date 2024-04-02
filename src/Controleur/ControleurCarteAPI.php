<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Service\CarteService;
use App\Trellotrolle\Service\Exception\ServiceConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

//TODO: injection de dépendance
class ControleurCarteAPI extends ControleurGenerique
{
    #[Route(path: 'api/carte/afficherCarte/{idCarte}', name: 'afficherCarte', methods: ["GET"])]
    public static function afficherCarte($idCarte): Response
    {
        try {
            $carte = (new CarteService())->recupererCarte($idCarte);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
        return new JsonResponse($carte->jsonSerialize(), Response::HTTP_OK);
    }

    //changement uniquement titre / descriptif / couleur
    #[Route(path: 'api/carte/mettreAJourCarte', name: 'modifierCarte', methods: ["PATCH"])]
    public static function mettreAJourCarte(Request $request): Response
    {
        try {
            $content = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $idCarte = $content->idCarte ?? null;
            $titre = $content->titre ?? null;
            $descriptif = $content->descriptif ?? null;
            $couleur = $content->couleur ?? null;
            (new CarteService())->mettreAJour($idCarte , $titre , $descriptif , $couleur);
        } catch (ServiceException|ServiceConnexionException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        } catch (JsonException $exception) {
            return new JsonResponse(
                ["error" => "Corps de la requête mal formé"],
                Response::HTTP_BAD_REQUEST
            );
        }
        return new JsonResponse("",Response::HTTP_OK);
    }


}