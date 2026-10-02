<?php

declare(strict_types=1);

class PricingCalculator
{
    private const THREE_DAYS_PASS = 'three_days';
    private const THREE_DAYS_PASS_DISCOUNT = 20.0;

    public function calculateTotal(Booking $booking): float
    {
        $baseStrategy = new StandardPricingStrategy();

        $strategy = match ($booking->getCustomer()->getType()) {
            Customer::TYPE_VIP => new VipPricingStrategy($baseStrategy),
            default => $baseStrategy,
        };

        $total = $strategy->calculate($booking);

        if ($booking->getPassType() === self::THREE_DAYS_PASS) {
            $total -= self::THREE_DAYS_PASS_DISCOUNT;
        }

        return round(max(0.0, $total), 2);
    }
}