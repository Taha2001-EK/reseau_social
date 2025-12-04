<?php

namespace App\Tests\Controller;

use App\Document\Post;
use App\Document\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostSecurityTest extends WebTestCase
{
    public function testEditOtherUserPostRestricted()
    {
        $client = static::createClient();
        
        // On récupère le gestionnaire de base de données
        $container = static::getContainer();
        $dm = $container->get('doctrine_mongodb')->getManager();

        // 1. Créer l'utilisateur A (Le Propriétaire honnête)
        $userA = new User();
        $userA->setEmail('owner_' . uniqid() . '@test.com');
        $userA->setUsername('owner_' . uniqid());
        $userA->setPassword('password');
        $userA->setRoles(['ROLE_USER']);
        $dm->persist($userA);

        // 2. Créer l'utilisateur B (Le "Hacker" ou curieux)
        $userB = new User();
        $userB->setEmail('hacker_' . uniqid() . '@test.com');
        $userB->setUsername('hacker_' . uniqid());
        $userB->setPassword('password');
        $userB->setRoles(['ROLE_USER']);
        $dm->persist($userB);

        // 3. Créer un post appartenant à A
        $post = new Post();
        $post->setTitle('Touche pas à mon post');
        $post->setContent('Ceci est un contenu privé.');
        $post->setAuthor($userA); // C'est A l'auteur
        $dm->persist($post);
        
        $dm->flush(); // On sauvegarde tout en base

        // 4. On connecte l'utilisateur B
        $client->loginUser($userB);

        // 5. B essaie d'aller sur la page d'édition du post de A
        // L'URL dans ton contrôleur est : /posts/{id}/edit
        $client->request('GET', '/posts/' . $post->getId() . '/edit');

        // 6. LE VERDICT : On doit recevoir une erreur 403 (Access Denied)
        $this->assertResponseStatusCodeSame(403);
    }
}