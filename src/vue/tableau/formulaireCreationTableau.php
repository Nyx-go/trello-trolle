<div>
    <form method="post" action="controleurFrontal.php">
        <fieldset>
            <h3>Création d'un tableau</h3>
            <p>
                <label for="nomColonne">Nom du tableau&#42;</label>
                <input type="text" placeholder="Mon super tableau" name="nomTableau" id="nomTableau" minlength="3" maxlength="50" required>
            </p>
            <input type='hidden' name='action' value='creerTableau'>
            <input type='hidden' name='controleur' value='tableau'>
            <p>
                <input type="submit" value="Créer le tableau">
            </p>
        </fieldset>
    </form>
</div>