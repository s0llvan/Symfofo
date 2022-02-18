<?php

namespace App\Controller;

use App\Entity\Topic;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
