<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TestRunner.php';

class FailedPaymentProcessorMock implements PaymentProcessorInterface
{
    public function processPayment(float $amount, int $bookingId, string $currency = 'EUR'): bool
    {
        return false;
    }
}

function createCustomers(): array
{
    return [
        'vip' => new Customer(id: 1, email: 'vip@example.com', phone: '0600000000', type: Customer::TYPE_VIP),
        'standard' => new Customer(id: 2, email: 'std@example.com', phone: '0611223344', type: Customer::TYPE_STANDARD),
        'no_phone' => new Customer(id: 3, email: 'nophone@example.com', phone: '', type: Customer::TYPE_STANDARD),
    ];
}

function createTickets(): array
{
    return [
        'day' => new Ticket(code: 'DAY-1', label: 'Pass 1 Jour', price: 79.90),
        '100' => new Ticket(code: 'T-100', label: 'Ticket 100€', price: 100.00),
        '150' => new Ticket(code: 'T-150', label: 'Ticket 150€', price: 150.00),
        'negative' => new Ticket(code: 'T-NEG', label: 'Ticket Négatif', price: -50.00),
        '99_90' => new Ticket(code: 'T-9990', label: 'Ticket 99.90€', price: 99.90),
        '300' => new Ticket(code: 'T-300', label: 'Ticket 300€', price: 300.00),
    ];
}

function runPricingTests(TestRunner $runner): void
{
    $customers = createCustomers();
    $tickets = createTickets();
    $calculator = new PricingCalculator();

    echo "=== 1. TESTS DES BORNES TARIFAIRES (100 € et 300 €) ===\n";

    $booking100 = new Booking(id: 2001, customer: $customers['vip'], passType: Booking::PASS_DAY);
    $booking100->addItem(new BookingItem($tickets['100'], 1));
    $runner->near(90.00, $calculator->calculateTotal($booking100), 'VIP Borne 100€ exacte (10% remise = 90€)');

    $booking9990 = new Booking(id: 2002, customer: $customers['vip'], passType: Booking::PASS_DAY);
    $booking9990->addItem(new BookingItem($tickets['99_90'], 1));
    $runner->near(94.91, $calculator->calculateTotal($booking9990), 'VIP Juste sous 100€ (5% remise = 94.91€)');

    $booking300 = new Booking(id: 2003, customer: $customers['vip'], passType: Booking::PASS_THREE_DAYS);
    $booking300->addItem(new BookingItem($tickets['300'], 1));
    $runner->near(235.00, $calculator->calculateTotal($booking300), 'VIP Borne 300€ exacte avec Pass 3 jours');

    $booking150ThreeDays = new Booking(id: 2005, customer: $customers['vip'], passType: Booking::PASS_THREE_DAYS);
    $booking150ThreeDays->addItem(new BookingItem($tickets['150'], 1));
    $runner->near(115.00, $calculator->calculateTotal($booking150ThreeDays), 'VIP Pass 3 jours sous 300€ (-20€)');

    echo "\n=== 2. TESTS DES MONTANTS NÉGATIFS / PANIER INVALIDE ===\n";

    $bookingNegative = new Booking(id: 2004, customer: $customers['standard'], passType: Booking::PASS_DAY);
    $bookingNegative->addItem(new BookingItem($tickets['negative'], 1));

    $exceptionThrown = false;
    try {
        $calculator->calculateTotal($bookingNegative);
    } catch (\Throwable $e) {
        $exceptionThrown = true;
    }
    $runner->same(true, $exceptionThrown, 'Levée d\'exception sur un montant de billet négatif');
}

function runNotificationTests(TestRunner $runner): void
{
    $customers = createCustomers();
    $notifier = new ConfirmationNotifier();

    echo "\n=== 3. TESTS DES NOTIFICATIONS ET SMS CONDITIONNEL ===\n";

    $bookingWithPhone = new Booking(id: 3001, customer: $customers['standard'], passType: Booking::PASS_DAY);
    $eventWithPhone = new BookingConfirmedEvent($bookingWithPhone, 79.90);

    ob_start();
    $notifier->onBookingConfirmed($eventWithPhone);
    $outputWithPhone = ob_get_clean();

    $hasEmail = str_contains($outputWithPhone, 'EMAIL std@example.com');
    $hasSms = str_contains($outputWithPhone, 'SMS 0611223344');
    $runner->same(true, $hasEmail && $hasSms, 'Notification avec téléphone (Email + SMS envoyés)');

    $bookingNoPhone = new Booking(id: 3002, customer: $customers['no_phone'], passType: Booking::PASS_DAY);
    $eventNoPhone = new BookingConfirmedEvent($bookingNoPhone, 79.90);

    ob_start();
    $notifier->onBookingConfirmed($eventNoPhone);
    $outputNoPhone = ob_get_clean();

    $hasEmailOnly = str_contains($outputNoPhone, 'EMAIL nophone@example.com');
    $hasNoSms = !str_contains($outputNoPhone, 'SMS');
    $runner->same(true, $hasEmailOnly && $hasNoSms, 'Notification sans téléphone (Email seul, SMS ignoré)');
}

function runPaymentTests(TestRunner $runner): void
{
    $customers = createCustomers();
    $tickets = createTickets();
    $serviceMonitored = new BookingService();
    $serviceMonitored->addObserver(new ConfirmationNotifier());

    echo "\n=== 4. TESTS DE GESTION DES PAIEMENTS ET MONITORING ===\n";

    $failedProcessor = new PaymentMonitoringDecorator(new FailedPaymentProcessorMock());
    $bookingFailed = new Booking(id: 4001, customer: $customers['standard'], passType: Booking::PASS_DAY);
    $bookingFailed->addItem(new BookingItem($tickets['day'], 1));

    $paymentFailedAndBlocked = false;
    try {
        $serviceMonitored->confirm($bookingFailed, $failedProcessor);
    } catch (\RuntimeException $e) {
        $paymentFailedAndBlocked = true;
    }
    $runner->same(true, $paymentFailedAndBlocked, 'Échec de paiement intercepté et réservation bloquée');

    $stripeAdapter = new StripePaymentAdapter();
    $monitoredStripe = new PaymentMonitoringDecorator($stripeAdapter);
    $bookingSuccess = new Booking(id: 4002, customer: $customers['vip'], passType: Booking::PASS_DAY);
    $bookingSuccess->addItem(new BookingItem($tickets['day'], 1));

    $totalSuccess = $serviceMonitored->confirm($bookingSuccess, $monitoredStripe);
    $runner->near(75.91, $totalSuccess, 'Paiement Stripe monitoré réussi pour VIP');
}

$runner = new TestRunner();
runPricingTests($runner);
runNotificationTests($runner);
runPaymentTests($runner);
$runner->summary();