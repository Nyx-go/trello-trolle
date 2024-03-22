<?php
namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\AttributeRouteControllerLoader;
use App\Trellotrolle\Lib\Conteneur;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteurURL
{
    public static function traiterRequete(): void
    {
        $requete = Request::createFromGlobals();

        $fileLocator = new FileLocator(__DIR__);
        $attrClassLoader = new AttributeRouteControllerLoader();
        $routes = (new AttributeDirectoryLoader($fileLocator, $attrClassLoader))->load(__DIR__);

        // Création de la requête
        $contexteRequete = (new RequestContext())->fromRequest($requete);

        $generateurUrl = new UrlGenerator($routes, $contexteRequete);
        $assistantUrl = new UrlHelper(new RequestStack(), $contexteRequete);

        Conteneur::ajouterService("generateurUrl", $generateurUrl);
        Conteneur::ajouterService("assistantUrl", $assistantUrl);
//
//        $twigLoader = new FilesystemLoader(__DIR__ . '/../vue/');
//        $twig = new Environment(
//            $twigLoader,
//            [
//                'autoescape' => 'html',
//                'strict_variables' => true
//            ]
//        );
//
//        $callableRoute = $generateurUrl->generate(...);
//        $twig->addFunction(new TwigFunction("route", $callableRoute));
//
//        $callableAsset = $assistantUrl->getAbsoluteUrl(...);
//        $twig->addFunction(new TwigFunction("asset", $callableAsset));
//
//        $twig->addGlobal('userId', ConnexionUtilisateur::getIdUtilisateurConnecte());
//        $twig->addGlobal('userEmail', ConnexionUtilisateur::getIdUtilisateurConnecte());
//        $twig->addGlobal('messagesFlash', new MessageFlash());
//
//        Conteneur::ajouterService("twig", $twig);

        try {
            $associateurUrl = new UrlMatcher($routes, $contexteRequete);
            $donneesRoute = $associateurUrl->match($requete->getPathInfo());
            $requete->attributes->add($donneesRoute);
            /** Throws:
             * NoConfigurationException If no routing configuration could be found
             * ResourceNotFoundException If the resource could not be found
             * MethodNotAllowedException If the resource was found but the request method is not allowed
             */

            $resolveurDeControleur = new ControllerResolver();
            $controleur = $resolveurDeControleur->getController($requete);
            /** Throws:
             * LogicException If a controller was found based on the request, but it is not callable
             */

            $resolveurDArguments = new ArgumentResolver();
            $arguments = $resolveurDArguments->getArguments($requete, $controleur);
            /** Throws:
             * RuntimeException When no value could be provided for a required argument
             */

            $reponse = call_user_func_array($controleur, $arguments);
        } catch (ResourceNotFoundException $exception) {
            $reponse = ControleurGenerique::afficherErreur($exception->getMessage(), 404);
        } catch (MethodNotAllowedException $exception) {
            $reponse = ControleurGenerique::afficherErreur($exception->getMessage(), 405);
        } catch (\Exception $exception) {
            $reponse = ControleurGenerique::afficherErreur($exception->getMessage()) ;
        }
        $reponse->send();
    }
}