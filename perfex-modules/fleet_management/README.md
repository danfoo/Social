# Fleet Management — module Perfex CRM

Gestion de flotte pour la **location de véhicules avec ou sans chauffeur** :
véhicules, entretiens, rappels (assurance, visite technique, vignette, carte
grise…), affectation des chauffeurs et **facturation des locations via une
facture Perfex native**.

Compatible **Perfex CRM ≥ 2.3** (testé pour la lignée 3.4.x).

## Fonctionnalités

- **Véhicules** : fiche complète (immatriculation, marque/modèle, VIN, carburant,
  boîte, places, kilométrage, tarifs jour avec/sans chauffeur, assurance, statut).
  La **catégorie**, la **marque** et le **modèle** se choisissent dans des listes
  gérées sous **Configuration** (le modèle se filtre selon la marque). La
  **compagnie d'assurance** provient des **fournisseurs de type « assurance »**.
- **Configuration (catalogue)** : gestion des **catégories**, **marques** et
  **modèles** de véhicules (un modèle appartient à une marque).
- **Dépenses automatiques** : tout coût lié à un véhicule (entretien, plein de
  carburant, rappel chiffré comme l'assurance ou la vignette) est enregistré
  automatiquement comme **dépense dans le module Dépenses natif de Perfex**,
  dans une catégorie dédiée « Fleet management ». La dépense est **synchronisée**
  (créée, mise à jour, supprimée) avec la fiche source via un lien `expense_id`.
- **Historique du véhicule** : chaque fiche véhicule expose un onglet
  **Historique** retraçant toutes les actions (création, affectation de
  chauffeur, pleins de carburant, mises à jour du kilométrage, entretiens,
  pièces changées, rappels, locations, factures) avec date et auteur.
- **Entretiens** : historique par véhicule (vidange, révision, pneus, freins,
  réparation, carrosserie…), coût, kilométrage, prochain entretien, fournisseur,
  **pièces changées** et **photos jointes** (chacune avec sa date de prise).
- **Carburant** : suivi des pleins (litres, prix/litre, coût total calculé auto,
  kilométrage, station-service, chauffeur, plein complet) + statistiques
  (nombre de pleins, litres et coût cumulés). Le km met à jour l'odomètre du
  véhicule.
- **Fournisseurs** : carnet d'adresses (garages, assurances, stations-service,
  partenaires, pièces…) avec contact, téléphone, email, TVA, adresse. Reliable
  aux entretiens, rappels et pleins de carburant.
- **Rappels + notifications** : échéances de documents avec notification
  automatique X jours avant via le **cron Perfex** (notification interne aux
  membres du staff autorisés). Badges « bientôt » / « expiré ».
- **Chauffeurs** : ce sont des membres du staff Perfex portant le rôle dédié
  **« Chauffeur »** (créé automatiquement à l'activation). Affectation d'un
  chauffeur à un véhicule avec historique.
- **Locations** : réservation (véhicule, client, période, avec/sans chauffeur,
  km départ/retour, lieux). Le nombre de jours et le total sont calculés
  automatiquement.
- **Facturation** : bouton **« Créer la facture »** → génère une **facture
  Perfex en brouillon** (1 ligne, qté = nb de jours, tarif = tarif/jour) liée à
  la location, à compléter (taxes/remises) avant envoi.

## Installation

1. Copier le dossier dans `modules/` de votre installation Perfex :

   ```
   modules/fleet_management/
   ```

2. **Setup → Modules → Fleet Management → Activate**. L'activation crée les
   tables, le rôle staff **« Chauffeur »** et les permissions.

3. Donner la permission **Fleet Management** (view/create/edit/delete) aux rôles
   concernés dans **Setup → Roles**.

4. Le menu **Gestion de flotte** apparaît dans la barre latérale.

## Déclarer un chauffeur

Un chauffeur = un membre du staff avec le rôle **« Chauffeur »**.
Dans **Setup → Staff**, créez/éditez un membre et assignez-lui ce rôle ; il
apparaîtra alors dans la liste **Chauffeurs** et dans les sélecteurs de chauffeur.

## Rappels automatiques

Le module s'accroche au hook `app_cron`. Assurez-vous que le **cron Perfex** est
configuré (Setup → Settings → Cron Job). Chaque rappel `actif` déclenche une
notification interne lorsque `date d'échéance − (jours avant)` est atteinte, une
seule fois (ré-armé si vous modifiez l'échéance).

## Facturation d'une location

Depuis la liste **Locations** ou la fiche location, cliquez sur **Créer la
facture**. Une facture brouillon est créée pour le client et la période, puis
liée à la location (le bouton disparaît une fois la facture créée). Vous êtes
redirigé vers la facture Perfex pour ajuster taxes/remises et l'envoyer.

## Structure

```
fleet_management/
├── fleet_management.php              # module, menu, permissions, cron
├── install.php / uninstall.php       # schéma BDD, rôle Chauffeur, options
├── controllers/                      # Vehicles, Rentals, Maintenance, Reminders, Drivers
├── models/Fleet_management_model.php # logique métier + création de facture
├── helpers/fleet_management_helper.php
├── views/                            # vehicles, rentals, maintenance, reminders, drivers
└── language/english|french/
```

Tables créées : `fleet_vehicles`, `fleet_maintenance`, `fleet_reminders`,
`fleet_assignments`, `fleet_rentals`, `fleet_suppliers`, `fleet_fuel_logs`
(préfixe Perfex appliqué).

> Mise à niveau : après chaque nouvelle version, **désactivez puis réactivez** le
> module. `install.php` est idempotent : il crée les nouvelles tables
> (`fleet_suppliers`, `fleet_fuel_logs`, `fleet_categories`, `fleet_brands`,
> `fleet_models`) et ajoute les colonnes manquantes sans toucher aux données
> existantes.

### Pour commencer

1. Allez d'abord dans **Configuration** pour créer vos **catégories**, **marques**
   et **modèles**.
2. Créez vos **fournisseurs** (notamment ceux de type « assurance » et
   « station-service »).
3. Vous pouvez alors créer des véhicules : catégorie, marque/modèle et compagnie
   d'assurance sont proposés sous forme de listes déroulantes.

## Notes / limites connues

- À l'édition d'un entretien/rappel via la fenêtre modale, le **champ date n'est
  pas pré-rempli** (re-sélectionnez la date) ; les autres champs le sont.
- La facture est créée **en brouillon** et sans taxe par défaut : ajoutez la TVA
  voulue sur la facture Perfex. (La logique est centralisée dans
  `Fleet_management_model::create_invoice()` si vous voulez appliquer une taxe
  par défaut.)
- La désactivation conserve les données ; la **désinstallation** supprime les
  tables et le rôle option.
