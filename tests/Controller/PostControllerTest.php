<?php

namespace App\Tests\Controller;

use App\Document\User;
use App\Tests\MongoWebTestCase;
use Doctrine\ODM\MongoDB\DocumentManager;

class PostControllerTest extends MongoWebTestCase
{
    public function testHomePageIsPublic(): void
    {
        $client = static::createClient();
        // On demande la racine du site
        $crawler = $client->request('GET', '/');

        // Si tu as une 404 ici, c'est que PostController.php n'a pas la route #[Route('/', name: 'app_home')]
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Fil d\'actualité');
    }

    public function testCreatePostRequiresLogin(): void
    {
        $client = static::createClient();
        // On essaie d'aller sur la création sans être connecté
        $client->request('GET', '/posts/create');

        // On doit être redirigé vers le login
        $this->assertResponseRedirects('/login');
    }

    public function testCreatePostPageAsUser(): void
    {
        $client = static::createClient();
        
        // Astuce : On récupère le conteneur VIA le client pour éviter les conflits
        $container = $client->getContainer();
        $dm = $container->get(DocumentManager::class);

        // 1. Création de l'utilisateur
        $user = new User();
        $user->setUsername('Tester');
        $user->setEmail('test@test.com');
        $user->setPassword('$2y$13$AgF.hF...hash...'); // Mot de passe bidon
        
        $dm->persist($user);
        $dm->flush();

        // 2. Connexion
        $client->loginUser($user);

        // 3. Accès à la page
        $client->request('GET', '/posts/create');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un nouveau post');
    }
}