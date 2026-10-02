<?php

declare(strict_types=1);

final class BookingRepository
{
    public function __construct(
        private ?BookingLoggerInterface $logger = null
    ) {
        $this->logger ??= new ConsoleBookingLogger();
    }

    public function save(Booking $booking, float $total): void
    {
        $this->logger->logConfirmedBooking($booking->getId(), $total);
    }
}