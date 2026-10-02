<?php

declare(strict_types=1);

final class Money
{
    public function __construct(
        private float $amount,
        private string $currency = 'EUR'
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException("Le montant ne peut pas être négatif.");
        }
    }

    public function getAmount(): float
    {
        return round($this->amount, 2);
    }

    public function getCurrency(): string
    {
        return strtoupper($this->currency);
    }

    public function format(): string
    {
        return sprintf("%.2f %s", $this->getAmount(), $this->getCurrency());
    }
}