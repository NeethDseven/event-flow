<?php

declare(strict_types=1);

class BookingService
{
    private PricingCalculator $pricingCalculator;
    private BookingRepository $bookingRepository;

    /** @var BookingObserverInterface[] */
    private array $observers = [];

    public function __construct(
        ?PricingCalculator $pricingCalculator = null,
        ?BookingRepository $bookingRepository = null
    ) {
        $this->pricingCalculator = $pricingCalculator ?? new PricingCalculator();
        $this->bookingRepository = $bookingRepository ?? new BookingRepository();
    }

    public function addObserver(BookingObserverInterface $observer): void
    {
        $this->observers[] = $observer;
    }

    public function confirm(
        Booking $booking,
        PaymentProcessorInterface $paymentProcessor
    ): float {
        // 1. Calcul du total
        $total = $this->pricingCalculator->calculateTotal($booking);

        // 2. Paiement
        $success = $paymentProcessor->processPayment(
            $total,
            $booking->getId(),
            'EUR'
        );

        if (!$success) {
            throw new RuntimeException(
                sprintf(
                    "Échec du paiement pour la réservation #%d",
                    $booking->getId()
                )
            );
        }

        // 3. Persistance uniquement après paiement réussi
        $this->bookingRepository->save($booking, $total);

        // 4. Notification
        $this->notifyObservers($booking, $total);

        return $total;
    }

    private function notifyObservers(
        Booking $booking,
        float $total
    ): void {
        $event = new BookingConfirmedEvent($booking, $total);

        foreach ($this->observers as $observer) {
            $observer->onBookingConfirmed($event);
        }
    }
}