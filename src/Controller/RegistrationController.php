<?php

namespace App\Controller;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, DocumentManager $dm): Response
    {
        // 1. Initialisation de l'utilisateur vide
        $user = new User();

        // 2. Création du formulaire (Builder pattern)
        $form = $this->createFormBuilder($user)
            ->add('username', TextType::class, ['label' => 'Pseudo'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('password', PasswordType::class, ['label' => 'Mot de passe'])
            ->add('save', SubmitType::class, ['label' => 'Créer mon compte', 'attr' => ['class' => 'btn btn-primary']])
            ->getForm();

        // 3. Traitement de la requête HTTP
        $form->handleRequest($request);

        // 4. Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            
            // Hachage du mot de passe (Sécurité obligatoire)
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);

            // Persistance dans MongoDB
            $dm->persist($user);
            $dm->flush();

            // Message flash pour l'UX
            $this->addFlash('success', 'Compte créé avec succès ! Connectez-vous.');

            // Redirection vers le login (cette page n'existe pas encore, ça fera une erreur 404 temporaire, c'est normal)
            return $this->redirectToRoute('app_login'); 
        }

        return $this->render('registration/index.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}