<?php

declare(strict_types=1);

class BookingService
{
    private PricingCalculator $pricingCalculator;
    private BookingLoggerInterface $logger;
    /** @var BookingObserverInterface[] */
    private array $observers = [];

    public function __construct(
        ?PricingCalculator $pricingCalculator = null,
        ?BookingLoggerInterface $logger = null
    ) {
        $this->pricingCalculator = $pricingCalculator ?? new PricingCalculator();
        $this->logger = $logger ?? new ConsoleBookingLogger();
    }

    public function addObserver(BookingObserverInterface $observer): void
    {
        $this->observers[] = $observer;
    }

    public function confirm(Booking $booking, PaymentProcessorInterface $paymentProcessor): float
    {
        $total = $this->pricingCalculator->calculateTotal($booking);
        $success = $paymentProcessor->processPayment($total, $booking->getId());

        if (!$success) {
            throw new \RuntimeException(
                sprintf("Échec du paiement pour la réservation #%d", $booking->getId())
            );
        }

        $this->logger->logConfirmedBooking($booking->getId(), $total);
        $this->notifyObservers($booking, $total);

        return $total;
    }

    private function notifyObservers(Booking $booking, float $total): void
    {
        $event = new BookingConfirmedEvent($booking, $total);
        foreach ($this->observers as $observer) {
            $observer->onBookingConfirmed($event);
        }
    }
}