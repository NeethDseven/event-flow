<?php

declare(strict_types=1);

class PricingCalculator
{
    public function calculateTotal(Booking $booking): float
    {
        $subtotal = 0.0;
        foreach ($booking->getItems() as $item) {
            if ($item->getTicket()->getPrice() < 0.0) {
                throw new \InvalidArgumentException("Le prix d'un billet ne peut pas etre negatif.");
            }

            $subtotal += $item->getTicket()->getPrice() * $item->getQuantity();
        }

        $discountRate = $this->getVipDiscountRate($booking->getCustomer(), $subtotal);
        $totalAfterVipDiscount = $subtotal * (1 - $discountRate);

        if ($booking->getPassType() === 'three_days') {
            $totalAfterVipDiscount -= 20.0;
        }

        return max(0.0, round($totalAfterVipDiscount, 2));
    }

    private function getVipDiscountRate(Customer $customer, float $subtotal): float
    {
        if ($customer->getType() !== 'vip') {
            return 0.0;
        }

        if ($subtotal < 100.0) {
            return 0.05;
        }

        if ($subtotal < 300.0) {
            return 0.10;
        }

        return 0.15;
    }
}