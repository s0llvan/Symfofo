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
        $form = $this->createForm(ProfileType::class, $this->getUser());
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            if($form->get('delete_profile_picture')->getData() && !$this->getUser()->getProfilePictureFilename()) {
                $filepath = $this->getParameter("profile_picture_directory");
                $fileUploader->delete($filepath . '/' . $this->getUser()->getProfilePictureFilename(), false);
                $this->getUser()->setProfilePictureFilename(null);
            }
            
            $profilePictureFile = $form->get('profile_picture')->getData();
            if($profilePictureFile) {
                $profilePictureFilename = $fileUploader->upload($profilePictureFile);
                if($profilePictureFilename) {
                    $this->getUser()->setProfilePictureFilename($profilePictureFilename);
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
