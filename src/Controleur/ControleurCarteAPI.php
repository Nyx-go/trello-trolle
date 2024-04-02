<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Service\CarteService;
use App\Trellotrolle\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

//TODO: injection de dépendance
class ControleurCarteAPI extends ControleurGenerique
{
    #[Route(path: 'api/carte/afficherCarte', name:'afficherCarte', methods:["GET"])]
    public static function afficherCarte(): Response {
        try {
            $idCarte = $_GET["idCarte"] ?? null;
            $carte = (new CarteService())->afficherCarte($idCarte);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
        return new JsonResponse($carte->jsonSerialize(),Response::HTTP_OK);
    }


}