<?php

defined('BASEPATH') or exit('No direct script access allowed');

# Module & menu
$lang['fleet_management']              = 'Gestion de flotte';
$lang['fleet_vehicles']               = 'Véhicules';
$lang['fleet_rentals']                = 'Locations';
$lang['fleet_maintenance']            = 'Entretiens';
$lang['fleet_reminders']              = 'Rappels';
$lang['fleet_drivers']                = 'Chauffeurs';

# Vehicle
$lang['fleet_vehicle']                = 'Véhicule';
$lang['fleet_add_vehicle']            = 'Ajouter un véhicule';
$lang['fleet_edit_vehicle']           = 'Modifier le véhicule';
$lang['fleet_vehicle_name']           = 'Nom / libellé';
$lang['fleet_plate']                  = 'Immatriculation';
$lang['fleet_brand']                  = 'Marque';
$lang['fleet_model']                  = 'Modèle';
$lang['fleet_year']                   = 'Année';
$lang['fleet_category']               = 'Catégorie';
$lang['fleet_vin']                    = 'N° de châssis (VIN)';
$lang['fleet_color']                  = 'Couleur';
$lang['fleet_fuel_type']              = 'Carburant';
$lang['fleet_transmission']           = 'Boîte de vitesses';
$lang['fleet_seats']                  = 'Places';
$lang['fleet_odometer']               = 'Kilométrage (km)';
$lang['fleet_daily_rate']             = 'Tarif journalier';
$lang['fleet_daily_rate_with_driver'] = 'Tarif journalier (avec chauffeur)';
$lang['fleet_purchase_date']          = 'Date d\'achat';
$lang['fleet_purchase_price']         = 'Prix d\'achat';
$lang['fleet_insurance_company']      = 'Compagnie d\'assurance';
$lang['fleet_insurance_policy']       = 'N° de police d\'assurance';
$lang['fleet_notes']                  = 'Notes';
$lang['fleet_status']                 = 'Statut';
$lang['fleet_phone']                  = 'Téléphone';

# Catalog (categories / brands / models)
$lang['fleet_library']                = 'Configuration';
$lang['fleet_categories']             = 'Catégories';
$lang['fleet_brands']                 = 'Marques';
$lang['fleet_models']                 = 'Modèles';
$lang['fleet_model_label']            = 'Modèle';

# Vehicle statuses
$lang['fleet_status_available']       = 'Disponible';
$lang['fleet_status_rented']          = 'En location';
$lang['fleet_status_maintenance']     = 'En entretien';
$lang['fleet_status_out_of_service']  = 'Hors service';

# Rental statuses
$lang['fleet_status_reserved']        = 'Réservée';
$lang['fleet_status_ongoing']         = 'En cours';
$lang['fleet_status_completed']       = 'Terminée';
$lang['fleet_status_cancelled']       = 'Annulée';

# Rentals
$lang['fleet_rental']                 = 'Location';
$lang['fleet_add_rental']             = 'Nouvelle location';
$lang['fleet_edit_rental']            = 'Modifier la location';
$lang['fleet_period']                 = 'Période';
$lang['fleet_days']                   = 'Jours';
$lang['fleet_total']                  = 'Total';
$lang['fleet_date_start']             = 'Date de début';
$lang['fleet_date_end']               = 'Date de fin';
$lang['fleet_with_driver']            = 'Avec chauffeur';
$lang['fleet_driver']                 = 'Chauffeur';
$lang['fleet_odometer_start']         = 'Km au départ';
$lang['fleet_odometer_end']           = 'Km au retour';
$lang['fleet_pickup_location']        = 'Lieu de prise en charge';
$lang['fleet_return_location']        = 'Lieu de restitution';
$lang['fleet_create_invoice']         = 'Créer la facture';
$lang['fleet_invoice_created']        = 'Facture (brouillon) créée avec succès';
$lang['fleet_invoice_create_failed']  = 'Impossible de créer la facture';
$lang['fleet_invoice_item_title']     = 'Location de véhicule — %s';
$lang['fleet_invoice_item_period']    = 'Période de location : du %s au %s';
$lang['fleet_unit_day']               = 'jour(s)';

# Maintenance
$lang['fleet_add_maintenance']        = 'Ajouter un entretien';
$lang['fleet_maintenance_record']     = 'Fiche d\'entretien';
$lang['fleet_type']                   = 'Type';
$lang['fleet_service_date']           = 'Date d\'intervention';
$lang['fleet_cost']                   = 'Coût';
$lang['fleet_provider']               = 'Garage / prestataire';
$lang['fleet_next_service_date']      = 'Prochain entretien (date)';
$lang['fleet_next_service_odometer']  = 'Prochain entretien (km)';
$lang['fleet_description']            = 'Description';
$lang['fleet_maintenance_in_global']  = 'Gérez tous les entretiens dans';

$lang['fleet_mtype_oil_change']       = 'Vidange';
$lang['fleet_mtype_revision']         = 'Révision';
$lang['fleet_mtype_tires']            = 'Pneus';
$lang['fleet_mtype_brakes']           = 'Freins';
$lang['fleet_mtype_repair']           = 'Réparation';
$lang['fleet_mtype_bodywork']         = 'Carrosserie';
$lang['fleet_mtype_other']            = 'Autre';

# Reminders
$lang['fleet_add_reminder']           = 'Ajouter un rappel';
$lang['fleet_reminder']               = 'Rappel';
$lang['fleet_reminder_title']         = 'Intitulé';
$lang['fleet_due_date']               = 'Date d\'échéance';
$lang['fleet_notify_days']            = 'Notifier X jours avant';
$lang['fleet_reminder_expired']       = 'Expiré';
$lang['fleet_reminder_soon']          = 'Dans %s jour(s)';
$lang['fleet_reminder_ok']            = 'Dans %s jour(s)';
$lang['fleet_reminder_due_notification'] = 'Rappel flotte : %s (échéance %s)';

