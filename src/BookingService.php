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

    public function confirm(Booking $booking, PaymentProcessorInterface $paymentProcessor): float
    {
        if ($booking->isEmpty()) {
            throw new InvalidArgumentException('Empty booking');
        }

        // 1. Calcul du total
        $total = $this->pricingCalculator->calculateTotal($booking);

        // 2. Traitement du paiement et vérification du résultat
        $success = $paymentProcessor->processPayment($total, $booking->getId());

        if (!$success) {
            throw new PaymentFailedException(
                sprintf("Échec du paiement pour la réservation #%d", $booking->getId())
            );
        }

        // 3. Confirmation et persistance (uniquement en cas de paiement réussi)
        $booking->confirm();
        $this->bookingRepository->save($booking, $total);

        // 4. Notification des observateurs (Pattern Observer)
        $this->notifyObservers(new BookingConfirmedEvent($booking, $total));

        return $total;
    }

    /**
     * Une réaction qui échoue ne doit ni annuler une réservation déjà payée,
     * ni empêcher les autres réactions de s'exécuter.
     */
    private function notifyObservers(BookingConfirmedEvent $event): void
    {
        foreach ($this->observers as $observer) {
            try {
                $observer->onBookingConfirmed($event);
            } catch (\Throwable $error) {
                echo sprintf("OBSERVER ERROR %s: %s\n", $observer::class, $error->getMessage());
            }
        }
    }
}