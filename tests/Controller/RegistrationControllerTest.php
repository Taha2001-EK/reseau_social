<?php

namespace App\Tests\Controller;

use App\Tests\MongoWebTestCase; // On utilise notre classe de test spéciale Mongo

class RegistrationControllerTest extends MongoWebTestCase
{
    public function testRegisterPageWorks(): void
    {
        $client = static::createClient();
        
        // CORRECTION : La route est /register (et non /registration)
        $crawler = $client->request('GET', '/register');

        // On vérifie que la page charge bien (Code 200)
        $this->assertResponseIsSuccessful();
        
        // On vérifie qu'on est bien sur la page d'inscription (Vérifie le titre H1)
        $this->assertSelectorTextContains('h1', 'Rejoindre le réseau');
    }
}