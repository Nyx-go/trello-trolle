<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\Conteneur;
use App\Trellotrolle\Lib\MessageFlash;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Twig\Environment;

class ControleurGenerique {

    protected static function afficherVue(string $cheminVue, array $parametres = []): Response
    {
        extract($parametres);
//        $messagesFlash = $_REQUEST["messagesFlash"] ?? [];
        $messagesFlash = MessageFlash::lireTousMessages();
        ob_start();
        require __DIR__ . "/../vue/$cheminVue";
        $corpsReponse = ob_get_clean();
        return new Response($corpsReponse);
    }

    // https://stackoverflow.com/questions/768431/how-do-i-make-a-redirect-in-php
    protected static function redirection(string $route, array $parametres = []): RedirectResponse
    {
        /** @var UrlGenerator $generateurUrl */
        $generateurUrl = Conteneur::recupererService("generateurUrl");
        $url = $generateurUrl->generate($route, $parametres);

        return new RedirectResponse($url);
    }

    public static function afficherErreur($messageErreur = "", $statusCode = 400): Response
    {
        $reponse = ControleurGenerique::afficherTwig( "erreur.html.twig",[
            "messageErreur" => $messageErreur
        ]);

        $reponse->setStatusCode($statusCode);
        return $reponse;
    }

    public static function issetAndNotNull(array $requestParams) : bool {
        foreach ($requestParams as $param) {
            if(!(isset($_REQUEST[$param]) && $_REQUEST[$param] != null)) {
                return false;
            }
        }
        return true;
    }

    protected static function afficherTwig(string $cheminVue, array $parametres = []): Response
    {
        /** @var Environment $twig */
        $twig = Conteneur::recupererService("twig");
        $corpsReponse = $twig->render($cheminVue, $parametres);
        return new Response($corpsReponse);
    }
}