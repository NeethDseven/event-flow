<?php

declare(strict_types=1);

interface PaymentProcessorInterface
{
    public function processPayment(float $amount, int $bookingId, string $currency = 'EUR'): bool;
}