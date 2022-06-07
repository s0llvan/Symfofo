<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\TopicRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends AbstractController
{
    #[Route('/category/{id}', name: 'category')]
    public function index(Request $request, Category $category, TopicRepository $topicRepository, PaginatorInterface $paginatorInterface): Response
    {
        $currentPage = $request->query->get('page', 1);

		$topics = $topicRepository->findByCategory($category->getId(), $currentPage);
        
        return $this->render('category.html.twig', [
            'category' => $category,
			'topics' => $topics,
            'currentPage' => $currentPage
        ]);
    }
}
