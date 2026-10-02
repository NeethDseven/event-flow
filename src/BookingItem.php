<?php

declare(strict_types=1);

class BookingItem
{
    public function __construct(
        private Ticket $ticket,
        private int $quantity
    ) {}

    public function getTicket(): Ticket
    {
        return $this->ticket;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}