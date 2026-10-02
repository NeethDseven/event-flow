<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TestRunner.php';

$runner = new TestRunner();

// Données de test
$customerVip = new Customer(id: 1, email: 'vip@example.com', phone: '0600000000', type: 'vip');
$customerStd = new Customer(id: 2, email: 'std@example.com', phone: '0611223344', type: 'standard');
$customerNoPhone = new Customer(id: 3, email: 'nophone@example.com', phone: '', type: 'standard');

$dayTicket = new Ticket(code: 'DAY-1', label: 'Pass 1 Jour', price: 79.90);
$ticket100 = new Ticket(code: 'T-100', label: 'Ticket 100€', price: 100.00);
$ticket150 = new Ticket(code: 'T-150', label: 'Ticket 150€', price: 150.00);
$ticketNegative = new Ticket(code: 'T-NEG', label: 'Ticket Négatif', price: -50.00);

$calculator = new PricingCalculator();

echo "=== 1. TESTS DES BORNES TARIFAIRES (100 € et 300 €) ===\n";

// Borne VIP 100€ exacte (remise 10%)
$booking100 = new Booking(id: 2001, customer: $customerVip, passType: 'day');
$booking100->addItem(new BookingItem($ticket100, 1));
$runner->near(90.00, $calculator->calculateTotal($booking100), 'VIP Borne 100€ exacte (10% remise = 90€)');

// VIP Juste sous la borne 100€ (99.90€ -> remise 5%)
$ticket9990 = new Ticket(code: 'T-9990', label: 'Ticket 99.90€', price: 99.90);
$booking9990 = new Booking(id: 2002, customer: $customerVip, passType: 'day');
$booking9990->addItem(new BookingItem($ticket9990, 1));
$runner->near(94.91, $calculator->calculateTotal($booking9990), 'VIP Juste sous 100€ (5% remise = 94.91€)');

// Borne VIP 300€ exacte avec Pass 3 jours (300 - 15% = 255€ ; 255 - 20€ = 235€)
$ticket300 = new Ticket(code: 'T-300', label: 'Ticket 300€', price: 300.00);
$booking300 = new Booking(id: 2003, customer: $customerVip, passType: 'three_days');
$booking300->addItem(new BookingItem($ticket300, 1));
$runner->near(235.00, $calculator->calculateTotal($booking300), 'VIP Borne 300€ exacte avec Pass 3 jours');


echo "\n=== 2. TESTS DES MONTANTS NÉGATIFS / PANIER INVALIDE ===\n";

$bookingNegative = new Booking(id: 2004, customer: $customerStd, passType: 'day');
$bookingNegative->addItem(new BookingItem($ticketNegative, 1));

$exceptionThrown = false;
try {
    $calculator->calculateTotal($bookingNegative);
} catch (\Throwable $e) {
    $exceptionThrown = true;
}
$runner->same(true, $exceptionThrown, 'Levée d\'exception sur un montant de billet négatif');


echo "\n=== 3. TESTS DES NOTIFICATIONS ET SMS CONDITIONNEL ===\n";

$notifier = new ConfirmationNotifier();

// Client AVEC téléphone
$bookingWithPhone = new Booking(id: 3001, customer: $customerStd, passType: 'day');
$eventWithPhone = new BookingConfirmedEvent($bookingWithPhone, 79.90);

ob_start();
$notifier->onBookingConfirmed($eventWithPhone);
$outputWithPhone = ob_get_clean();

$hasEmail = str_contains($outputWithPhone, 'EMAIL std@example.com');
$hasSms = str_contains($outputWithPhone, 'SMS 0611223344');
$runner->same(true, $hasEmail && $hasSms, 'Notification avec téléphone (Email + SMS envoyés)');

// Client SANS téléphone
$bookingNoPhone = new Booking(id: 3002, customer: $customerNoPhone, passType: 'day');
$eventNoPhone = new BookingConfirmedEvent($bookingNoPhone, 79.90);

ob_start();
$notifier->onBookingConfirmed($eventNoPhone);
$outputNoPhone = ob_get_clean();

$hasEmailOnly = str_contains($outputNoPhone, 'EMAIL nophone@example.com');
$hasNoSms = !str_contains($outputNoPhone, 'SMS');
$runner->same(true, $hasEmailOnly && $hasNoSms, 'Notification sans téléphone (Email seul, SMS ignoré)');


echo "\n=== 4. TESTS DE GESTION DES PAIEMENTS ET MONITORING ===\n";

class FailedPaymentProcessorMock implements PaymentProcessorInterface
{
    public function processPayment(float $amount, int $bookingId, string $currency = 'EUR'): bool
    {
        return false;
    }
}

$serviceMonitored = new BookingService();
$serviceMonitored->addObserver(new ConfirmationNotifier());

// Test Échec de paiement
$failedProcessor = new PaymentMonitoringDecorator(new FailedPaymentProcessorMock());
$bookingFailed = new Booking(id: 4001, customer: $customerStd, passType: 'day');
$bookingFailed->addItem(new BookingItem($dayTicket, 1));

$paymentFailedAndBlocked = false;
try {
    $serviceMonitored->confirm($bookingFailed, $failedProcessor);
} catch (\RuntimeException $e) {
    $paymentFailedAndBlocked = true;
}
$runner->same(true, $paymentFailedAndBlocked, 'Échec de paiement intercepté et réservation bloquée');

// Test Succès de paiement monitoré
$stripeAdapter = new StripePaymentAdapter();
$monitoredStripe = new PaymentMonitoringDecorator($stripeAdapter);
$bookingSuccess = new Booking(id: 4002, customer: $customerVip, passType: 'day');
$bookingSuccess->addItem(new BookingItem($dayTicket, 1));

$totalSuccess = $serviceMonitored->confirm($bookingSuccess, $monitoredStripe);
$runner->near(75.91, $totalSuccess, 'Paiement Stripe monitoré réussi pour VIP');

// Bilan global des tests et code de sortie (exit 1 si échec)
$runner->summary();