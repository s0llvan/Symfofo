<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;

class IndexController extends AbstractController
{
	private $mailer;
	
	public function __construct(MailerInterface $mailer)
	{
		$this->mailer = $mailer;
	}
	
	/**
	* @Route("/", name="index")
	*/
	public function index(CategoryRepository $categoryRepository): Response
	{
		/* $email = (new Email())
		->from('no-reply@example.com') 
		->to('user@example.com') 
		->subject('I love Me')
		->html('<h1>Lorem ipsum</h1> <p>...</p>');
		
		try {
			$this->mailer->send($email);
		} catch (TransportExceptionInterface $e) {
			dump($e);
		} */
		
		return $this->render('index.html.twig', [
			'categories' => $categoryRepository->findBy(['parent' => null]),
		]);
	}
}
