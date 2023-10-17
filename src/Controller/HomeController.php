<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(CartService $cartService): Response
    {
        return $this->render('home/index.html.twig', [
            'cartCocktails' => $cartService->getFullCart()
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(CartService $cartService): Response
    {
        return $this->render('home/contact.html.twig', [
            'cartCocktails' => $cartService->getFullCart()
        ]);
    }
}
