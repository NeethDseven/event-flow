# EventFlow

Projet final B2 - Design Patterns & Clean Code.

## Prérequis

PHP 8.1 ou supérieur.

## Lancer l'application

```bash
php index.php
```

## Lancer les tests fournis

```bash
php tests/characterization.php
```

## Résumé du projet

EventFlow est une application PHP de gestion de réservation qui applique les principes de conception et les design patterns étudiés en cours.

### Ce qui a été mis en place

- Calcul tarifaire séparé du service de réservation via `PricingCalculator` et les stratégies `StandardPricingStrategy` / `VipPricingStrategy`
- Support de plusieurs moyens de paiement via les interfaces et adaptateurs (`StripePaymentAdapter`, `PayFastPaymentAdapter`)
- Confirmation de réservation décorrélée grâce au pattern Observer (`BookingObserverInterface`)
- Monitoring des paiements avec `PaymentMonitoringDecorator`
- Séparation des tâches de persistance, de notification et de suivi analytics
- Centralisation des valeurs métier pour améliorer la lisibilité et la maintenance

### Design patterns utilisés

- Adapter
- Observer
- Strategy
- Decorator

### Vérification

Le projet a été validé avec les tests fournis :

- `php tests/characterization.php` → 9 tests OK, 0 échec
- `php index.php` → exécution du flux de réservation confirmée

## Important

Le code de départ est volontairement imparfait.

Les tests fournis décrivent le comportement initial. Certaines règles doivent ensuite évoluer conformément au sujet. Il vous appartient donc d'adapter les tests lorsque le comportement métier demandé évolue.

Ne modifiez pas `src/PayFastSdk.php`.

Le projet ne contient volontairement aucun framework ni dépendance externe.
