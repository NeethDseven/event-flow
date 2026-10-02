<?php

declare(strict_types=1);

class AnalyticsTracker implements BookingObserverInterface
{
    private AnalyticsClient $analyticsClient;

    public function __construct(?AnalyticsClient $analyticsClient = null)
    {
        $this->analyticsClient = $analyticsClient ?? new AnalyticsClient();
    }

    public function onBookingConfirmed(BookingConfirmedEvent $event): void
    {
        $booking = $event->getBooking();
        
        // Utilisation de la méthode réelle ->track()
        $this->analyticsClient->track('booking_confirmed', [
            'booking_id' => $booking->getId(),
            'total' => $event->getTotalPaid(),
        ]);
    }
}