<?php

declare(strict_types=1);

class PaymentMonitoringDecorator implements PaymentProcessorInterface
{
    public function __construct(
        private PaymentProcessorInterface $wrapped
    ) {}

    public function processPayment(float $amount, int $bookingId): bool
    {
        $startTime = microtime(true);

        try {
            $success = $this->wrapped->processPayment($amount, $bookingId);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $status = $success ? 'SUCCESS' : 'FAILED';
            echo sprintf(
                "MONITORING [%s] Payment booking #%d | Amount: %.2f € | Duration: %.2f ms\n",
                $status,
                $bookingId,
                $amount,
                $duration
            );

            return $success;
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            echo sprintf(
                "MONITORING [ERROR] Payment booking #%d | Amount: %.2f € | Duration: %.2f ms | Msg: %s\n",
                $bookingId,
                $amount,
                $duration,
                $e->getMessage()
            );

            throw $e;
        }
    }
}