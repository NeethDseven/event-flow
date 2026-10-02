<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

function assertSameValue(mixed $expected, mixed $actual, string $testName): void
{
    // Arrondi à 2 décimales pour éviter les imprécisions d'affichage float
    $normalizedActual = is_float($actual) ? round($actual, 2) : $actual;
    $normalizedExpected = is_float($expected) ? round($expected, 2) : $expected;

    if ($normalizedExpected === $normalizedActual) {
        echo "[OK] {$testName}\n";
    } else {
        echo "[FAIL] {$testName} - Attendu: " . json_encode($expected) . ", Obtenu: " . json_encode($actual) . "\n";
    }
}

// Test 1: Comportement nominal VIP avec Stripe
$customerVip = new Customer(id: 1, email: 'vip@example.com', phone: '0600000000', type: 'vip');
$dayTicket = new Ticket(code: 'DAY-1', label: 'Pass 1 Jour', price: 79.90);
$booking = new Booking(id: 1001, customer: $customerVip, passType: 'day');
$booking->addItem(new BookingItem($dayTicket, 2));

$service = new BookingService();
$total = $service->confirm($booking, 'stripe');

assertSameValue(143.82, $total, 'Paiement Stripe pour client VIP (remise 10%)');