<?php

declare(strict_types=1);

final class ConsolePaymentLogger extends ConsoleLogger implements PaymentLoggerInterface
{
    private const STATUS_SUCCESS = 'SUCCESS';
    private const STATUS_FAILED = 'FAILED';

    public function logResult(
        int $bookingId,
        float $amount,
        string $currency,
        float $durationMs,
        bool $success,
        ?string $message = null
    ): void {
        if ($message === null) {
            $status = $success ? self::STATUS_SUCCESS : self::STATUS_FAILED;
            $this->write(sprintf(
                "MONITORING [%s] Payment booking #%d | Amount: %.2f %s | Duration: %.2f ms",
                $status,
                $bookingId,
                $amount,
                $currency,
                $durationMs
            ));
            return;
        }

        $this->write(sprintf(
            "MONITORING [ERROR] Payment booking #%d | Amount: %.2f %s | Duration: %.2f ms | Msg: %s",
            $bookingId,
            $amount,
            $currency,
            $durationMs,
            $message
        ));
    }
}
