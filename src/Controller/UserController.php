<?php

namespace App\Controller;

use App\Document\Post;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    // Consigne : "Afficher le profil d'un utilisateur spécifique"
    // Consigne : "Cette page doit être protégée"
    #[Route('/user/{id}', name: 'user_profile', methods: ['GET'])]
    #[IsGranted('ROLE_USER')] // Protection : il faut être connecté
    public function profile(string $id, DocumentManager $dm): Response
    {
        // 1. On récupère l'utilisateur demandé
        $user = $dm->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable');
        }

        // 2. Bonus (non demandé mais logique) : On récupère les posts de cet utilisateur
        $posts = $dm->getRepository(Post::class)->findBy(
            ['author.id' => $id], 
            ['createdAt' => 'DESC']
        );

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'posts' => $posts
        ]);
    }
}