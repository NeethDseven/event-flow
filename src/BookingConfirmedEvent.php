<?php

declare(strict_types=1);

class BookingConfirmedEvent
{
    public function __construct(
        private Booking $booking,
        private float $totalPaid
    ) {}

    public function getBooking(): Booking
    {
        return $this->booking;
    }

    public function getTotalPaid(): float
    {
        return $this->totalPaid;
    }
}