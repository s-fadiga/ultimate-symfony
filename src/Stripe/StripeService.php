<?php

namespace App\Stripe;

use App\Entity\Purchase;

class StripeService
{
    protected $secretKey;
    protected $publicKey;
    public function __construct(string $secretKey, string $publicKey) {
        $this->secretKey = $secretKey;
        $this->publicKey = $publicKey;

    }
    public function getPublicKey(): string {
        return $this->publicKey;
    }
    public function getPaymentIntent(Purchase $purchase) {

        \Stripe\Stripe::setApiKey('sk_test_51N0YOrBK7JH5KHInBuTtSgl5yYKyxgP2SwttMLoRFH9vKMFGKx3nBTxkw4tJW6sQoLbamcbd2j5898j8DHO7kgFc00di5tyT50');
        
        return \Stripe\PaymentIntent::create([
            'amount' => $purchase->getTotal(),
            'currency' => 'eur',
        ]);
    }
}