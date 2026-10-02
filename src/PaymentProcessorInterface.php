<?php

declare(strict_types=1);

interface PaymentProcessorInterface
{
    /**
     * Effectue le paiement d'un montant pour une réservation.
     */
    public function processPayment(float $amount, int $bookingId): bool;
}