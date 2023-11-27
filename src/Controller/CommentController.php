<?php

namespace App\Controller;

use App\Entity\Cocktail;
use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    #[Route('/comment', name: 'app_comment')]
    public function index(): Response
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    #[Route('/cocktail/{slug}/comment/add', name: 'comment_store')]
    public function add(Cocktail $cocktail, Request $request, EntityManagerInterface $manager)
    {
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);
        if($commentForm->isSubmitted() && $commentForm->isValid()){
            $comment->setCocktail($cocktail);
            $comment->setUser($this->getUser());
            $manager->persist($comment);
            $manager->flush();
        }
        return $this->redirectToRoute('cocktail_show', ['slug' => $cocktail->getSlug()]);
    }
}
