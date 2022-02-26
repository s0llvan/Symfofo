<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\TopicRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends AbstractController
{
    /**
     * @Route("/category/{id}", name="category")
     */
    public function index(Request $request, TopicRepository $topicRepository, Category $category, PaginatorInterface $paginatorInterface): Response
    {
		$topics = $paginatorInterface->paginate(
            $category->getTopics(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('category.html.twig', [
            'category' => $category,
			'topics' => $topics
        ]);
    }
}
