<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostControllerTest extends WebTestCase
{
    public function testIndex()
    {
        // On crée un client (un faux navigateur)
        $client = static::createClient();
        
        // On demande la page d'accueil ('/')
        $crawler = $client->request('GET', '/');

        // On vérifie que le serveur répond "200 OK" (Succès)
        $this->assertResponseIsSuccessful();
        
        // On vérifie qu'il y a un titre <h1> contenant "Post index"
        // (Si ton titre est différent sur ta page d'accueil, change "Post index" par ton vrai titre !)
        $this->assertSelectorTextContains('h1', 'Post index');
    }
}