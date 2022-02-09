<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
	use TargetPathTrait;
	
	public const LOGIN_ROUTE = 'login';
	
	private UrlGeneratorInterface $urlGenerator;
	private EntityManagerInterface $entityManager;
	
	public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $entityManager)
	{
		$this->urlGenerator = $urlGenerator;
		$this->entityManager = $entityManager;
	}
	
	public function authenticate(Request $request): Passport
	{
		$username = $request->request->get('username', '');
		$phrase = $request->getSession()->get('phrase');
		$captcha = $request->request->get('captcha', '');

		if ($phrase != $captcha) {
			throw new UserMessageAuthenticationException('Wrong captcha');
		}

		$user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
		if (!$user) {
			throw new UserMessageAuthenticationException("User not found");
		}

		if(!$user->getEmailConfirmed()) {
			throw new UserMessageAuthenticationException("Email not confirmed");
		}
		
		$request->getSession()->set(Security::LAST_USERNAME, $username);
		
		return new Passport(
			new UserBadge($username),
			new PasswordCredentials($request->request->get('password', '')),
			[
				new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
				]
			);
		}
		
		public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
		{
			if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
				return new RedirectResponse($targetPath);
			}
			
			return new RedirectResponse($this->urlGenerator->generate('index'));
		}
		
		protected function getLoginUrl(Request $request): string
		{
			return $this->urlGenerator->generate(self::LOGIN_ROUTE);
		}
	}
	