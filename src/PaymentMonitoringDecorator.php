<?php

declare(strict_types=1);

class PaymentMonitoringDecorator implements PaymentProcessorInterface
{
    private PaymentLoggerInterface $logger;

    public function __construct(
        private PaymentProcessorInterface $wrapped,
        ?PaymentLoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new ConsolePaymentLogger();
    }

    public function processPayment(float $amount, int $bookingId, string $currency = DomainConstants::CURRENCY_EUR): bool
    {
        $startTime = microtime(true);

        try {
            $success = $this->wrapped->processPayment($amount, $bookingId, $currency);
            $durationMs = $this->measureDurationMs($startTime);
            $this->logger->logResult($bookingId, $amount, $currency, $durationMs, $success);

            return $success;
        } catch (\Throwable $e) {
            $durationMs = $this->measureDurationMs($startTime);
            $this->logger->logResult($bookingId, $amount, $currency, $durationMs, false, $e->getMessage());

            throw $e;
        }
    }

    private function measureDurationMs(float $startTime): float
    {
        return round((microtime(true) - $startTime) * 1000, 2);
    }
}