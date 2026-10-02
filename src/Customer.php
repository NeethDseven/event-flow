<?php

declare(strict_types=1);

class Customer
{
    public const TYPE_STANDARD = DomainConstants::CUSTOMER_STANDARD;
    public const TYPE_VIP = DomainConstants::CUSTOMER_VIP;

    public function __construct(
        private int $id,
        private string $email,
        private string $phone,
        private string $type
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getType(): string
    {
        return $this->type;
    }
}