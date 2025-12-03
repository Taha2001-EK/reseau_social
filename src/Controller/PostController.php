<?php

namespace App\Controller;

use App\Document\Post;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostController extends AbstractController
{
    // 1. LISTE (Accueil)
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(DocumentManager $dm): Response
    {
        $posts = $dm->getRepository(Post::class)->findBy([], ['createdAt' => 'DESC']);
        return $this->render('post/index.html.twig', ['posts' => $posts]);
    }

    // 2. CRÉATION
    #[Route('/posts/create', name: 'post_create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, DocumentManager $dm): Response
    {
        $post = new Post();
        $form = $this->createFormBuilder($post)
            ->add('title', TextType::class, ['label' => 'Titre'])
            ->add('content', TextareaType::class, ['label' => 'Contenu'])
            ->add('save', SubmitType::class, ['label' => 'Publier', 'attr' => ['class' => 'btn btn-success']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser());
            $dm->persist($post);
            $dm->flush();

            $this->addFlash('success', 'Post publié !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('post/create.html.twig', ['form' => $form->createView()]);
    }

    // 3. DÉTAIL
    #[Route('/posts/{id}', name: 'post_show', methods: ['GET'])]
    public function show(string $id, DocumentManager $dm): Response
    {
        $post = $dm->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Post introuvable');
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    // 4. ÉDITION
    // Ajout de PUT pour conformité stricte
    #[Route('/posts/{id}/edit', name: 'post_edit', methods: ['GET', 'POST', 'PUT'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, string $id, DocumentManager $dm): Response
    {
        $post = $dm->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Post introuvable');
        }

        if ($post->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas l\'auteur de ce post.');
        }

        $form = $this->createFormBuilder($post)
            ->add('title', TextType::class, ['label' => 'Titre'])
            ->add('content', TextareaType::class, ['label' => 'Contenu'])
            ->add('save', SubmitType::class, ['label' => 'Mettre à jour', 'attr' => ['class' => 'btn btn-warning']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dm->flush();
            $this->addFlash('success', 'Post modifié avec succès !');
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        return $this->render('post/edit.html.twig', ['form' => $form->createView()]);
    }

    // 5. SUPPRESSION
    // Ajout de DELETE pour conformité stricte
    #[Route('/posts/{id}/delete', name: 'post_delete', methods: ['POST', 'DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, string $id, DocumentManager $dm): Response
    {
        $post = $dm->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Post introuvable');
        }

        if ($post->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Interdit.');
        }

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $dm->remove($post);
            $dm->flush();
            $this->addFlash('success', 'Post supprimé.');
        }

        return $this->redirectToRoute('app_home');
    }
}