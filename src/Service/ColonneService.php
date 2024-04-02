<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;

class ColonneService implements ColonneServiceInterface
{

    //TODO : N'oublie pas de mettre les fonctions dans l'interface
    public function __construct(
        private ColonneRepositoryInterface $colonneRepository,
        private TableauRepositoryInterface $tableauRepository
    )
    {
    }

}