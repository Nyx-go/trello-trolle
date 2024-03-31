<?php

use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Lib\Conteneur;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Routing\Generator\UrlGenerator;

/** @var UrlGenerator $generateurUrl */
$generateurUrl = Conteneur::recupererService("generateurUrl");
/** @var UrlHelper $assistantUrl */
$assistantUrl = Conteneur::recupererService("assistantUrl");

/** @var Utilisateur $utilisateur */

$loginHTML = htmlspecialchars($utilisateur->getLogin());
?>
<div>
    <form method="post" action="<?=$generateurUrl->generate('mettreAJourMdp')?>">
        <fieldset>
            <h3>Mise à jour du mot de passe</h3>
            <p >
                <label  for="login_id">Login&#42;</label>
                <input  type="text" value="<?= $loginHTML ?>" minlength="3" maxlength="30" placeholder="Ex : rlebreton" name="login" id="login_id" readonly>
            </p>
            <p >
                <label  for="mdpAncien_id">Ancien mot de passe&#42;</label>
                <input  type="password" value="" placeholder="" name="mdpAncien" id="mdpAncien_id" required>
            </p>
            <p >
                <label  for="mdp_id">Nouveau mot de passe&#42;</label>
                <label  for="mdp_id"><strong>6 à 50 caractères, au moins une minusle, une majuscule et un caractère spécial</strong></label>
                <input  type="password" value="" placeholder="" minlength="6" maxlength="50" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*_=+\-]).{6,50}" name="mdp" id="mdp_id" required>
            </p>
            <p >
                <label  for="mdp2_id">Vérification du nouveau mot de passe&#42;</label>
                <input  type="password" value="" placeholder="" minlength="6" maxlength="50" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*_=+\-]).{6,50}" name="mdp2" id="mdp2_id" required>
            </p>
            <input type='hidden' name='login' value='<?= $loginHTML ?>'>
            <input type='hidden' name='action' value='mettreAJourMdp'>
            <input type='hidden' name='controleur' value='utilisateur'>
            <p>
                <input type="submit" value="Mettre à jour Mdp"/>
            </p>
        </fieldset>
    </form>
</div>