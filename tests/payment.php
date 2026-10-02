<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TestRunner.php';

$runner = new TestRunner();


final class SpyBookingObserver implements BookingObserverInterface
{
    public int $calls = 0;

    public function onBookingConfirmed(BookingConfirmedEvent $event): void
    {
        $this->calls++;
    }
}

function createPaymentBooking(int $id, float $price): Booking
{
    $customer = new Customer(id: 10, email: 'pay@example.com', phone: '', type: 'standard');
    $booking = new Booking(id: $id, customer: $customer, passType: 'day');
    $booking->addItem(new BookingItem(new Ticket(code: 'PAY', label: 'Ticket paiement', price: $price), 1));

    return $booking;
}

function confirmWith(PaymentProcessorInterface $processor, Booking $booking): array
{
    $spy = new SpyBookingObserver();
    $service = new BookingService();
    $service->addObserver($spy);

    $total = null;
    $exception = null;

    ob_start();
    try {
        $total = $service->confirm($booking, $processor);
    } catch (Throwable $error) {
        $exception = $error;
    }
    $output = ob_get_clean();

    return ['total' => $total, 'exception' => $exception, 'output' => $output, 'observerCalls' => $spy->calls];
}

$processors = [
    'Stripe' => new StripePaymentAdapter(),
    'PayFast' => new PayFastPaymentAdapter(),
];

foreach ($processors as $name => $processor) {
    echo "\n=== {$name} ===\n";

    // Paiement accepté
    $accepted = confirmWith($processor, createPaymentBooking(5001, 79.90));
    $runner->same(null, $accepted['exception'], "{$name} : paiement accepté sans erreur");
    $runner->near(79.90, (float) $accepted['total'], "{$name} : le total payé est renvoyé");
    $runner->same(true, str_contains($accepted['output'], 'SQL INSERT booking=5001'), "{$name} : la réservation est enregistrée");
    $runner->same(1, $accepted['observerCalls'], "{$name} : les réactions après confirmation sont déclenchées");

    // Paiement refusé (montant nul)
    $refused = confirmWith($processor, createPaymentBooking(5002, 0.0));
    $runner->same(PaymentFailedException::class, $refused['exception'] ? get_class($refused['exception']) : null, "{$name} : un refus lève PaymentFailedException");
    $runner->same(false, str_contains($refused['output'], 'SQL INSERT'), "{$name} : un refus n'enregistre pas la réservation");
    $runner->same(0, $refused['observerCalls'], "{$name} : un refus ne déclenche aucune réaction");
}

$runner->summary();