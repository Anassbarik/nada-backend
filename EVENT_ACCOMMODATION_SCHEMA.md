# SCHÉMA STRUCTURE - EVENT ACCOMMODATION PACKAGE
## (Séminaire / Expo / Foire - Type Hébergement)

```
EVENT ACCOMMODATION PACKAGE (Hébergement pour Événements)
│
├── 1. ACCOMMODATION (ÉVÉNEMENT HÉBERGEMENT)
│   ├── Nom de l'accommodation / événement
│   ├── Slug (URL-friendly identifier)
│   ├── Lieu / Venue
│   ├── Localisation (Ville / Pays)
│   ├── URL Google Maps
│   ├── Dates (début / fin)
│   ├── Site web officiel (optionnel)
│   ├── Logo organisateur
│   ├── Logo de l'accommodation
│   ├── Bannière / Image principale
│   ├── Description (multilingue: EN / FR)
│   ├── Liens menu (JSON array)
│   ├── Statut (draft / published / archived)
│   ├── Organisateur ID (référence utilisateur)
│   └── Créé par (référence admin)
│
├── 2. HOTEL / HÉBERGEMENT
│   ├── ID Accommodation (référence)
│   ├── Nom de l'hôtel
│   ├── Slug (URL-friendly identifier)
│   ├── Catégorie / Étoiles (3*, 4*, 5*)
│   ├── Localisation (adresse)
│   ├── URL de localisation
│   ├── Durée (en jours)
│   ├── Description (multilingue: EN / FR)
│   ├── Inclusions (JSON array)
│   │   ├── Petit-déjeuner
│   │   ├── Wi-Fi
│   │   ├── Parking
│   │   ├── Piscine
│   │   ├── Spa
│   │   └── Autres services
│   ├── Site web de l'hôtel
│   ├── Note / Rating (décimal)
│   ├── Nombre d'avis
│   ├── Statut (active / inactive)
│   ├── Images (relation HasMany)
│   │   ├── URL image
│   │   ├── Texte alternatif
│   │   ├── Ordre d'affichage
│   │   └── Statut (active / inactive)
│   └── Créé par (référence admin)
│
├── 3. PACKAGE HÔTEL (OFFRE HÉBERGEMENT)
│   ├── ID Hôtel (référence)
│   ├── Nom du package
│   ├── Type de chambre
│   │   ├── Single (1 personne)
│   │   ├── Double / Twin (2 personnes)
│   │   ├── Triple (3 personnes)
│   │   └── Suite / Familiale (4+ personnes)
│   ├── Dates séjour
│   │   ├── Check-in (date)
│   │   └── Check-out (date)
│   ├── Nombre d'occupants
│   ├── Tarification
│   │   ├── Prix HT (hors taxes)
│   │   ├── Prix TTC (avec TVA 20%)
│   │   └── Devise (MAD par défaut)
│   ├── Disponibilité
│   │   ├── Quantité totale de chambres
│   │   ├── Chambres restantes
│   │   └── Disponibilité (booléen)
│   └── Créé par (référence admin)
│
├── 4. CLIENT / RÉSERVATION
│   ├── Informations client
│   │   ├── Nom complet
│   │   ├── Société / Entreprise
│   │   ├── Email
│   │   ├── Téléphone
│   │   └── Instructions spéciales
│   ├── Résidents (occupants de la chambre)
│   │   ├── Résident 1 (nom)
│   │   ├── Résident 2 (nom)
│   │   ├── Résident 3 (nom)
│   │   └── Le réservateur est-il résident ? (Oui / Non)
│   ├── Invité (si différent du réservateur)
│   │   ├── Nom invité
│   │   ├── Email invité
│   │   └── Téléphone invité
│   ├── Demandes spéciales
│   └── Nombre de participants
│
├── 5. TRANSPORT AÉRIEN (OPTIONNEL)
│   ├── Numéro de vol
│   ├── Date de vol
│   ├── Heure de vol
│   ├── Aéroport (départ / arrivée)
│   └── Informations complémentaires
│
├── 6. DATES & SÉJOUR
│   ├── Date d'arrivée (check-in)
│   ├── Date de départ (check-out)
│   ├── Nombre de nuits (calculé automatiquement)
│   └── Durée totale du séjour
│
├── 7. TARIFICATION & PAIEMENT
│   ├── Prix total du package
│   ├── Méthode de paiement
│   │   ├── Portefeuille (wallet)
│   │   ├── Virement bancaire
│   │   └── Mixte (portefeuille + banque)
│   ├── Répartition paiement
│   │   ├── Montant portefeuille
│   │   └── Montant bancaire
│   ├── Taxes & frais
│   │   ├── TVA (20%)
│   │   └── Frais de service (si applicable)
│   └── Conditions de paiement
│
├── 8. STATUT RÉSERVATION
│   ├── Statuts disponibles
│   │   ├── Brouillon (draft)
│   │   ├── En attente (pending)
│   │   ├── Confirmé (confirmed)
│   │   ├── Annulé (cancelled)
│   │   └── Remboursé (refunded)
│   ├── Référence de réservation
│   │   └── Format: BOOK-YYYYMMDD-XXX
│   ├── Gestion automatique
│   │   ├── Mise à jour disponibilité chambres
│   │   ├── Confirmation auto (paiement wallet complet)
│   │   └── Remboursement portefeuille (si refunded)
│   └── Historique des modifications
│
├── 9. REMBOURSEMENTS
│   ├── Montant remboursé
│   ├── Date de remboursement
│   ├── Notes de remboursement
│   ├── Méthode de remboursement
│   │   ├── Portefeuille (crédit automatique)
│   │   └── Virement bancaire
│   └── Impact sur disponibilité
│       └── Réintégration chambre disponible
│
├── 10. SERVICES ADDITIONNELS
│   ├── Transferts aéroport
│   │   ├── Aéroport → Hôtel
│   │   └── Hôtel → Aéroport
│   ├── Navettes
│   │   └── Hôtel ↔ Lieu événement
│   ├── Services hôtel
│   │   ├── Petit-déjeuner (inclus / optionnel)
│   │   ├── Wi-Fi
│   │   ├── Parking
│   │   └── Autres (selon inclusions)
│   └── Services personnalisés
│       ├── City tour
│       ├── Guide / Interprète
│       └── Autres demandes
│
├── 11. AÉROPORTS ASSOCIÉS
│   ├── Liste des aéroports
│   │   ├── Nom aéroport
│   │   ├── Code IATA
│   │   ├── Ville
│   │   ├── Pays
│   │   ├── Distance depuis hôtel
│   │   └── Ordre d'affichage
│   └── Gestion des transferts
│
├── 12. CONTENU MULTIMÉDIA
│   ├── Images hôtel
│   │   ├── Image principale
│   │   ├── Galerie d'images
│   │   ├── Ordre d'affichage
│   │   └── Statut (active / inactive)
│   ├── Contenu événement
│   │   ├── Pages de contenu (multilingue)
│   │   ├── Sections (JSON)
│   │   ├── Images hero
│   │   └── Types de pages
│   └── Documents
│       ├── Brochures
│       ├── Plans
│       └── Guides
│
├── 13. DOCUMENTS GÉNÉRÉS
│   ├── Voucher (Bon de séjour)
│   │   ├── Référence unique
│   │   ├── Détails réservation
│   │   ├── Informations hôtel
│   │   ├── Instructions check-in
│   │   └── Contact support
│   ├── Facture (Invoice)
│   │   ├── Numéro facture
│   │   ├── Détails tarification
│   │   ├── TVA
│   │   ├── Méthode paiement
│   │   └── Statut paiement
│   └── Emails automatiques
│       ├── Confirmation réservation
│       ├── Notification admin
│       ├── Envoi voucher
│       └── Envoi facture
│
├── 14. GESTION DES PERMISSIONS
│   ├── Super-admin
│   │   └── Accès complet (CRUD)
│   ├── Admin régulier
│   │   ├── Création propres accommodations
│   │   ├── Édition propres créations
│   │   └── Sous-permissions (accordées par super-admin)
│   ├── Organisateur
│   │   └── Accès lecture (si assigné)
│   └── Permissions ressources
│       ├── Accommodation
│       ├── Hôtel
│       └── Package
│
├── 15. STATISTIQUES & RAPPORTS
│   ├── Réservations par accommodation
│   ├── Taux d'occupation
│   ├── Revenus par période
│   ├── Hôtels les plus réservés
│   ├── Packages populaires
│   └── Analyse des remboursements
│
└── 16. INTÉGRATIONS & SYSTÈMES
    ├── Portefeuille utilisateur (Wallet)
    │   ├── Solde
    │   ├── Historique transactions
    │   └── Crédit automatique (remboursements)
    ├── Système de notifications
    │   ├── Email
    │   ├── Notifications admin
    │   └── Confirmations client
    ├── API REST
    │   ├── Liste accommodations
    │   ├── Détails hôtel
    │   ├── Packages disponibles
    │   ├── Création réservation
    │   └── Statut réservation
    └── Gestion fichiers
        ├── Stockage images
        ├── Génération PDF (vouchers, factures)
        └── Upload sécurisé
```

