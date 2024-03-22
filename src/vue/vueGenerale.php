<?php /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\Conteneur;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Routing\Generator\UrlGenerator;

/** @var string $pagetitle */
/** @var UrlGenerator $generateurUrl */
$generateurUrl = Conteneur::recupererService("generateurUrl");
/** @var UrlHelper $assistantUrl */
$assistantUrl = Conteneur::recupererService("assistantUrl");


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $pagetitle ?></title>
    <link rel="stylesheet" href="<?=$assistantUrl->getAbsoluteUrl('../ressources/css/navstyle.css')?>">
    <link rel="stylesheet" href="<?=$assistantUrl->getAbsoluteUrl('../ressources/css/trellostyle.css')?>">

</head>
<body>
<header>
    <nav>
        <ul>
            <li>
                <a href="controleurFrontal.php?action=accueil&controleur=base">Accueil</a>
            </li>
            <?php
            if (!ConnexionUtilisateur::estConnecte()) {
                ?>
                <li>
                    <a href="controleurFrontal.php?action=afficherFormulaireConnexion&controleur=utilisateur">
                       Connexion <img alt="login" src="../ressources/img/enter.png" >
                    </a>
                </li>
                <li>
                    <a href="controleurFrontal.php?action=afficherFormulaireCreation&controleur=utilisateur">
                        Inscription <img alt="S'inscrire" title="S'inscrire" src="../ressources/img/add-user.png" >
                    </a>
                </li>
                <?php
            } else {
                $loginHTML = htmlspecialchars(ConnexionUtilisateur::getLoginUtilisateurConnecte());
                $loginURL = rawurlencode(ConnexionUtilisateur::getLoginUtilisateurConnecte());
                ?>
                <li>
                    <a href="controleurFrontal.php?action=afficherListeMesTableaux&controleur=tableau">Mes tableaux</a>
                </li>
                <li>
                    <a href="controleurFrontal.php?action=afficherDetail&controleur=utilisateur&login=<?= rawurlencode($loginURL) ?>">
                       Mon compte (<span><?= $loginURL ?></span>) <img alt="logout" src="../ressources/img/user.png">
                    </a>
                </li>
                <li>
                    <a href="controleurFrontal.php?action=deconnecter&controleur=utilisateur">
                       Déconnexion <img alt="logout" src="../ressources/img/logout.png">
                    </a>
                </li>
            <?php } ?>
        </ul>
    </nav>
    <div>
        <?php
        /** @var string[][] $messagesFlash */
        foreach ($messagesFlash as $type => $messagesFlashPourUnType) {
            // $type est l'une des valeurs suivantes : "success", "info", "warning", "danger"
            // $messagesFlashPourUnType est la liste des messages flash d'un type
            foreach ($messagesFlashPourUnType as $messageFlash) {
                echo <<< HTML
                    <div class="alert alert-$type">
                       $messageFlash
                    </div>
                    HTML;
            }
        }
        ?>
    </div>
</header>
<main>
    <?php
    /**
     * @var string $cheminVueBody
     */
    require __DIR__ . "/{$cheminVueBody}";
    ?>
</main>

<footer>
    <p>
        Copyright Trello-Trollé Company
    </p>
</footer>
</body>
</html>