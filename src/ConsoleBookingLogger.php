<?php

declare(strict_types=1);

final class ConsoleBookingLogger extends ConsoleLogger implements BookingLoggerInterface
{
    public function logConfirmedBooking(int $bookingId, float $total): void
    {
        $this->write(sprintf(
            "SQL INSERT booking=%d total=%.2f status=confirmed",
            $bookingId,
            $total
        ));
    }
}
