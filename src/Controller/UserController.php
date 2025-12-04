<?php

namespace App\Controller;

use App\Document\Comment;
use App\Document\Post;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/user/{id}', name: 'app_user_show')]
    #[IsGranted('ROLE_USER')]
    public function show(User $user, DocumentManager $dm): Response
    {
        $posts = $dm->getRepository(Post::class)->findBy(['author.id' => $user->getId()], ['createdAt' => 'DESC']);
        $comments = $dm->getRepository(Comment::class)->findBy(['author.id' => $user->getId()]);

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'posts' => $posts,
            'comments' => $comments,
        ]);
    }
}
