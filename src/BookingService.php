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
        // 1. Vérification de la réservation
        if ($booking->isEmpty()) {
            throw new InvalidArgumentException('Empty booking');
        }

        // 2. Calcul du total
        $total = $this->pricingCalculator->calculateTotal($booking);

        // 3. Paiement
        $success = $paymentProcessor->processPayment(
            $total,
            $booking->getId(),
            'EUR'
        );

        if (!$success) {
            throw new PaymentFailedException(
                sprintf(
                    "Échec du paiement pour la réservation #%d",
                    $booking->getId()
                )
            );
        }

        // 4. Confirmation et persistance
        $booking->confirm();
        $this->bookingRepository->save($booking, $total);

        // 5. Notification des observateurs
        $this->notifyObservers($booking, $total);

        return $total;
    }

    /**
     * Une réaction qui échoue ne doit ni annuler une réservation déjà payée,
     * ni empêcher les autres réactions de s'exécuter.
     */
    private function notifyObservers(
        Booking $booking,
        float $total
    ): void {
        $event = new BookingConfirmedEvent($booking, $total);

        foreach ($this->observers as $observer) {
            try {
                $observer->onBookingConfirmed($event);
            } catch (\Throwable $error) {
                echo sprintf(
                    "OBSERVER ERROR %s: %s\n",
                    $observer::class,
                    $error->getMessage()
                );
            }
        }
    }
}