<?php

declare(strict_types=1);

class PricingCalculator
{
    public function calculateTotal(Booking $booking): float
    {
        $baseStrategy = new StandardPricingStrategy();

        $strategy = match ($booking->getCustomer()->getType()) {
            'vip' => new VipPricingStrategy($baseStrategy),
            default => $baseStrategy,
        };

        return $strategy->calculate($booking);
    }
}