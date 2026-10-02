<?php

declare(strict_types=1);

class VipPricingStrategy implements PricingStrategyInterface
{
    private const DISCOUNT_BELOW_100 = 0.05;
    private const DISCOUNT_100_TO_299 = 0.10;
    private const DISCOUNT_300_AND_OVER = 0.15;
    private const THREE_DAYS_PASS_DISCOUNT = 20.0;

    public function __construct(
        private PricingStrategyInterface $baseStrategy
    ) {}

    public function calculate(Booking $booking): float
    {
        $subtotal = $this->baseStrategy->calculate($booking);
        $discount = $this->getDiscountRate($subtotal);
        $total = $subtotal * (1.0 - $discount);

        if ($booking->getPassType() === Booking::PASS_THREE_DAYS) {
            $total -= self::THREE_DAYS_PASS_DISCOUNT;
        }

        return round(max(0.0, $total), 2);
    }

    private function getDiscountRate(float $subtotal): float
    {
        if ($subtotal >= 300.0) {
            return self::DISCOUNT_300_AND_OVER;
        }

        if ($subtotal >= 100.0) {
            return self::DISCOUNT_100_TO_299;
        }

        return self::DISCOUNT_BELOW_100;
    }
}