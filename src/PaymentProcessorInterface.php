<?php

declare(strict_types=1);

interface PaymentProcessorInterface
{
    /**
     * Effectue le paiement d'un montant pour une réservation.
     *
     * @return bool true si le paiement est accepté, false s'il est refusé
     * @throws PaymentFailedException si le prestataire signale une erreur
     */
    public function processPayment(float $amount, int $bookingId, string $currency = 'EUR'): bool;
}