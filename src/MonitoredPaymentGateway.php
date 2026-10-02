<?php

declare(strict_types=1);

final class MonitoredPaymentGateway implements PaymentProcessorInterface
{
    public function __construct(
        private PaymentProcessorInterface $processor
    ) {
    }

    public function processPayment(
        float $amount,
        int $bookingId,
        string $currency = 'EUR'
    ): bool {
        $start = microtime(true);

        echo "PAYMENT REQUEST amount={$amount} booking={$bookingId}" . PHP_EOL;

        try {
            $success = $this->processor->processPayment(
                $amount,
                $bookingId,
                $currency
            );

            $duration = microtime(true) - $start;

            if ($success) {
                echo "PAYMENT SUCCESS amount={$amount} booking={$bookingId} duration={$duration}" . PHP_EOL;
            } else {
                echo "PAYMENT FAILURE amount={$amount} booking={$bookingId} duration={$duration}" . PHP_EOL;
            }

            return $success;
        } catch (Throwable $exception) {
            $duration = microtime(true) - $start;

            echo "PAYMENT FAILURE amount={$amount} booking={$bookingId} duration={$duration}" . PHP_EOL;

            throw $exception;
        }
    }
}