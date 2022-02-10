<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserAdminType;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminUserController extends AbstractController
{
	private $managerRegistry;

	public function __construct(ManagerRegistry $managerRegistry)
	{
		$this->managerRegistry = $managerRegistry;
	}

    /**
     * @Route("/admin/users", name="admin_user")
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

	/**
	 * @Route("/admin/users/{id}", name="admin_user_edit")
	 */
	public function edit(Request $request, User $user): Response
	{
		$form = $this->createForm(UserAdminType::class, $user, [
			'super_admin' => $this->isGranted('ROLE_SUPER_ADMIN')
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

	/**
	 * @Route("/admin/users/{id}/delete", name="admin_user_delete")
	 */
	public function delete(User $user): Response
	{
		$this->managerRegistry->getManager()->remove($user);
		$this->managerRegistry->getManager()->flush();

		return $this->redirectToRoute('admin_user');
	}
}
