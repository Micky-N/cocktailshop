<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Service\CartService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{

    public function __construct()
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    }

    #[Route('/checkout/create-session-stripe', name: 'checkout_session')]
    public function checkout(
        CartService            $cartService,
        UrlGeneratorInterface  $urlGenerator,
        EntityManagerInterface $entityManager
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->getProfile()->getBillingAddress()) {
            return $this->redirectToRoute('app_profile');
        }
        $cardItems = $cartService->getFullCart();
        if (!$cardItems) {
            return $this->redirectToRoute('cocktail_index');
        }

        $cocktailsStripe = [];
        foreach ($cardItems as $cardItem) {
            $cocktail = $cardItem['cocktail'];
            $cocktailsStripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int)($cocktail->getPrice() * 100),
                    'product_data' => [
                        'name' => $cocktail->getName(),
                        'metadata' => [
                            'cocktail_id' => $cocktail->getId()
                        ]
                    ]
                ],
                'quantity' => $cardItem['quantity']
            ];
        }

        /** @var User $user */
        $user = $this->getUser();
        $order = new Order();
        $order->setAmount(0);
        $order->setCustomer($user->getProfile());
        $order->setCustomerName($user->fullName());
        $order->setStripeStatus('unpaid');
        $order->setCreatedAt(new DateTimeImmutable('now'));
        $order->setStatus(Order::PENDING_PAYMENT);
        $entityManager->persist($order);
        $entityManager->flush();

        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => [[$cocktailsStripe]],
            'mode' => 'payment',
            'success_url' => $urlGenerator->generate('app_order_complete', ['order' => $order->getId()], UrlGenerator::ABSOLUTE_URL),
            'cancel_url' => $urlGenerator->generate('checkout_cancel', ['order' => $order->getId()], UrlGenerator::ABSOLUTE_URL),
        ]);


        $order->setAmount($checkout_session->amount_total);
        $order->setStripeSessionId($checkout_session->id);
        $entityManager->persist($order);
        $entityManager->flush();

        return new RedirectResponse($checkout_session->url);
    }

    #[Route('/checkout/success/{order}', name: 'checkout_success')]
    public function store(Order $order)
    {
        return $this->render('checkout/success.html.twig', [
            'order' => $order
        ]);
    }

    #[Route('/checkout/cancel/{order}', name: 'checkout_cancel')]
    public function cancel(EntityManagerInterface $entityManager, Order $order)
    {
        $session = Session::retrieve($order->getStripeSessionId());

        $order->setStripeStatus($session->payment_status);
        $order->setStatus(Order::FAILED_PAYMENT);

        $entityManager->persist($order);
        $entityManager->flush();

        return $this->redirectToRoute('cart_index');
    }
}
