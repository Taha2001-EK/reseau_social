<?php

namespace App\Tests;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MongoWebTestCase extends WebTestCase
{
    // On nettoie la base de données AVANT chaque test, c'est plus stable
    protected function setUp(): void
    {
        parent::setUp();

        // On démarre le kernel juste pour récupérer le service de base de données
        self::bootKernel();
        $dm = self::getContainer()->get(DocumentManager::class);
        
        // On vide les collections
        $schemaManager = $dm->getSchemaManager();
        $schemaManager->dropCollections();
        
        // On éteint le kernel pour laisser le test le redémarrer proprement
        self::ensureKernelShutdown();
    }
}