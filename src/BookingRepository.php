<?php

declare(strict_types=1);

final class BookingRepository
{
    public function save(Booking $booking, float $total): void
    {
        echo sprintf(
            "SQL INSERT booking=%d total=%.2f status=confirmed\n",
            $booking->getId(),
            $total
        );
    }
}