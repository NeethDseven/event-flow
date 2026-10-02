<?php

declare(strict_types=1);

class BookingService
{
    private PricingCalculator $pricingCalculator;

    public function __construct(?PricingCalculator $pricingCalculator = null)
    {
        $this->pricingCalculator = $pricingCalculator ?? new PricingCalculator();
    }

    public function confirm(Booking $booking, PaymentProcessorInterface $paymentProcessor): float
    {
        // 1. Calcul du total
        $total = $this->pricingCalculator->calculateTotal($booking);

        // 2. Traitement du paiement via l'abstraction (Pattern Adapter)
        $paymentProcessor->processPayment($total, $booking->getId());

        // 3. Persistance
        echo sprintf("SQL INSERT booking=%d total=%.2f status=confirmed\n", $booking->getId(), $total);

        // 4. Notifications
        $emailService = new EmailService();
        $emailService->sendConfirmation($booking->getCustomer()->getEmail(), $booking->getId());

        return $total;
    }
}