<?php

namespace App\Trellotrolle\Test;

use App\Trellotrolle\Modele\Repository\AffecteRepository;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\ParticipeRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Service\Exception\ServiceException;
use PHPUnit\Framework\TestCase;
use App\Trellotrolle\Service\TableauService;

class TableauServiceTest extends TestCase {

    private $tableauService;
    private $tableauRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tableauRepositoryMock = $this->createMock(TableauRepository::class);
        $this->utilisateurRepositoryMock = $this->createMock(UtilisateurRepository::class);
        $this->colonneRepositoryMock = $this->createMock(ColonneRepository::class);
        $this->carteRepositoryMock = $this->createMock(CarteRepository::class);
        $this->affecteRepositoryMock = $this->createMock(AffecteRepository::class);
        $this->participeRepositoryMock = $this->createMock(ParticipeRepository::class);

        $this->tableauService = new TableauService(
            $this->tableauRepositoryMock,
            $this->utilisateurRepositoryMock,
            $this->colonneRepositoryMock,
            $this->carteRepositoryMock,
            $this->affecteRepositoryMock,
            $this->participeRepositoryMock
        );
    }

    public function testSupprimerTableauInexistant() {
        $this->tableauRepositoryMock->method("recupererParCodeTableau")->willReturn(null);

        $this->expectException(ServiceException::class);

        $this->tableauService->supprimerTableau("codeTableauInexistant");
    }

    /*
    public function testSupprimerTableauIdTableauManquant() {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("TIdentifiant de tableau manquant");

        $this->tableauService->supprimerTableau(null);
    }

    public function testSupprimerTableauUtilisateurNonProprietaire() {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Vous n'êtes pas propriétaire de ce tableau");

        $this->tableauService->supprimerTableau(1);
    }
    */
}