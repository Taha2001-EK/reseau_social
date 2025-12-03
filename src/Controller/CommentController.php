<?php

namespace App\Controller;

use App\Document\Comment;
use App\Document\Post;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comments')] // Ce préfixe reste pour la suppression, mais on va l'écraser pour la création
class CommentController extends AbstractController
{
    // CORRECTION ICI : On met un "/" au début pour écraser le préfixe de classe et avoir l'URL exacte du sujet
    #[Route('/posts/{postId}/comments', name: 'comment_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, string $postId, DocumentManager $dm): Response
    {
        $post = $dm->getRepository(Post::class)->find($postId);

        if (!$post) {
            throw $this->createNotFoundException('Post introuvable');
        }

        // On récupère le contenu envoyé directement (pour faire simple sans classe FormType dédiée pour l'instant)
        $content = $request->request->get('content');

        if (!empty($content)) {
            $comment = new Comment();
            $comment->setContent($content);
            $comment->setAuthor($this->getUser()); // [cite: 55]
            $comment->setPost($post); // On lie le commentaire au post

            $dm->persist($comment);
            $dm->flush();

            $this->addFlash('success', 'Commentaire ajouté !');
        } else {
            $this->addFlash('error', 'Le commentaire ne peut pas être vide.');
        }

        // On retourne sur la page du post
        return $this->redirectToRoute('post_show', ['id' => $postId]);
    }

    // 2. SUPPRIMER UN COMMENTAIRE
    #[Route('/{id}', name: 'comment_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, string $id, DocumentManager $dm): Response
    {
        $comment = $dm->getRepository(Comment::class)->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Commentaire introuvable');
        }

        // Seul l'auteur peut supprimer 
        if ($comment->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce commentaire.');
        }

        // Token CSRF pour la sécurité
        if ($this->isCsrfTokenValid('delete-comment'.$comment->getId(), $request->request->get('_token'))) {
            $postId = $comment->getPost()->getId();
            $dm->remove($comment);
            $dm->flush();
            $this->addFlash('success', 'Commentaire supprimé.');
            
            return $this->redirectToRoute('post_show', ['id' => $postId]);
        }

        return $this->redirectToRoute('app_home');
    }
}