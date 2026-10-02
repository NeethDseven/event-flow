<?php

declare(strict_types=1);

class CurrencyConverter
{
    // Taux par rapport à l'EUR (base)
    private array $rates = [
        DomainConstants::CURRENCY_EUR => 1.0,
        'USD' => 1.08,
        DomainConstants::CURRENCY_ZAR => 19.5, // Ex: PayFast (Afrique du Sud)
    ];

    public function convert(Money $money, string $targetCurrency): Money
    {
        $sourceCurrency = $money->getCurrency();
        $targetCurrency = strtoupper($targetCurrency);

        if ($sourceCurrency === $targetCurrency) {
            return $money;
        }

        if (!isset($this->rates[$sourceCurrency]) || !isset($this->rates[$targetCurrency])) {
            throw new \InvalidArgumentException("Devise non supportée : {$targetCurrency}");
        }

        // Conversion vers la base EUR puis vers la devise cible
        $amountInEur = $money->getAmount() / $this->rates[$sourceCurrency];
        $convertedAmount = $amountInEur * $this->rates[$targetCurrency];

        return new Money($convertedAmount, $targetCurrency);
    }
}