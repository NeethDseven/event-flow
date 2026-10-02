<?php

declare(strict_types=1);

class ConfirmationNotifier implements BookingObserverInterface
{
    private EmailService $emailService;
    private SmsClient $smsClient;

    public function __construct(?EmailService $emailService = null, ?SmsClient $smsClient = null)
    {
        $this->emailService = $emailService ?? new EmailService();
        $this->smsClient = $smsClient ?? new SmsClient();
    }

    public function onBookingConfirmed(BookingConfirmedEvent $event): void
    {
        $booking = $event->getBooking();
        $customer = $booking->getCustomer();

        // 1. Email de confirmation
        $this->emailService->sendConfirmation($customer->getEmail(), $booking->getId());

        // 2. SMS uniquement si un numéro est présent (méthode ->send)
        $phone = trim($customer->getPhone());
        if ($phone !== '') {
            $this->smsClient->send($phone, sprintf("Réservation #%d confirmée !", $booking->getId()));
        }
    }
}