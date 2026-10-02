# Note de conception

## 1. Choix principaux

- **Séparation des responsabilités** : Découplage de la logique métier de réservation de la logique de calcul tarifaire, d'infrastructure de paiement et de notification.
- **Inversion des dépendances** : Utilisation d'interfaces (`PaymentProcessorInterface`, `BookingObserverInterface`) pour ne dépendre que d'abstractions et non de réalisations concrètes.
- **Sécurisation par les tests** : Mise en place de tests de caractérisation couvrant la grille tarifaire, la gestion des devises et l'ensemble des cas limites.
- **Gestion de la valeur monétaire** : Utilisation de `Money` et `CurrencyConverter` pour représenter un montant avec sa devise et convertir les paiements PayFast.

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
  - *Solution* : `BookingService` publie un `BookingConfirmedEvent` aux observateurs enregistrés via `BookingObserverInterface`.
  - *Pourquoi pas plus simple* : Des appels directs dans `BookingService` l'auraient surchargé et auraient violé OCP.

- **Decorator** :
  - *Problème* : Superviser les paiements (durée, statut, logs) sans modifier ni `BookingService`, ni les SDKs tiers.
  - *Solution* : `PaymentMonitoringDecorator` enveloppe l'implémentation de `PaymentProcessorInterface` pour capturer la durée et l'état.
  - *Pourquoi pas plus simple* : Modifier chaque client de paiement aurait introduit de la duplication et pollué le code métier.

- **Strategy** :
  - *Problème* : Les règles de réductions VIP et Pass 3 jours allaient évoluer de manière indépendante.
  - *Solution* : `PricingCalculator` sélectionne une stratégie standard ou VIP. `VipPricingStrategy` applique les seuils de remise de 5 %, 10 % et 15 %, ainsi que les 20 € de remise du Pass 3 jours.

## 4. Solutions envisagées puis écartées

- **Héritage pour les moyens de paiement** : Écarté car `StripeClient` et `PayFastSdk` sont deux SDKs externes indépendants. La composition via l'Adapter a été privilégiée.
- **Un EventDispatcher complexe avec un container IoC** : Écarté car surdimensionné (over-engineering) pour un projet sans framework. Un tableau d'observateurs suffit amplement.

## 5. Ce que nous améliorerions avec plus de temps

- Remplacer les `float` du calcul tarifaire par une gestion monétaire entièrement décimale afin d'éviter les imprécisions d'arrondi.
- Remplacer le `RuntimeException` de paiement par une exception métier dédiée comme `PaymentFailedException`.
- Ajouter un dépôt persistant réel à la place de la persistance simulée par la sortie SQL.

## 6. Refactorings réalisés (Ticket #106)

1. **Extraction de la tarification (SRP)** : Isolé les règles de calcul et les réductions dans `PricingCalculator`.
2. **Encapsulation et suppression des accès directs** : Ajout des getters manquants (`getTicket()`, `getQuantity()`, `getCustomer()`, `getItems()`) sur l'ensemble des entités du domaine.
3. **Inversion des dépendances de paiement (DIP)** : Remplacé le couplage dur envers `StripeClient` par l'interface `PaymentProcessorInterface`.

## 7. Corrections et sécurisation finales

- **Tarification Strategy** : Extraction de `PricingStrategyInterface`, `StandardPricingStrategy` et `VipPricingStrategy`. Les prix négatifs sont rejetés et les résultats sont arrondis à deux décimales.
- **Pass 3 jours** : La remise fixe de 20 € est appliquée à tout Pass 3 jours VIP, y compris lorsque le sous-total est inférieur à 300 €.
- **Paiements** : `StripePaymentAdapter` et `PayFastPaymentAdapter` respectent les signatures réelles de leurs SDKs. PayFast reçoit un payload avec référence, montant en centimes et devise ZAR.
- **Échec de paiement** : `BookingService` ne persiste et ne notifie la réservation que lorsque le processeur retourne `true`.
- **Supervision** : `PaymentMonitoringDecorator` journalise le succès, l'échec, les exceptions, le montant et la durée du paiement sans modifier les SDKs.
- **Tests** : `TestRunner` centralise les assertions et retourne un code d'erreur si un test échoue. La suite couvre les seuils tarifaires, les notifications, les montants invalides et le monitoring des paiements.
