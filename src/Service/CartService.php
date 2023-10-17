<?php

namespace App\Service;

use App\Entity\Cocktail;
use App\Repository\CocktailRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private SessionInterface $session;

    public function __construct(RequestStack $requestStack, private readonly CocktailRepository $cocktailRepository)
    {
        $this->session = $requestStack->getSession();
    }


    public function add(int $id): void
    {
        $cart = $this->session->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }
        $this->session->set('cart',$cart);
    }

    public function remove(int $id): void
    {
        $cart = $this->session->get('cart', []);

        if ($cart[$id] > 1) {
            $cart[$id]--;
        } else {
            unset($cart[$id]);
        }
        $this->session->set('cart',$cart);
    }

    public function delete(int $id): void
    {
        $cart = $this->session->get('cart', []);

        if(isset($cart[$id])){
            unset($cart[$id]);
        }

        $this->session->set('cart', $cart);
    }

    /**
     * @return array<int, int>
     */
    public function get(): array
    {
        return $this->session->get('cart', []);
    }

    /**
     * @return null
     */
    public function clear(): null
    {
        return $this->session->remove('cart');
    }

    public function getTotal(): float|int
    {
        $cart = $this->get();
        $total = 0;
        foreach ($cart as $cocktailId => $quantity){
            $cocktail = $this->cocktailRepository->find($cocktailId);
            if(!$cocktail){
                $this->remove($cocktailId);
                continue;
            }
            $total += $quantity * $cocktail->getPrice();
        }
        return $total;
    }

    /**
     * @return array<array{cocktail: Cocktail, quantity: float|int}>
     */
    public function getFullCart(): array
    {
        $cart = $this->get();
        $cartData = [];
        foreach ($cart as $cocktailId => $quantity){
            $cocktail = $this->cocktailRepository->find($cocktailId);
            if(!$cocktail){
                $this->remove($cocktailId);
                continue;
            }
            $cartData[] = [
                'cocktail' => $cocktail,
                'quantity' => $quantity
            ];
        }
        return $cartData;
    }
}