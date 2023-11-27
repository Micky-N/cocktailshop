<?php

namespace App\Service;

use App\Entity\Cocktail;
use App\Repository\CocktailRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private SessionInterface $session;

    /**
     * Get the current session
     *
     * @param RequestStack $requestStack
     * @param CocktailRepository $cocktailRepository
     */
    public function __construct(RequestStack $requestStack, private readonly CocktailRepository $cocktailRepository)
    {
        $this->session = $requestStack->getSession();
    }


    /**
     * Add a cocktail to the cart
     *
     * @param int $cocktailId
     * @return void
     */
    public function add(int $cocktailId): void
    {
        // Get the cart in session
        $cart = $this->session->get('cart', []);

        // If empty so add a new entry with 1 quantity
        // or add 1 quantity
        if (empty($cart[$cocktailId])) {
            $cart[$cocktailId] = 1;
        } else {
            $cart[$cocktailId]++;
        }

        // Save the edited cart to session
        $this->session->set('cart', $cart);
    }

    /**
     * Get cart from session
     *
     * @return array<int, int>
     */
    public function get(): array
    {
        return $this->session->get('cart', []);
    }

    public function count(): int
    {
        $cartArray = $this->get();
        return array_sum($cartArray);
    }

    /**
     * Delete cocktail from cart
     *
     * @param int $cocktailId
     * @return void
     */
    public function delete(int $cocktailId): void
    {
        $cart = $this->session->get('cart', []);

        // If cocktail is in the cart so delete it
        if (isset($cart[$cocktailId])) {
            unset($cart[$cocktailId]);
        }

        // Save the edited cart to session
        $this->session->set('cart', $cart);
    }

    /**
     * Delete cart from session
     * @return mixed
     */
    public function clear(): mixed
    {
        return $this->session->remove('cart');
    }

    /**
     * Remove a cocktail to the cart
     *
     * @param int $cocktailId
     * @return void
     */
    public function remove(int $cocktailId): void
    {
        $cart = $this->session->get('cart', []);

        // If more than 1 element remains, remove 1 quantity
        // or remove the cocktail from the cart
        if ($cart[$cocktailId] > 1) {
            $cart[$cocktailId]--;
        } else {
            unset($cart[$cocktailId]);
        }

        // Save the edited cart to session
        $this->session->set('cart', $cart);
    }

    /**
     * Get total price of the cart
     *
     * @return float|int
     */
    public function getTotal(): float|int
    {
        $cart = $this->get();
        $total = 0;
        foreach ($cart as $cocktailId => $quantity) {
            /**
             * Get the cocktail entity
             * @var Cocktail|null $cocktail
             */
            $cocktail = $this->cocktailRepository->find($cocktailId);
            // Remove from cart if entity not exists
            if (!$cocktail) {
                $this->remove($cocktailId);
                continue;
            }
            // Addition total of price * quantity of each item in the cart
            $total += $quantity * $cocktail->getPrice();
        }
        return $total;
    }

    /**
     * Get all cart cocktail with quantity
     * @return array<array{cocktail: Cocktail, quantity: float|int}>
     */
    public function getFullCart(): array
    {
        $cart = $this->get();
        $cartData = [];
        foreach ($cart as $cocktailId => $quantity) {
            /**
             * Get the cocktail entity
             * @var Cocktail|null $cocktail
             */
            $cocktail = $this->cocktailRepository->find($cocktailId);

            // Remove from cart if entity not exists
            if (!$cocktail) {
                $this->remove($cocktailId);
                continue;
            }

            // Add cocktail with quantity
            $cartData[] = [
                'cocktail' => $cocktail,
                'quantity' => $quantity
            ];
        }
        return $cartData;
    }
}