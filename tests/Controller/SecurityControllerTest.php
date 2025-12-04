<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    // Cette fonction nettoie la base de données après chaque test
    // pour éviter l'erreur "Cet email est déjà utilisé" si tu relances le test.
    protected function tearDown(): void
    {
        parent::tearDown();
        // Note: Idéalement on vide la collection MongoDB ici, 
        // mais pour l'instant on va utiliser des données uniques.
    }

    public function testRegisterAndLogin()
    {
        $client = static::createClient();

        // 1. Inscription
        // On génère un email unique pour ne pas planter si on relance le test
        $uniqueId = uniqid(); 
        $email = "newuser_$uniqueId@example.com";
        $username = "user_$uniqueId";

        $crawler = $client->request('GET', '/register');
        
        // Vérifie qu'on est bien sur la page (Code 200)
        $this->assertResponseIsSuccessful();

        // Remplissage du formulaire
        // (Adapte le nom du bouton 'Register' si c'est 'S'inscrire' sur ton site)
        $client->submitForm('Register', [
            'registration_form[email]' => $email,
            'registration_form[username]' => $username,
            'registration_form[plainPassword]' => 'password123',
            // 'registration_form[agreeTerms]' => true, // Décommente si tu as une case "CGU"
        ]);

        // On doit être redirigé vers le login après succès
        $this->assertResponseRedirects('/login');

        // 2. Connexion
        $client->followRedirect(); // On suit la redirection vers /login
        
        // On remplit le formulaire de login
        // (Adapte le nom du bouton 'Sign in' ou 'Se connecter')
        $client->submitForm('Sign in', [
            '_username' => $email,       // Remplace 'email' par '_username'
            '_password' => 'password123', // Remplace 'password' par '_password'
        ]);
        
        // Vérifier qu'on est redirigé vers l'accueil (ou ailleurs selon ta config)
        // Cela prouve qu'on est connecté !
        $this->assertResponseRedirects('/'); 
    }
}