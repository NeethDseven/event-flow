<?php

declare(strict_types=1);

interface BookingLoggerInterface
{
    public function logConfirmedBooking(int $bookingId, float $total): void;
}
