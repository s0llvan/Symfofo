<?php

namespace App\Controller;

use Gregwar\Captcha\CaptchaBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
		$captcha = $this->setCaptcha($request);

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
			'last_username' => $lastUsername,
			'error' => $error,
			'captcha' => $captcha
		]);
    }
    
    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

	public function setCaptcha(Request $request): CaptchaBuilder
	{
		$captcha = new CaptchaBuilder();
		$captcha->build();
		$request->getSession()->set('phrase', $captcha->getPhrase());
		
		return $captcha;
	}
}
