<?php

namespace App\Trellotrolle\Modele\Repository;

use PDO;

interface ConnexionBaseDeDonneesInterface
{
    public function getPdo(): PDO;
}