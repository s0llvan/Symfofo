<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Topic;
use App\Form\NewTopicType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TopicController extends AbstractController
{
	/**
	* @Route("/topic/{id}", name="topic")
	*/
	public function index(Topic $topic): Response
	{
		return $this->render('topic.html.twig', [
			'topic' => $topic
		]);
	}
	
	/**
	* @Route("/topic/{id}/create", name="new_topic")
	*/
	public function create(ManagerRegistry $managerRegistry, Request $request, Category $category): Response
	{
		$topic = new Topic();
		$topic->setAuthor($this->getUser());
		$topic->setCategory($category);

		$form = $this->createForm(NewTopicType::class, $topic);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			
			$entityManager = $managerRegistry->getManager();
			$entityManager->persist($topic);
			$entityManager->flush();

			return $this->redirectToRoute('topic', [
				'id' => $topic->getId()
			]);
		}

		return $this->render('new_topic.html.twig', [
			'category' => $category,
			'form' => $form->createView()
		]);
	}
}
