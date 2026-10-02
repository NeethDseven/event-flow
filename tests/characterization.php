<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

function assertSameValue(mixed $expected, mixed $actual, string $testName): void
{
    $normalizedActual = is_float($actual) ? round($actual, 2) : $actual;
    $normalizedExpected = is_float($expected) ? round($expected, 2) : $expected;

    if ($normalizedExpected === $normalizedActual) {
        echo "[OK] {$testName}\n";
    } else {
        echo "[FAIL] {$testName} - Attendu: " . json_encode($expected) . ", Obtenu: " . json_encode($actual) . "\n";
    }
}

// Données partagées pour les tests
$customerVip = new Customer(id: 1, email: 'vip@example.com', phone: '0600000000', type: 'vip');
$customerStd = new Customer(id: 2, email: 'std@example.com', phone: '0600000000', type: 'standard');
$dayTicket = new Ticket(code: 'DAY-1', label: 'Pass 1 Jour', price: 79.90);

// --- Test 1: Ticket #101 / #103 - Comportement VIP avec Stripe Adapter ---
$bookingStripe = new Booking(id: 1001, customer: $customerVip, passType: 'day');
$bookingStripe->addItem(new BookingItem($dayTicket, 2));

$service = new BookingService();
$stripeAdapter = new StripePaymentAdapter();
$totalStripe = $service->confirm($bookingStripe, $stripeAdapter);

assertSameValue(143.82, $totalStripe, 'Paiement Stripe pour client VIP (remise 10%)');

// --- Test 2: Ticket #103 - Paiement via PayFast Adapter ---
$bookingPayFast = new Booking(id: 1005, customer: $customerStd, passType: 'day');
$bookingPayFast->addItem(new BookingItem($dayTicket, 1));
$payFastAdapter = new PayFastPaymentAdapter();
$totalPayFast = $service->confirm($bookingPayFast, $payFastAdapter);

assertSameValue(79.90, $totalPayFast, 'Paiement via PayFast');

// --- Tests Ticket #102 : Calculs de la nouvelle grille tarifaire ---
$calculator = new PricingCalculator();

// Standard : Pas de remise
$bookingStd = new Booking(id: 1002, customer: $customerStd, passType: 'day');
$bookingStd->addItem(new BookingItem($dayTicket, 1));
assertSameValue(79.90, $calculator->calculateTotal($bookingStd), 'Tarif client Standard (0% remise)');

// VIP < 100€ : Remise 5%
$bookingVipSmall = new Booking(id: 1003, customer: $customerVip, passType: 'day');
$bookingVipSmall->addItem(new BookingItem($dayTicket, 1));
assertSameValue(75.91, $calculator->calculateTotal($bookingVipSmall), 'Tarif VIP < 100€ (5% remise)');

// VIP >= 300€ + Pass 3 jours (-15% puis -20€)
$expensiveTicket = new Ticket(code: 'PASS-3', label: 'Pass 3 jours', price: 200.00);
$bookingVipBig = new Booking(id: 1004, customer: $customerVip, passType: 'three_days');
$bookingVipBig->addItem(new BookingItem($expensiveTicket, 2));
assertSameValue(320.00, $calculator->calculateTotal($bookingVipBig), 'Tarif VIP >= 300€ avec Pass 3 jours');