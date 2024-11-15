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
<div>
    <form method="post" action="<?=$generateurUrl->generate('creerDepuisFormulaire')?>">
        <fieldset>
            <h3>Inscription</h3>
            <p >
                <label  for="login_id">Login&#42;</label>
                <input  type="text" value="" placeholder="Ex : rlebreton" name="login"
                       id="login_id" minlength="3" maxlength="30" required>
            </p>
            <p >
                <label  for="prenom_id">Prenom&#42;</label>
                <input  type="text" value="" placeholder="Ex : Romain" name="prenom"
                       id="prenom_id" minlength="1" maxlength="30" required>
            </p>
            <p >
                <label  for="nom_id">Nom&#42;</label>
                <input  type="text" value="" placeholder="Ex : Lebreton" name="nom" id="nom_id"
                        minlength="1" maxlength="30" required>
            </p>
            <p >
                <label  for="email_id">Email&#42;</label>
                <input  type="email" maxlength="255" value="" placeholder="rlebreton@yopmail.com" name="email" id="email_id" required>
            </p>
            <p >
                <label  for="mdp_id">Mot de passe&#42;</label>
                <label  for="mdp_id"><strong>6 à 50 caractères, au moins une minusle, une majuscule et un caractère spécial</strong></label>
                <input  type="password" minlength="6" maxlength="50" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*_=+\-]).{6,50}" value="" placeholder="" name="mdp" id="mdp_id" required>
            </p>
            <p >
                <label  for="mdp2_id">Vérification du mot de passe&#42;</label>
                <input  type="password" minlength="6" maxlength="50" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*_=+\-]).{6,50}" value="" placeholder="" name="mdp2" id="mdp2_id"
                       required>
            </p>
            <input type='hidden' name='action' value='creerDepuisFormulaire'>
            <input type='hidden' name='controleur' value='utilisateur'>
            <p>
                <input type="submit" value="S'inscrire"/>
            </p>
        </fieldset>
    </form>
</div>