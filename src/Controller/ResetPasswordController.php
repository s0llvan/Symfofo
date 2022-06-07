<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ResetPasswordType;
use App\Form\ResetPasswordNewType;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordController extends AbstractController
{
	private $mailer;
	
	public function __construct(MailerInterface $mailer)
	{
		$this->mailer = $mailer;
	}

    #[Route('/reset-password', name: 'reset_password')]
	public function index(Request $request, UserRepository $userRepository, ManagerRegistry $managerRegistry): Response
	{
		$form = $this->createForm(ResetPasswordType::class);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			
			$user = $userRepository->findOneBy([
				'username' => $form->get('username')->getData(),
				'email' => $form->get('email')->getData()
			]);
			
			if ($user) {
				
				$dateNow = new \DateTime();
				$dateDiff = $dateNow->getTimestamp();
				
				if ($user->getPasswordResetTokenLast()) {
					$dateDiff = $dateNow->getTimestamp() - $user->getPasswordResetTokenLast()->getTimestamp();
					
					// Hours
					$dateDiff = $dateDiff / 60 / 60;
				}
				
				if ($dateDiff > 24) {
					
					$token = bin2hex(random_bytes(32));
					$user->setPasswordResetToken($token);
					$user->setPasswordResetTokenLast($dateNow);
					
					$entityManager = $managerRegistry->getManager();
					$entityManager->flush();
					
					$confirmationLink = $this->generateUrl('reset_password_confirmation', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                    $message = (new TemplatedEmail())
					->subject('Symfofo - Reset password')
					->from('no-reply@symfofo.fr')
					->to($user->getEmail())
					->htmlTemplate('emails/registration.html.twig')
					->context([
						'user' => $user,
						'confirmation_link' => $confirmationLink
						]
					);
					$this->mailer->send($message);
					
					$this->addFlash('success', 'Please click on the link sent by email to reset your password');
				} else {
					$this->addFlash('error', 'You have already reset your password, please wait 24 hours');
				}
			} else {
				$this->addFlash('error', 'User not found');
			}
		}
		
		return $this->render('reset_password/index.html.twig', [
			'form' => $form->createView()
		]);
	}
	
    #[Route('/reset-password/{token}', name: 'reset_password_confirmation')]
	public function registerConfirmationAction(Request $request, string $token, UserRepository $userRepository, UserPasswordHasherInterface $passwordEncoder, ManagerRegistry $managerRegistry): Response
	{
		if (!$user = $userRepository->findOneBy([
			'passwordResetToken' => $token
			])) {
				return $this->redirectToRoute('index');
			}
						
			$form = $this->createForm(ResetPasswordNewType::class);
			$form->get('username')->setData($user->getUsername());
			
			$form->handleRequest($request);
			
			if ($form->isSubmitted() && $form->isValid()) {
				
				$user->setPasswordResetToken(null);
				$user->setPasswordResetTokenLast(null);
				
				$formPassword = $form->get('password')->getData();
				
				$password = $passwordEncoder->hashPassword($user, $formPassword);
				$user->setPassword($password);
				
				$this->addFlash('success', 'You have successfully changed your password, now you can log-in');
			}
			
			$entityManager = $managerRegistry->getManager();
			$entityManager->flush();
			
			return $this->render('reset_password/new_password.html.twig', [
				'form' => $form->createView()
			]);
		}
	}