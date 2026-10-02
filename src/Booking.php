<?php

declare(strict_types=1);

class Booking
{
    private const STATUS_PENDING = 'pending';
    private const STATUS_CONFIRMED = 'confirmed';

    private array $items = [];
    private string $status = self::STATUS_PENDING;

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

    public function isEmpty(): bool
    {
        return count($this->items) === 0;
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function confirm(): void
    {
        $this->status = self::STATUS_CONFIRMED;
    }
}