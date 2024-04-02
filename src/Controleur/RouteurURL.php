<?php
namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\AttributeRouteControllerLoader;
use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\Conteneur;
use App\Trellotrolle\Lib\MessageFlash;
use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class RouteurURL
{
    /**
     * @throws Exception
     */
    public static function traiterRequete(): void
    {


        $conteneur = new ContainerBuilder();

        //On indique au FileLocator de chercher à partir du dossier de configuration
        $loader = new YamlFileLoader($conteneur, new FileLocator(__DIR__."/../Configuration"));
        //On remplit le conteneur avec les données fournies dans le fichier de configuration
        $loader->load("conteneur.yml");


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

        $twigLoader = new FilesystemLoader(__DIR__ . '/../vue/');
        $twig = new Environment(
            $twigLoader,
            [
                'autoescape' => 'html',
                'strict_variables' => true
            ]
        );

        $twig->addFunction(new TwigFunction("route",$generateurUrl->generate(...)));
        $twig->addFunction(new TwigFunction("asset",$assistantUrl->getAbsoluteUrl(...)));
        $twig->addGlobal('loginUser', ConnexionUtilisateur::getLoginUtilisateurConnecte());
        $twig->addGlobal('estConnecte', ConnexionUtilisateur::estConnecte());
        $twig->addGlobal('messagesFlash', new MessageFlash());

        Conteneur::ajouterService("twig", $twig);

        try {
            $associateurUrl = new UrlMatcher($routes, $contexteRequete);
            $donneesRoute = $associateurUrl->match($requete->getPathInfo());
            $requete->attributes->add($donneesRoute);
            /** Throws:
             * NoConfigurationException If no routing configuration could be found
             * ResourceNotFoundException If the resource could not be found
             * MethodNotAllowedException If the resource was found but the request method is not allowed
             */

            $resolveurDeControleur = new ContainerControllerResolver($conteneur);
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
            $reponse = (new ControleurGenerique)->afficherErreur($exception->getMessage(), 404);
        } catch (MethodNotAllowedException $exception) {
            $reponse = (new ControleurGenerique)->afficherErreur($exception->getMessage(), 405);
        } catch (Exception $exception) {
            $reponse = (new ControleurGenerique)->afficherErreur($exception->getMessage()) ;
        }
        $reponse->send();
    }
}