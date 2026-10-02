# Audit initial

## 1. Comportement observable

Lors de l'exécution via `php index.php`, la réservation calculait un total brut de 159.80 € (2 x 79.90 €). Le statut VIP appliquait une remise arbitraire de 10%, ramenant le total à 143.82 €, puis un paiement Stripe était simulé et un e-mail de confirmation était envoyé.

## 2. Problèmes identifiés

| # | Problème | Catégorie | Impact |

| 1 | `BookingService` centralise la tarification, le paiement et les notifications | Responsabilité (SRP) | Classe monolithique très difficile à maintenir et à faire évoluer |
| 2 | Utilisation de `if ($paymentMethod === 'stripe')` en dur | Couplage / OCP | Impossibilité d'ajouter PayFast sans modifier la méthode métier |
| 3 | SDK PayFast incompatible avec StripeClient | Interface / Couplage | Nécessite un pattern d'adaptation pour l'intégrer sans tout casser |
| 4 | Notifications et statistiques codées en dur dans le flux principal | Responsabilité / Couplage | Risque d'échec du paiement si une notification plante |
| 5 | Taux de réduction et chaînes de caractères magiques | Lisibilité / Dépassement | Code fragile et source de bugs lors des calculs tarifaires |
| 6 | Absence d'injection de dépendances et de double de test | Testabilité | Difficulté d'isoler la logique métier des services externes |

## 3. Nos trois priorités

1. **Isoler et sécuriser le calcul tarifaire** (Ticket 102) via une stratégie dédiée pour éviter les erreurs de facturation.
2. **Abstraire les moyens de paiement via une interface commune** (Ticket 103) pour intégrer PayFast proprement.
3. **Découpler les actions post-confirmation** (Ticket 104) grâce à un mécanisme d'événements/observateurs.

## 4. Risques avant refactoring

- Modifier la logique de calcul tarifaire sans tests de caractérisation risque d'impacter les clients VIP actuels.
- Coupler PayFast au service principal risque de briser le paiement Stripe existant
