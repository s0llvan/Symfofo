<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserAdminType;
use App\Form\UserCreateAdminType;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminUserController extends AbstractController
{
	private $managerRegistry;
	
	public function __construct(ManagerRegistry $managerRegistry)
	{
		$this->managerRegistry = $managerRegistry;
	}
	
    #[Route('/admin/users', name: 'admin_user')]
	public function index(Request $request, UserRepository $userRepository): Response
	{
        $currentPage = $request->query->get('page', 1);

		$users = $userRepository->findUsers($currentPage);

		return $this->render('admin/user/index.html.twig', [
			'users' => $users,
            'currentPage' => $currentPage
		]);
	}
	
    #[Route('/admin/users/{id}', name: 'admin_user_edit', requirements: ['id' => '\d+'])]
	public function edit(Request $request, User $user): Response
	{
		$form = $this->createForm(UserAdminType::class, $user, [
			'role' => $this->isGranted('ROLE_SUPER_ADMIN')
			|| ($user->getRole()->getSlug() == 'ROLE_MOD' && $this->isGranted('ROLE_ADMIN'))
			|| ($user->getRole()->getSlug() == 'ROLE_USER' && $this->isGranted('ROLE_MOD'))
		]);
		$form->handleRequest($request);
		
		// Check if form is submitted and valid
		if ($form->isSubmitted() && $form->isValid()) {
			
			$this->managerRegistry->getManager()->flush();
			
			$this->addFlash('success', 'Informations saved');
		}
		
		return $this->render('admin/user/edit.html.twig', [
			'form' => $form->createView(),
			'user' => $user
		]);
	}
	
    #[Route('/admin/users/create', name: 'admin_user_create')]
	public function create(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface, UserRepository $userRepository): Response
	{
		$user = new User();
		
		$form = $this->createForm(UserCreateAdminType::class, $user);
		$form->handleRequest($request);
		
		// Check if form is submitted and valid
		if ($form->isSubmitted() && $form->isValid()) {
			
			$user->setPassword($userPasswordHasherInterface->hashPassword($user, $form->get('password')->getData()));
			$user->setEmailConfirmed(true);

            $userRepository->add($user, true);
			
			$this->addFlash('success', 'Informations saved');
		}
		
		return $this->render('admin/user/create.html.twig', [
			'form' => $form->createView()
		]);
	}
	
    #[Route('/admin/users/{id}/delete', name: 'admin_user_delete')]
	public function delete(User $user, UserRepository $userRepository): Response
	{
		$this->managerRegistry->getManager()->remove($user);
		$this->managerRegistry->getManager()->flush();

        $userRepository->remove($user, true);
		
		return $this->redirectToRoute('admin_user');
	}
}
