<?php

namespace App\Controller;

use App\Form\ProfileType;
use App\Service\FileUploader;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    public function index(Request $request, ManagerRegistry $managerRegistry, FileUploader $fileUploader): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            if($form->get('delete_profile_picture')->getData() && !$user->getProfilePictureFilename()) {
                $filepath = $this->getParameter("profile_picture_directory");
                $fileUploader->delete($filepath . '/' . $user->getProfilePictureFilename(), false);
                $user->setProfilePictureFilename(null);
            }
            
            $profilePictureFile = $form->get('profile_picture')->getData();
            if($profilePictureFile) {
                $profilePictureFilename = $fileUploader->upload($profilePictureFile);
                if($profilePictureFilename) {
                    $user->setProfilePictureFilename($profilePictureFilename);
                } else {
                    $this->addFlash('error', 'An error was occured, can\'t upload your picture');
                }
            }
            
            $entityManager = $managerRegistry->getManager();
            $entityManager->flush();
        }
        
        return $this->render('profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
