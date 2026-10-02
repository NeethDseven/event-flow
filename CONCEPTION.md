# Note de conception

## 1. Choix principaux

- **Séparation des responsabilités** : Découplage de la logique métier de réservation de la logique de calcul tarifaire, d'infrastructure de paiement et de notification.
- **Inversion des dépendances** : Utilisation d'interfaces (`PaymentProcessorInterface`, `BookingObserverInterface`) pour ne dépendre que d'abstractions et non de réalisations concrètes.
- **Sécurisation par les tests** : Mise en place de tests de caractérisation couvrant la grille tarifaire, la gestion des devises et l'ensemble des cas limites.

## 2. Principes SOLID mobilisés

- **SRP (Single Responsibility Principle)** :
  - *Problème initial* : `BookingService` concentrait le calcul des prix, l'exécution des paiements, la persistance et les notifications.
  - *Classes concernées* : `BookingService`, `PricingCalculator`, `ConfirmationNotifier`, `LoyaltyProcessor`, `AnalyticsTracker`.
  - *Bénéfice obtenu* : Chaque classe a une seule raison de changer. `BookingService` ne sert plus que d'orchestrateur.

- **OCP (Open/Closed Principle)** :
  - *Problème initial* : Ajouter une remise, un moyen de paiement ou une notification demandait de modifier les structures `if/else` de `BookingService`.
  - *Classes concernées* : `BookingService`, `PaymentProcessorInterface`, `BookingObserverInterface`.
  - *Bénéfice obtenu* : On peut ajouter un processeur (ex: PayFast) ou un observateur sans toucher à une seule ligne de `BookingService`.

- **LSP (Liskov Substitution Principle)** :
  - *Problème initial* : Impossible de remplacer Stripe par un autre processeur de paiement sans casser le contrat.
  - *Classes concernées* : `StripePaymentAdapter`, `PayFastPaymentAdapter`, `PaymentProcessorInterface`.
  - *Bénéfice obtenu* : Les adaptateurs peuvent être interchangés de manière totalement transparente pour `BookingService`.

- **DIP (Dependency Inversion Principle)** :
  - *Problème initial* : Le service haut niveau (`BookingService`) dépendait d'implémentations bas niveau (`StripeClient`).
  - *Classes concernées* : `BookingService`, `PaymentProcessorInterface`.
  - *Bénéfice obtenu* : Le haut niveau dépend désormais d'une abstraction.

## 3. Design Patterns utilisés

- **Adapter** :
  - *Problème* : Le SDK `PayFastSdk` fourni possède une interface incompatible et ne doit pas être modifié.
  - *Solution* : `PayFastPaymentAdapter` enveloppe le SDK pour respecter l'interface `PaymentProcessorInterface`.
  - *Pourquoi pas plus simple* : Modifier `PayFastSdk` était interdit par la consigne, et instancier directement `PayFastSdk` dans le domaine aurait violé DIP.

- **Observer** :
  - *Problème* : La confirmation de réservation déclenche plusieurs actions (Email, SMS, Fidélité, Analytics) qui risquent d'évoluer avec le temps.
  - *Solution* : Un bus d'événements interne déclenchant un `BookingConfirmedEvent` aux écouteurs enregistrés.
  - *Pourquoi pas plus simple* : Des appels directs dans `BookingService` l'auraient surchargé et auraient violé OCP.

- **Decorator** :
  - *Problème* : Superviser les paiements (durée, statut, logs) sans modifier ni `BookingService`, ni les SDKs tiers.
  - *Solution* : `PaymentMonitoringDecorator` enveloppe l'implémentation de `PaymentProcessorInterface` pour capturer la durée et l'état.
  - *Pourquoi pas plus simple* : Modifier chaque client de paiement aurait introduit de la duplication et pollué le code métier.

- **Strategy** :
  - *Problème* : Les règles de réductions VIP et Pass 3 jours allaient évoluer de manière indépendante.
  - *Solution* : `PricingCalculator` encapsule l'algorithme de calcul de prix.

## 4. Solutions envisagées puis écartées

- **Héritage pour les moyens de paiement** : Écarté car `StripeClient` et `PayFastSdk` sont deux SDKs externes indépendants. La composition via l'Adapter a été privilégiée.
- **Un EventDispatcher complexe avec un container IoC** : Écarté car surdimensionné (over-engineering) pour un projet sans framework. Un tableau d'observateurs suffit amplement.

## 5. Ce que nous améliorerions avec plus de temps

- Définir un objet valeur `Money` (avec valeur décimale et devise) pour éviter d'utiliser des `float` sujets à des imprécisions d'arrondi.
- Améliorer la gestion des erreurs lors d'un échec de paiement (levée d'exceptions customisées `PaymentFailedException`).
