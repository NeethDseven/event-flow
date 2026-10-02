<?php

declare(strict_types=1);

class Booking
{
    private array $items = [];

    public function __construct(
        private int $id,
        private Customer $customer,
        private string $passType
    ) {}

    public function addItem(BookingItem $item): void
    {
        $this->items[] = $item;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function getPassType(): string
    {
        return $this->passType;
    }
}