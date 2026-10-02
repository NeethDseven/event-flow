<?php

declare(strict_types=1);

class StripePaymentAdapter implements PaymentProcessorInterface
{
    private StripeClient $stripeClient;

    public function __construct(?StripeClient $stripeClient = null)
    {
        $this->stripeClient = $stripeClient ?? new StripeClient();
    }

    public function processPayment(float $amount, int $bookingId, string $currency = 'EUR'): bool
    {
        try {
            $transactionId = $this->stripeClient->charge($amount);
        } catch (RuntimeException $exception) {
            throw new PaymentFailedException('Stripe payment failed: ' . $exception->getMessage(), 0, $exception);
        }

        return $transactionId !== '';
    }
}