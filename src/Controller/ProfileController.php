<?php

namespace App\Controller;

use App\Form\ProfileType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    public function index(Request $request, ManagerRegistry $managerRegistry, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(ProfileType::class, $this->getUser());
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $plaintextPassword = $form->get('new_password')->getData();
            
            if($plaintextPassword) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $this->getUser(),
                    $plaintextPassword
                );
                $this->getUser()->setPassword($hashedPassword);

                $this->addFlash('success', 'Your password have been successfully updated !');
            }
            
            $entityManager = $managerRegistry->getManager();
            $entityManager->flush();
        }
        
        return $this->render('profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
