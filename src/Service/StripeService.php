<?php

namespace App\Service;

use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeService
{
    private $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
        Stripe::setApiKey($secretKey);
    }

    public function createPaymentIntent(int $amount, string $currency = 'usd'): PaymentIntent
    {
        return PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currency,
        ]);
    }
}
