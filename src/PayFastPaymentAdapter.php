<?php

declare(strict_types=1);

class PayFastPaymentAdapter implements PaymentProcessorInterface
{
    private PayFastSdk $payFastSdk;

    public function __construct(?PayFastSdk $payFastSdk = null)
    {
        $this->payFastSdk = $payFastSdk ?? new PayFastSdk();
    }

    public function processPayment(float $amount, int $bookingId): bool
    {
        $result = $this->payFastSdk->executePayment([
            'reference' => (string) $bookingId,
            'amount_cents' => (int) round($amount * 100),
            'currency' => 'EUR',
        ]);

        return $result['success'] ?? false;
    }
}