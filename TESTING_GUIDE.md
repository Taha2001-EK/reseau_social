# Guide de Tests pour le Projet Réseau Social

Ce guide vous accompagnera étape par étape pour mettre en place et exécuter les tests de votre application Symfony avec MongoDB.

## Prérequis

Assurez-vous que les dépendances de test sont installées :

```bash
composer require --dev phpunit/phpunit symfony/test-pack
```

## Configuration de l'environnement de test

1.  **Base de données de test** : Configurez votre fichier `.env.test.local` (créez-le s'il n'existe pas) pour utiliser une base de données MongoDB dédiée aux tests.

    ```dotenv
    # .env.test.local
    MONGODB_URL=mongodb://localhost:27017
    MONGODB_DB=symfony_test
    ```

2.  **Nettoyage de la base** : Comme MongoDB n'a pas de transactions "rollback" comme SQL pour chaque test, il est conseillé de nettoyer les collections entre chaque test.

## Étape 1 : Tests Unitaires (Documents)

Ces tests vérifient que vos classes `User`, `Post` et `Comment` se comportent comme prévu (getters, setters, logique interne).

**Fichier :** `tests/Document/UserTest.php`

```php
<?php

namespace App\Tests\Document;

use App\Document\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserAttributes()
    {
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('test@example.com');
        $user->setPassword('hashedpassword');

        $this->assertEquals('testuser', $user->getUsername());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('hashedpassword', $user->getPassword());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }
}
```

*À faire :* Créez des tests similaires pour `Post` et `Comment`.

## Étape 2 : Tests Fonctionnels (Contrôleurs & Pages)

Ces tests simulent un navigateur et vérifient que les pages s'affichent correctement et que les formulaires fonctionnent.

### 2.1 Test de la Page d'Accueil

**Fichier :** `tests/Controller/PostControllerTest.php`

```php
<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Post index');
    }
}
```

### 2.2 Test d'Inscription et Connexion

**Fichier :** `tests/Controller/SecurityControllerTest.php`

```php
<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testRegisterAndLogin()
    {
        $client = static::createClient();

        // 1. Inscription
        $crawler = $client->request('GET', '/register');
        $buttonCrawlerNode = $crawler->selectButton('Register');
        $form = $buttonCrawlerNode->form([
            'registration_form[email]' => 'newuser@example.com',
            'registration_form[username]' => 'newuser',
            'registration_form[plainPassword]' => 'password123',
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/login');

        // 2. Connexion
        $client->followRedirect();
        $crawler = $client->request('GET', '/login');
        $buttonCrawlerNode = $crawler->selectButton('Sign in');
        $form = $buttonCrawlerNode->form([
            'email' => 'newuser@example.com',
            'password' => 'password123',
        ]);
        $client->submit($form);
        
        // Vérifier qu'on est redirigé vers l'accueil ou le profil
        $this->assertResponseRedirects('/'); 
    }
}
```

## Étape 3 : Tests de Création de Post (Authentifié)

Vérifiez qu'un utilisateur connecté peut créer un post, mais qu'un visiteur anonyme est redirigé vers le login.

**Fichier :** `tests/Controller/PostCrudTest.php`

```php
<?php

namespace App\Tests\Controller;

use App\Document\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostCrudTest extends WebTestCase
{
    public function testCreatePostRestricted()
    {
        $client = static::createClient();
        $client->request('GET', '/posts/create');
        
        // Doit rediriger vers le login
        $this->assertResponseRedirects('/login');
    }

    public function testCreatePostAsUser()
    {
        $client = static::createClient();
        
        // Simulation de connexion (nécessite d'avoir un user en base ou un mock)
        // Une méthode courante est de créer un utilisateur de test dans la base au début du test
        // Puis d'utiliser $client->loginUser($user);
        
        // Exemple simplifié si vous avez un Repository
        /*
        $userRepo = static::getContainer()->get('doctrine_mongodb')->getRepository(User::class);
        $user = $userRepo->findOneBy(['email' => 'newuser@example.com']);
        $client->loginUser($user);
        */

        // Une fois connecté :
        /*
        $crawler = $client->request('GET', '/posts/create');
        $this->assertResponseIsSuccessful();
        
        $form = $crawler->selectButton('Save')->form([
            'post[title]' => 'Mon Titre',
            'post[content]' => 'Mon contenu super intéressant.'
        ]);
        $client->submit($form);
        
        $this->assertResponseRedirects('/');
        */
    }
}
```

## Étape 4 : Tests de Sécurité (Permissions)

Vérifiez qu'un utilisateur ne peut pas modifier le post d'un autre.

1.  Connectez-vous en tant que `User A`.
2.  Créez un post (id: 1).
3.  Connectez-vous en tant que `User B`.
4.  Essayez d'accéder à `/posts/1/edit`.
5.  Vérifiez que la réponse est un code 403 (Access Denied).

## Lancer les tests

Pour exécuter tous vos tests :

```bash
php bin/phpunit
```

Pour un fichier spécifique :

```bash
php bin/phpunit tests/Controller/PostControllerTest.php
```

## Conseils pour le débogage

*   Si un test échoue, regardez le fichier `var/log/test.log` pour plus de détails.
*   Utilisez `dump()` et `die()` dans votre code ou vos tests pour inspecter les variables (nécessite le composant `var-dumper`).
*   Si vous avez des erreurs de base de données, assurez-vous que votre serveur MongoDB tourne et est accessible via l'URL configurée.
