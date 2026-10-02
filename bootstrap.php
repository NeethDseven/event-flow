<?php

declare(strict_types=1);

// =========================
// Modèles métier
// =========================

require_once __DIR__ . '/src/Customer.php';
require_once __DIR__ . '/src/Ticket.php';
require_once __DIR__ . '/src/BookingItem.php';
require_once __DIR__ . '/src/Booking.php';


// =========================
// Dépendances techniques
// =========================

require_once __DIR__ . '/src/StripeClient.php';
require_once __DIR__ . '/src/PayFastSdk.php';
require_once __DIR__ . '/src/EmailService.php';
require_once __DIR__ . '/src/SmsClient.php';
require_once __DIR__ . '/src/AnalyticsClient.php';


// =========================
// Services
// =========================

require_once __DIR__ . '/src/LoyaltyService.php';
require_once __DIR__ . '/src/PricingCalculator.php';


// =========================
// Paiement
// =========================

require_once __DIR__ . '/src/PaymentProcessorInterface.php';
require_once __DIR__ . '/src/StripePaymentAdapter.php';
require_once __DIR__ . '/src/PayFastPaymentAdapter.php';
require_once __DIR__ . '/src/MonitoredPaymentGateway.php';


// =========================
// Observer / événements
// =========================

require_once __DIR__ . '/src/BookingConfirmedEvent.php';
require_once __DIR__ . '/src/BookingObserverInterface.php';
require_once __DIR__ . '/src/ConfirmationNotifier.php';
require_once __DIR__ . '/src/LoyaltyProcessor.php';
require_once __DIR__ . '/src/AnalyticsTracker.php';


// =========================
// Service principal
// =========================

require_once __DIR__ . '/src/BookingService.php';