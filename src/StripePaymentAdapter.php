<?php

declare(strict_types=1);

class StripePaymentAdapter implements PaymentProcessorInterface
{
    private StripeClient $stripeClient;

    public function __construct(?StripeClient $stripeClient = null)
    {
        $this->stripeClient = $stripeClient ?? new StripeClient();
    }

    public function processPayment(float $amount, int $bookingId): bool
    {
        $this->stripeClient->charge($amount);
        return true;
    }
}