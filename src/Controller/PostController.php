<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Topic;
use App\Form\EditPostType;
use App\Form\NewPostType;
use App\Repository\PostRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/topic/{id}/new-post', name: 'new_post')]
    public function create(Topic $topic, Request $request, PostRepository $postRepository): Response
    {
        $post = new Post();
        $post->setTopic($topic);
        $post->setAuthor($this->getUser());
        
        $form = $this->createForm(NewPostType::class, $post);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $postRepository->add($post, true);
            
            $lastPage = ceil(($topic->getPosts()->count() + 1) / 6);
            
            return $this->redirectToRoute('topic', [
                'id' => $topic->getId(),
                'page' => $lastPage,
                '_fragment' => 'post_' . $post->getId()
            ]);
        }
        
        return $this->render('post/create.html.twig', [
            'topic' => $topic,
            'form' => $form->createView()
        ]);
    }
    
    #[Route('/post/{id}/edit', name: 'edit_post')]
    public function edit(Post $post, Request $request, PostRepository $postRepository): Response
    {
        $form = $this->createForm(EditPostType::class, $post);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $postRepository->add($post, true);

            $lastPage = ceil(($post->getTopic()->getPosts()->count() + 1) / 6);

            return $this->redirectToRoute('topic', [
                'id' => $post->getTopic()->getId(),
                'page' => $lastPage,
                '_fragment' => 'post_' . $post->getId()
            ]);
        }
        
        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView()
        ]);
    }
}