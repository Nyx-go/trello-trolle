<?php

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\Conteneur;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Routing\Generator\UrlGenerator;

/** @var UrlGenerator $generateurUrl */
$generateurUrl = Conteneur::recupererService("generateurUrl");
/** @var UrlHelper $assistantUrl */
$assistantUrl = Conteneur::recupererService("assistantUrl");

?>

<div><p>Bienvenue sur <strong>Trello-Trollé</strong>!</p></div>
<div>
    <?php if (ConnexionUtilisateur::estConnecte()) { ?>
        <span><a href="<?=$generateurUrl->generate('afficherListeMesTableaux')?>">Consulter mes tableaux</a></span>
    <?php } else { ?>
        <p>
            Pour créer des tableaux, commencez par vous
            <a href="<?=$generateurUrl->generate('afficherFormulaireConnexion')?>">connecter</a>
            ou par <a href="<?=$generateurUrl->generate('afficherFormulaireCreation')?>">créer un
                compte</a>!
        </p>
    <?php } ?>
</div>
