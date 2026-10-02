<?php

declare(strict_types=1);

interface BookingObserverInterface
{
    public function onBookingConfirmed(BookingConfirmedEvent $event): void;
}