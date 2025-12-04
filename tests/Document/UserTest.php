<?php

namespace App\Tests\Document;

use App\Document\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserAttributes()
    {
        // On crée un utilisateur vide
        $user = new User();
        
        // On remplit ses infos
        $user->setUsername('testuser');
        $user->setEmail('test@example.com');
        $user->setPassword('superSecretPassword');

        // On vérifie que les infos sont bien enregistrées (Getters)
        $this->assertEquals('testuser', $user->getUsername());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('superSecretPassword', $user->getPassword());
        
        // Vérifie qu'il a bien le rôle par défaut
        // Note: Assure-toi que ta classe User initialise bien $roles = [] ou ['ROLE_USER']
        // Si ce test échoue, on saura qu'il faut corriger ton Entité User.
    }
}