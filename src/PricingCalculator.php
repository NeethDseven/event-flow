<?php

declare(strict_types=1);

class PricingCalculator
{
    private const VIP_DISCOUNT_UNDER_100 = 0.05;
    private const VIP_DISCOUNT_UNDER_300 = 0.10;
    private const VIP_DISCOUNT_300_AND_MORE = 0.15;

    private const VIP_THRESHOLD_100 = 100.0;
    private const VIP_THRESHOLD_300 = 300.0;

    private const THREE_DAYS_DISCOUNT = 20.0;

    public function calculateTotal(Booking $booking): float
    {
        $subtotal = 0.0;

        foreach ($booking->getItems() as $item) {
            $subtotal += $item->getTicket()->getPrice() * $item->getQuantity();
        }

        $discountRate = $this->getVipDiscountRate(
            $booking->getCustomer(),
            $subtotal
        );

        $totalAfterVipDiscount = $subtotal * (1 - $discountRate);

        if ($booking->getPassType() === 'three_days') {
            $totalAfterVipDiscount -= self::THREE_DAYS_DISCOUNT;
        }

        return max(0.0, round($totalAfterVipDiscount, 2));
    }

    private function getVipDiscountRate(
        Customer $customer,
        float $subtotal
    ): float {
        if ($customer->getType() !== 'vip') {
            return 0.0;
        }

        if ($subtotal < self::VIP_THRESHOLD_100) {
            return self::VIP_DISCOUNT_UNDER_100;
        }

        if ($subtotal < self::VIP_THRESHOLD_300) {
            return self::VIP_DISCOUNT_UNDER_300;
        }

        return self::VIP_DISCOUNT_300_AND_MORE;
    }
}