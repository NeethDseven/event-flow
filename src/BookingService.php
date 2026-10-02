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

        // 2. Traitement du paiement
        $paymentProcessor->processPayment($total, $booking->getId());

        // 3. Persistance
        $this->bookingRepository->save($booking, $total);

        // 4. Notification des observateurs
        $event = new BookingConfirmedEvent($booking, $total);

        foreach ($this->observers as $observer) {
            $observer->onBookingConfirmed($event);
        }

        return $total;
    }
}