<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Events;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Repository\UserRepository;
use App\Repository\RoleRepository;
use App\Entity\Role;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
	/**
	* @Route("/register", name="registration")
	*/
	public function registerAction(Request $request, UserPasswordHasherInterface $passwordEncoder, EventDispatcherInterface $eventDispatcher, MailerInterface $mailer, RoleRepository $roleRepository, ManagerRegistry $managerRegistry): Response
	{
		$entityManager = $managerRegistry->getManager();
		
		$user = new User();
		$form = $this->createForm(UserType::class, $user);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			
			$token = bin2hex(random_bytes(32));
			
			$user->setEmailConfirmationToken($token);
			
			$password = $passwordEncoder->hashPassword($user, $user->getPassword());
			$user->setPassword($password);
			
			$slug = 'ROLE_USER';
			
			$role = $roleRepository->findOneBySlug($slug);
			
			if (!$role) {
				$role = new Role();
				$role->setName('User');
				$role->setSlug('ROLE_USER');
				
				$entityManager->persist($role);
			}
			
			$user->setRole($role);
			
			$entityManager->persist($user);
			
			$confirmationLink = $this->generateUrl('registration_confirmation', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
			
			$message = (new TemplatedEmail())
			->from('no-reply@symfofo.fr')
			->to($user->getEmail())
			->htmlTemplate('emails/registration.html.twig')
			->context([
				'user' => $user,
				'confirmation_link' => $confirmationLink
				]
			);
			
			try {
				$mailer->send($message);
				
				$event = new GenericEvent($user);
				$eventDispatcher->dispatch($event, Events::USER_REGISTERED);
				
				$this->addFlash('success', 'Please click on the link sent by email to confirm your account');
				
			} catch (TransportExceptionInterface $e) {
				
				$entityManager->remove($user);
				
				$this->addFlash('error', 'Email confirmation cannot be send');
			}
			
			$entityManager->flush();
			
			return $this->redirectToRoute('registration');
		}
		
		return $this->render('security/registration.html.twig', [
			'form' => $form->createView()
		]);
	}
	
	/**
	* @Route("/register-confirmation/{token}", name="registration_confirmation")
	*/
	public function registerConfirmationAction(Request $request, $token, UserRepository $userRepository, ManagerRegistry $managerRegistry): Response
	{
		if ($user = $userRepository->findOneBy(['emailConfirmationToken' => $token])) {
			
			$user->setEmailConfirmationToken(null);
			$user->setEmailConfirmed(true);
			
			$entityManager = $managerRegistry->getManager();
			$entityManager->flush();
			
			$this->addFlash('success', 'Registration completed, you can now log in !');
		} else {
			$this->addFlash('error', 'Wrong token !');
		}
		
		return $this->redirectToRoute('login');
	}
}