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
$prenomHTML = htmlspecialchars($utilisateur->getPrenom());
$nomHTML = htmlspecialchars($utilisateur->getNom());
$emailHTML = htmlspecialchars($utilisateur->getEmail());
?>
<div>
    <form method="post" action="<?=$generateurUrl->generate('mettreAJour')?>">
        <fieldset>
            <h3>Mise à jour du profil</h3>
            <p >
                <label  for="login_id">Login&#42;</label>
                <input  type="text" value="<?= $loginHTML ?>" minlength="3" maxlength="30" placeholder="Ex : rlebreton" name="login" id="login_id" readonly>
            </p>
            <p >
                <label  for="prenom_id">Prenom&#42;</label>
                <input  type="text" value="<?= $prenomHTML ?>" minlength="1" maxlength="30" placeholder="Ex : Romain" name="prenom" id="prenom_id" required>
            </p>
            <p >
                <label  for="nom_id">Nom&#42;</label>
                <input  type="text" value="<?= $nomHTML ?>" minlength="1" maxlength="30"  placeholder="Ex : Lebreton" name="nom" id="nom_id" required>
            </p>
            <p >
                <label  for="email_id">Email&#42;</label>
                <input  type="email" value="<?= $emailHTML ?>" maxlength="255" placeholder="rlebreton@yopmail.com" name="email" id="email_id" required>
            </p>
            <p >
                <label  for="mdpAncien_id">Ancien mot de passe&#42;</label>
                <input  type="password" value="" placeholder="" name="mdpAncien" id="mdpAncien_id" required>
            </p>
            <a href="<?=$generateurUrl->generate('afficherFormulaireMiseAJourMdp')?>">Modifier votre mot de passe</a>
            <input type='hidden' name='login' value='<?= $loginHTML ?>'>
            <input type='hidden' name='action' value='mettreAJour'>
            <input type='hidden' name='controleur' value='utilisateur'>
            <p>
                <input type="submit" value="Mettre à jour"/>
            </p>
        </fieldset>
    </form>
</div>