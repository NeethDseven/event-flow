<?php

declare(strict_types=1);

class VipPricingStrategy implements PricingStrategyInterface
{
    public function __construct(
        private PricingStrategyInterface $baseStrategy
    ) {}

    public function calculate(Booking $booking): float
{
        $subtotal = $this->baseStrategy->calculate($booking);

        if ($subtotal >= 300.0) {
            $discount = 0.15;
        } elseif ($subtotal >= 100.0) {
            $discount = 0.10;
        } else {
            $discount = 0.05;
        }

        return round($subtotal * (1.0 - $discount), 2);
    }
}