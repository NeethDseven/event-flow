<?php

declare(strict_types=1);

interface PaymentLoggerInterface
{
    public function logResult(
        int $bookingId,
        float $amount,
        string $currency,
        float $durationMs,
        bool $success,
        ?string $message = null
    ): void;
}
