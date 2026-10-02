<?php

declare(strict_types=1);

interface PricingStrategyInterface
{
    public function calculate(Booking $booking): float;
}