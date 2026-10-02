<?php

declare(strict_types=1);

class BookingService
{
    private PricingCalculator $pricingCalculator;

    public function __construct(?PricingCalculator $pricingCalculator = null)
    {
        $this->pricingCalculator = $pricingCalculator ?? new PricingCalculator();
    }

    public function confirm(Booking $booking, string $paymentMethod): float
    {
        // 1. Calcul du total via le composant isolé
        $total = $this->pricingCalculator->calculateTotal($booking);

        // 2. Traitement du paiement Stripe initial
        if ($paymentMethod === 'stripe') {
            $stripe = new StripeClient();
            $stripe->charge($total);
        }

        // 3. Persistance
        echo sprintf("SQL INSERT booking=%d total=%.2f status=confirmed\n", $booking->getId(), $total);

        // 4. Notifications (utilisation du getter getEmail())
        $emailService = new EmailService();
        $emailService->sendConfirmation($booking->getCustomer()->getEmail(), $booking->getId());

        return $total;
    }
}