<?php

declare(strict_types=1);

class StandardPricingStrategy implements PricingStrategyInterface
{
    public function calculate(Booking $booking): float
{
    $total = 0.0;
    foreach ($booking->getItems() as $item) {
        $price = $item->getTicket()->getPrice();
        if ($price < 0) {
            throw new \InvalidArgumentException("Le prix d'un billet ne peut pas être négatif.");
        }
        $total += $price * $item->getQuantity();
    }

    return round($total, 2);
}
}