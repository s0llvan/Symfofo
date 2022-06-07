<?php

namespace App\Controller;

use App\Form\AccountType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'account')]
    public function index(Request $request, ManagerRegistry $managerRegistry, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(AccountType::class, $this->getUser());
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

        return $this->render('account/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
