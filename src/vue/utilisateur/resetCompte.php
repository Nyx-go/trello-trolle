<div>
    <form method="post" action="controleurFrontal.php">
        <fieldset>
            <h3>Récupérer mon compte</h3>
            <p class="InputAddOn">
                <label class="InputAddOn-item" for="email_id">Email du compte&#42;</label>
                <input class="InputAddOn-field" type="email" value="" placeholder="rlebreton@yopmail.com" name="email" id="email_id" required>
            </p>
            <input type='hidden' name='action' value='recupererCompte'>
            <input type='hidden' name='controleur' value='utilisateur'>
            <p>
                <input class="InputAddOn-field" type="submit" value="Récupérer mon compte"/>
            </p>
        </fieldset>
    </form>
</div>
