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
use Knp\Component\Pager\PaginatorInterface;

class TopicController extends AbstractController
{
    #[Route('/topic/{id}', name: 'topic')]
    public function index(Request $request, ManagerRegistry $managerRegistry, PaginatorInterface $paginatorInterface, Topic $topic): Response
    {
        $topic->increaseView();
        
        $entityManager = $managerRegistry->getManager();
        $entityManager->flush();
        
        $posts = $paginatorInterface->paginate(
            $topic->getPosts(),
            $request->query->getInt('page', 1),
            6
        );
        
        return $this->render('topic/index.html.twig', [
            'topic' => $topic,
            'posts' => $posts
        ]);
    }
    
    #[Route('/topic/{id}/create', name: 'new_topic')]
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
        
        return $this->render('topic/create.html.twig', [
            'category' => $category,
            'form' => $form->createView()
        ]);
    }
    
    #[Route('/topic/{id}/delete', name: 'delete_topic')]
    public function delete(Topic $topic, ManagerRegistry $managerRegistry): Response
    {
        $categoryId = $topic->getCategory()->getId();
        
        if($this->getUser() == $topic->getAuthor()) {
            $entityManager = $managerRegistry->getManager();
            $entityManager->remove($topic);
            $entityManager->flush();
        }
        
        return $this->redirectToRoute('category', [
            'id' => $categoryId
        ]);
    }
}
