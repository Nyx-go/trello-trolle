<?php

namespace App\Trellotrolle\Test;

use App\Trellotrolle\Modele\Repository\TableauRepository;
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
        $this->tableauService = new TableauService($this->tableauRepositoryMock);
    }

    public function testSupprimerTableauInexistant() {
        $this->tableauRepositoryMock->method("recupererParClePrimaire")->willReturn(null);

        $this->tableauService->supprimerTableau(1);
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