$lang['fleet_rtype_insurance']            = 'Assurance';
$lang['fleet_rtype_technical_inspection'] = 'Visite technique / contrôle technique';
$lang['fleet_rtype_vignette']             = 'Vignette / taxe';
$lang['fleet_rtype_registration']         = 'Carte grise';
$lang['fleet_rtype_service']              = 'Entretien programmé';
$lang['fleet_rtype_other']                = 'Autre';

# Drivers
$lang['fleet_drivers_help']           = 'Les chauffeurs sont des membres du staff Perfex possédant le rôle dédié « Chauffeur ». Attribuez ce rôle à un membre du staff pour qu\'il apparaisse ici.';
$lang['fleet_add_staff_member']       = 'Ajouter un membre du staff';

# Suppliers
$lang['fleet_supplier']               = 'Fournisseur';
$lang['fleet_add_supplier']           = 'Ajouter un fournisseur';
$lang['fleet_supplier_name']          = 'Nom';
$lang['fleet_contact_name']           = 'Nom du contact';
$lang['fleet_website']                = 'Site web';
$lang['fleet_vat']                    = 'N° de TVA';
$lang['fleet_address']                = 'Adresse';

$lang['fleet_stype_garage']           = 'Garage';
$lang['fleet_stype_insurance']        = 'Compagnie d\'assurance';
$lang['fleet_stype_fuel_station']     = 'Station-service';
$lang['fleet_stype_rental_partner']   = 'Partenaire de location';
$lang['fleet_stype_parts']            = 'Fournisseur de pièces';
$lang['fleet_stype_other']            = 'Autre';

# Fuel
$lang['fleet_fuel']                   = 'Carburant';
$lang['fleet_fuel_log']               = 'Plein de carburant';
$lang['fleet_add_fuel']               = 'Ajouter un plein';
$lang['fleet_date']                   = 'Date';
$lang['fleet_liters']                 = 'Litres';
$lang['fleet_price_per_liter']        = 'Prix / litre';
$lang['fleet_total_cost']             = 'Coût total';
$lang['fleet_station']                = 'Station-service';
$lang['fleet_full_tank']              = 'Plein complet';
$lang['fleet_fuel_type']              = 'Type de carburant';
$lang['fleet_fuel_entries']           = 'Pleins';
$lang['fleet_fuel_total_liters']      = 'Litres au total';
$lang['fleet_fuel_total_cost']        = 'Coût total';
$lang['fleet_fuel_total_hint']        = 'Laissez le coût total vide pour le calculer automatiquement (litres × prix au litre).';

$lang['fleet_fuel_diesel']            = 'Diesel';
$lang['fleet_fuel_petrol']            = 'Essence';
$lang['fleet_fuel_lpg']               = 'GPL';
$lang['fleet_fuel_electric']          = 'Électrique';
$lang['fleet_fuel_hybrid']            = 'Hybride';
$lang['fleet_fuel_other']             = 'Autre';

# Vehicle history (activity timeline)
$lang['fleet_history']                = 'Historique';
$lang['fleet_history_empty']          = 'Aucune activité enregistrée pour ce véhicule.';
$lang['fleet_atype_vehicle']          = 'Véhicule';
$lang['fleet_atype_assignment']       = 'Chauffeur';
$lang['fleet_atype_maintenance']      = 'Entretien';
$lang['fleet_atype_photo']            = 'Photo';
$lang['fleet_atype_reminder']         = 'Rappel';
$lang['fleet_atype_fuel']             = 'Carburant';
$lang['fleet_atype_odometer']         = 'Kilométrage';
$lang['fleet_atype_rental']           = 'Location';
$lang['fleet_atype_invoice']          = 'Facture';
$lang['fleet_atype_other']            = 'Activité';

$lang['fleet_log_vehicle_created']    = 'Véhicule créé';
$lang['fleet_log_odometer']           = 'Kilométrage mis à jour de %s à %s km';
$lang['fleet_log_driver_assigned']    = 'Chauffeur %s affecté';
$lang['fleet_log_assignment_ended']   = 'Affectation de %s terminée';
$lang['fleet_log_maintenance']        = 'Entretien : %s';
$lang['fleet_log_reminder_added']     = 'Rappel ajouté : %s';
$lang['fleet_log_fuel']               = 'Plein : %s L';
$lang['fleet_log_rental_created']     = 'Location créée (#%s)';
$lang['fleet_log_invoice_created']    = 'Facture %s créée';
$lang['fleet_log_photo_added']        = 'Photo ajoutée (%s)';

# Maintenance parts & photos
$lang['fleet_parts']                  = 'Pièces changées';
$lang['fleet_parts_hint']             = 'Listez les pièces remplacées (une par ligne)';
$lang['fleet_maintenance_photos']     = 'Photos de l\'entretien';
$lang['fleet_photo']                  = 'Photo';
$lang['fleet_photo_taken_date']       = 'Date de la photo';
$lang['fleet_photo_upload']           = 'Téléverser';
$lang['fleet_photo_uploaded']         = 'Photo téléversée avec succès';
$lang['fleet_no_photos']              = 'Aucune photo jointe pour le moment.';

# Assignments
$lang['fleet_driver_assigned']        = 'Chauffeur affecté au véhicule';
$lang['fleet_assignment_ended']       = 'Affectation terminée';
$lang['fleet_assign']                 = 'Affecter';
$lang['fleet_assignment_start']       = 'Date de début';
$lang['fleet_assignment_end']         = 'Date de fin';
$lang['fleet_ended']                  = 'Terminée';
$lang['fleet_end_assignment']         = 'Terminer';
