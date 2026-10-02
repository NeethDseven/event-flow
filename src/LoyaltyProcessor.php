<?php

declare(strict_types=1);

class LoyaltyProcessor implements BookingObserverInterface
{
    private LoyaltyService $loyaltyService;

    public function __construct(?LoyaltyService $loyaltyService = null)
    {
        $this->loyaltyService = $loyaltyService ?? new LoyaltyService();
    }

    public function onBookingConfirmed(BookingConfirmedEvent $event): void
    {
        $customer = $event->getBooking()->getCustomer();
        $this->loyaltyService->addPoints($customer->getId(), (int) floor($event->getTotalPaid()));
    }
}