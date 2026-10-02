<?php

declare(strict_types=1);

class PayFastPaymentAdapter implements PaymentProcessorInterface
{
    private PayFastSdk $payFastSdk;
    private CurrencyConverter $converter;

    public function __construct(
        ?PayFastSdk $payFastSdk = null,
        ?CurrencyConverter $converter = null
    ) {
        $this->payFastSdk = $payFastSdk ?? new PayFastSdk();
        $this->converter = $converter ?? new CurrencyConverter();
    }

    public function processPayment(float $amount, int $bookingId, string $currency = 'EUR'): bool
    {
        $money = new Money($amount, $currency);
        $converted = $this->converter->convert($money, DomainConstants::CURRENCY_ZAR);
        $payload = [
            'reference' => (string) $bookingId,
            'amount_cents' => (int) round($converted->getAmount() * 100),
            'currency' => $converted->getCurrency(),
        ];
        $result = $this->payFastSdk->executePayment($payload);

        return $result['success'];
    }
}