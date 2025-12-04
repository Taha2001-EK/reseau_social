<?php

namespace App\Tests\Controller;

use App\Document\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostCrudTest extends WebTestCase
{
    public function testCreatePostRestricted()
    {
        $client = static::createClient();
        
        // CORRECTION 1 : L'URL est /posts/create
        $client->request('GET', '/posts/create');

        // On doit être redirigé vers le login car on n'est pas connecté
        $this->assertResponseRedirects('/login');
    }

    public function testCreatePostAsUser()
    {
        $client = static::createClient();
        
        // 1. On crée un utilisateur fictif
        $container = static::getContainer();
        $dm = $container->get('doctrine_mongodb')->getManager();

        $user = new User();
        $user->setEmail('author_' . uniqid() . '@test.com');
        $user->setUsername('author_' . uniqid());
        $user->setPassword('password');
        $user->setRoles(['ROLE_USER']);

        $dm->persist($user);
        $dm->flush();

        // 2. Connexion magique
        $client->loginUser($user);

        // 3. On va sur la page (CORRECTION 1 : URL /posts/create)
        $crawler = $client->request('GET', '/posts/create');
        $this->assertResponseIsSuccessful();

        // 4. On remplit le formulaire
        // NOTE : Si ça plante ici, vérifie que ton bouton dans _form.html.twig s'appelle bien "Save"
        $client->submitForm('Save', [
            'post[title]' => 'Mon super article de test',
            'post[content]' => 'Ceci est le contenu de mon article généré par test.',
        ]);

        // 5. Vérification de la redirection (CORRECTION 2 : Redirection vers /)
        $this->assertResponseRedirects('/');
    }
}