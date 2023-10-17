<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    #[Route('/cart', name: 'cart_index')]
    public function index(): Response
    {
        return $this->render('cart/index.html.twig', [
            'cartCocktails' => $this->cartService->getFullCart(),
            'total' => $this->cartService->getTotal()
        ]);
    }

    #[Route('/cart/add/{cocktail}', name: 'cart_add', methods: 'POST')]
    public function cartAdd(Request $request, CartService $cartService, int $cocktail): RedirectResponse
    {
        $cartService->add($cocktail);
        return $this->redirectBack($request);
    }

    #[Route('/cart/remove/{cocktail}', name: 'cart_remove', methods: 'POST')]
    public function cartRemove(Request $request, CartService $cartService, int $cocktail): RedirectResponse
    {
        $cartService->remove($cocktail);
        return $this->redirectBack($request);
    }

    #[Route('/cart/removeAll', name: 'cart_remove_all', methods: 'POST')]
    public function cartRemoveAll(Request $request, CartService $cartService): RedirectResponse
    {
        $cartService->clear();
        return $this->redirectToRoute('cocktail_index');
    }

    /**
     * Redirect back
     * @param Request $request
     * @return RedirectResponse
     */
    private function redirectBack(Request $request): RedirectResponse
    {
        // Get the previous url
        $referer = $request->headers->get('referer');
        // Get the previous url
        $route = $request->attributes->get("_route");
        // Get the previous url params
        $routeParams = $request->attributes->get("_route_params", []);

        // If the HTTP_REFERER founded
        if($referer) return $this->redirect($referer);
        // Else if the route name founded
        if($route) return $this->redirectToRoute($route, $routeParams);

        // else go to home page
        return $this->redirectToRoute('app_home');
    }
}