## RELATIONS ENTRE ENTITÉS

```
Accommodation (1) ──< (N) Hotel
Hotel (1) ──< (N) Package
Package (1) ──< (N) Booking
Accommodation (1) ──< (N) Booking
Hotel (1) ──< (N) Booking
Accommodation (1) ──< (N) Airport
Accommodation (1) ──< (N) AccommodationContent
Hotel (1) ──< (N) HotelImage
Booking (1) ──< (1) Invoice
Booking (1) ──< (1) Voucher
User (1) ──< (N) Booking
User (1) ──< (1) Wallet
```

## FLUX DE RÉSERVATION

```
1. Sélection Accommodation
   ↓
2. Choix Hôtel
   ↓
3. Sélection Package (type chambre, dates)
   ↓
4. Vérification disponibilité
   ↓
5. Saisie informations client
   ├── Informations réservateur
   ├── Informations résidents
   └── Informations vol (optionnel)
   ↓
6. Choix méthode paiement
   ├── Portefeuille (si solde suffisant)
   ├── Virement bancaire
   └── Mixte
   ↓
7. Confirmation réservation
   ├── Génération référence
   ├── Mise à jour disponibilité
   ├── Débit portefeuille (si applicable)
   └── Envoi emails
   ↓
8. Génération documents
   ├── Voucher
   └── Facture
```

## RÈGLES MÉTIER IMPORTANTES

1. **Disponibilité automatique** : Décrémente lors de confirmation, incrémente lors d'annulation/remboursement
2. **Prix TTC** : Calculé automatiquement (Prix HT × 1.20)
3. **Confirmation auto** : Si paiement wallet complet, statut passe à "confirmed"
4. **Remboursement wallet** : Crédit automatique du portefeuille lors du statut "refunded"
5. **Unicité slug** : Génération automatique avec gestion des doublons
6. **Multilingue** : Support EN/FR pour descriptions et contenus
7. **Permissions hiérarchiques** : Accès basé sur création et sous-permissions
8. **Gestion images** : Support multiple images avec ordre et statut
9. **Références uniques** : Format standardisé pour bookings (BOOK-YYYYMMDD-XXX)

