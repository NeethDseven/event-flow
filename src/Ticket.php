<?php

declare(strict_types=1);

class Ticket
{
    public function __construct(
        private string $code,
        private string $label,
        private float $price
    ) {}

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}