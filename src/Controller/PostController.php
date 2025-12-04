<?php

namespace App\Controller;

use App\Document\Comment;
use App\Document\Post;
use App\Document\User;
use App\Form\CommentType;
use App\Form\PostType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostController extends AbstractController
{
    #[Route('/', name: 'app_post_index', methods: ['GET'])]
    public function index(DocumentManager $dm): Response
    {
        $posts = $dm->getRepository(Post::class)->findAll();

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/posts/create', name: 'app_post_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, DocumentManager $dm): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $post->setAuthor($user);
            
            $dm->persist($post);
            $dm->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/posts/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment, [
            'action' => $this->generateUrl('app_comment_new', ['postId' => $post->getId()]),
        ]);

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'comment_form' => $commentForm->createView(),
        ]);
    }

    #[Route('/posts/{postId}/comments', name: 'app_comment_new', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addComment(Request $request, string $postId, DocumentManager $dm): Response
    {
        $post = $dm->getRepository(Post::class)->find($postId);
        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $comment->setAuthor($user);
            $comment->setPost($post);
            
            // Also add to post collection if needed for inverse side consistency in memory, 
            // though standard persistence handles it via referencing.
            // But since Post has @ReferenceMany(mappedBy='post'), we don't persist Post, we persist Comment.
            
            $dm->persist($comment);
            $dm->flush();

            return $this->redirectToRoute('app_post_show', ['id' => $postId]);
        }
        
        // If form invalid, we render show again (simplified)
        // Ideally we would forward or re-render show with errors.
        // For simplicity, redirect back to show.
        return $this->redirectToRoute('app_post_show', ['id' => $postId]);
    }

    #[Route('/comments/{id}', name: 'app_comment_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deleteComment(Request $request, Comment $comment, DocumentManager $dm): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($comment->getAuthor() !== $user) {
            throw $this->createAccessDeniedException('You are not allowed to delete this comment.');
        }

        if ($this->isCsrfTokenValid('delete_comment'.$comment->getId(), $request->request->get('_token'))) {
            $dm->remove($comment);
            $dm->flush();
        }

        return $this->redirectToRoute('app_post_show', ['id' => $comment->getPost()->getId()]);
    }

    #[Route('/posts/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Post $post, DocumentManager $dm): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($post->getAuthor() !== $user) {
            throw $this->createAccessDeniedException('You are not allowed to edit this post.');
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dm->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/posts/{id}', name: 'app_post_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Post $post, DocumentManager $dm): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($post->getAuthor() !== $user) {
            throw $this->createAccessDeniedException('You are not allowed to delete this post.');
        }

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $dm->remove($post);
            $dm->flush();
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
