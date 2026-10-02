<?php

declare(strict_types=1);

class Customer
{
    public function __construct(
        private int $id,
        private string $email,
        private ?string $phone = null,
        private string $type = 'standard'
    ) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email');
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function hasPhone(): bool
    {
        return $this->phone !== null && trim($this->phone) !== '';
    }

    public function getType(): string
    {
        return $this->type;
    }
}