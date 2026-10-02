<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$customer = new Customer(
    id: 42,
    email: 'lea@example.com',
    phone: '0612345678',
    type: 'vip'
);

$dayTicket = new Ticket(
    code: 'DAY-1',
    label: 'Pass Jour 1',
    price: 79.90
);

$booking = new Booking(
    id: 1001,
    customer: $customer,
    passType: 'day'
);

$booking->addItem(new BookingItem($dayTicket, 2));

// Initialisation du service et enregistrement des observateurs (Ticket #104)
$service = new BookingService();
$service->addObserver(new ConfirmationNotifier());
$service->addObserver(new LoyaltyProcessor());
$service->addObserver(new AnalyticsTracker());

// Choix du processeur de paiement (Ticket #103)
$paymentProcessor = new StripePaymentAdapter();

// Monitoring du paiement (Ticket #105)
$monitoredPaymentProcessor = new PaymentMonitoringDecorator($paymentProcessor);

$total = $service->confirm($booking, $monitoredPaymentProcessor);

echo 'TOTAL FINAL: ' . number_format($total, 2, '.', '') . PHP_EOL;