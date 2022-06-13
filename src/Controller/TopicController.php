<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\Topic;
use App\Form\EditTopicType;
use App\Form\NewPostType;
use App\Form\NewTopicType;
use App\Repository\PostRepository;
use App\Repository\TopicRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TopicController extends AbstractController
{
    #[Route('/topic/{id}', name: 'topic')]
    public function index(Request $request, ManagerRegistry $managerRegistry, Topic $topic, PostRepository $postRepository): Response
    {
        $topic->increaseView();
        
        $entityManager = $managerRegistry->getManager();
        $entityManager->flush();
        
        $currentPage = $request->query->get('page', 1);

        $posts = $postRepository->findByTopic($topic->getId(), $currentPage, 6);
        
        return $this->render('topic/index.html.twig', [
            'topic' => $topic,
            'posts' => $posts,
            'currentPage' => $currentPage
        ]);
    }
    
    #[Route('/topic/{id}/create', name: 'new_topic')]
    public function create(TopicRepository $topicRepository, Request $request, Category $category): Response
    {
        $topic = new Topic();
        $topic->setAuthor($this->getUser());
        $topic->setCategory($category);
        
        $form = $this->createForm(NewTopicType::class, $topic);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $topicRepository->add($topic, true);
            
            return $this->redirectToRoute('topic', [
                'id' => $topic->getId()
            ]);
        }
        
        return $this->render('topic/create.html.twig', [
            'category' => $category,
            'form' => $form->createView()
        ]);
    }

    #[Route('/topic/{id}/edit', name: 'edit_topic')]
    public function edit(Topic $topic, Request $request, TopicRepository $topicRepository): Response
    {
        $form = $this->createForm(EditTopicType::class, $topic);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $topicRepository->add($topic, true);

            return $this->redirectToRoute('topic', [
                'id' => $topic->getId()
            ]);
        }

        return $this->render('topic/edit.html.twig', [
            'topic' => $topic,
            'form' => $form->createView()
        ]);
    }
    
    #[Route('/topic/{id}/delete', name: 'delete_topic')]
    public function delete(Topic $topic, TopicRepository $topicRepository): Response
    {
        $categoryId = $topic->getCategory()->getId();
        
        if($this->getUser() == $topic->getAuthor() && $topic->getPosts()->count() <= 0) {
            $topicRepository->remove($topic, true);

            $lastPage = ceil(($topic->getCategory()->getTopics()->count() - 1) / 10);

            return $this->redirectToRoute('category', [
                'id' => $categoryId,
                'page' => $lastPage
            ]);
        }
        
        return $this->redirectToRoute('category', [
            'id' => $categoryId
        ]);
    }
}
