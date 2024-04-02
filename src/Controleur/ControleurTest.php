<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Service\CarteService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ControleurTest extends ControleurGenerique
{

    //OK PREUVE QUE JE PEUX POUR L'INSTANT ME PASSER DE JWT
    #[Route(path: 'api/test', name:'test', methods:["GET"])]
    public function test (): Response {
        if (ConnexionUtilisateur::estConnecte()) return new JsonResponse((new CarteService())->recupererCarte(10)->jsonSerialize());
        return new JsonResponse("test");
    }
}