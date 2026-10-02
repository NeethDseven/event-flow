<?php

declare(strict_types=1);

class BookingService
{
    private PricingCalculator $pricingCalculator;
    /** @var BookingObserverInterface[] */
    private array $observers = [];

    public function __construct(?PricingCalculator $pricingCalculator = null)
    {
        $this->pricingCalculator = $pricingCalculator ?? new PricingCalculator();
    }

    public function addObserver(BookingObserverInterface $observer): void
    {
        $this->observers[] = $observer;
    }

    public function confirm(Booking $booking, PaymentProcessorInterface $paymentProcessor): float
    {
        // 1. Calcul du total
        $total = $this->pricingCalculator->calculateTotal($booking);

        // 2. Traitement du paiement
        $paymentProcessor->processPayment($total, $booking->getId());

        // 3. Persistance
        echo sprintf("SQL INSERT booking=%d total=%.2f status=confirmed\n", $booking->getId(), $total);

        // 4. Notification des observateurs (Pattern Observer)
        $event = new BookingConfirmedEvent($booking, $total);
        foreach ($this->observers as $observer) {
            $observer->onBookingConfirmed($event);
        }

        return $total;
    }
}