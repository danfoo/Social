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

# Dashboard
$lang['fleet_dashboard']              = 'Tableau de bord';
$lang['fleet_dash_total_cost']        = 'Coût total';
$lang['fleet_dash_occupancy']         = 'Occupation (%s j)';
$lang['fleet_dash_occupancy_short']   = 'Occupation';
$lang['fleet_dash_per_vehicle']       = 'Coût par véhicule';
$lang['fleet_dash_cost_per_km']       = 'Coût / km';
$lang['fleet_dash_expense_evolution'] = 'Évolution des dépenses (12 mois)';
$lang['fleet_dash_cost_split']        = 'Répartition des coûts';
$lang['fleet_dash_recent_vehicles']   = '10 derniers véhicules';
$lang['fleet_search']                 = 'Rechercher...';
$lang['fleet_export']                 = 'Exporter CSV';

# Settings
$lang['fleet_settings']                     = 'Réglages';
$lang['fleet_set_billing']                  = 'Facturation & dépenses';
$lang['fleet_set_expense_category']         = 'Catégorie de dépense pour la flotte';
$lang['fleet_set_expense_category_help']    = 'Les entretiens, pleins, pièces et rappels chiffrés sont enregistrés dans cette catégorie de dépenses.';
$lang['fleet_set_invoice_due_days']         = 'Échéance facture (jours)';
$lang['fleet_set_invoice_due_days_help']    = 'Nombre de jours ajoutés à aujourd\'hui pour l\'échéance des factures créées depuis une location.';
$lang['fleet_set_occupancy_days']           = 'Fenêtre d\'occupation (jours)';
$lang['fleet_set_occupancy_days_help']      = 'Période glissante utilisée pour calculer le taux d\'occupation des véhicules sur le tableau de bord.';
$lang['fleet_set_driver_role']              = 'Rôle chauffeur';
$lang['fleet_dash_most_used']         = 'Véhicule le plus utilisé';
$lang['fleet_dash_most_expensive']    = 'Véhicule le plus coûteux';
$lang['fleet_dash_most_fuel']         = 'Plus gros budget carburant';
$lang['fleet_period_month']           = 'Mois';
$lang['fleet_period_quarter']         = 'Trimestre';
$lang['fleet_period_year']            = 'Année';
$lang['fleet_period_all']             = 'Tout';

# Part <-> maintenance link
$lang['fleet_link_maintenance']       = 'Rattacher à un entretien (facultatif)';
$lang['fleet_no_link']                = '— Aucun —';

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
$lang['fleet_next_service']           = 'Prochain entretien';

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
$lang['fleet_suppliers']              = 'Fournisseurs';
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

# Parts / articles inventory
$lang['fleet_parts_articles']         = 'Pièces / Articles';
$lang['fleet_part']                   = 'Pièce';
$lang['fleet_add_part']               = 'Ajouter une pièce';
$lang['fleet_part_name']              = 'Nom de la pièce / article';
$lang['fleet_reference']              = 'Référence';
$lang['fleet_quantity']               = 'Quantité';
$lang['fleet_unit_price']             = 'Prix d\'achat unitaire';
$lang['fleet_parts_count']            = 'Pièces (catalogue)';
$lang['fleet_parts_total_cost']       = 'Coût d\'achat total';
$lang['fleet_pstatus_installed']      = 'Montée';
$lang['fleet_pstatus_in_stock']       = 'En stock';
$lang['fleet_pstatus_ordered']        = 'Commandée';
$lang['fleet_pstatus_returned']       = 'Retournée';
$lang['fleet_log_part_added']         = 'Pièce achetée : %s';
$lang['fleet_log_part_assigned']      = 'Pièce affectée : %s (x%s)';
$lang['fleet_atype_part']             = 'Pièce';

# Parts catalog configuration
$lang['fleet_part_categories']        = 'Catégories de pièces';
$lang['fleet_part_units']             = 'Unités de pièces';

# Supplier accounting
$lang['fleet_supplier_invoice_no']    = 'N° facture fournisseur';
$lang['fleet_payment']                = 'Paiement';
$lang['fleet_paid']                   = 'Payé';
$lang['fleet_unpaid']                 = 'Non payé';
$lang['fleet_mark_paid']              = 'Marquer payé';
$lang['fleet_mark_unpaid']            = 'Marquer non payé';
$lang['fleet_payment_updated']        = 'Statut de paiement mis à jour';
$lang['fleet_acc_total']              = 'Total des achats';
$lang['fleet_acc_paid']               = 'Payé';
$lang['fleet_acc_unpaid']             = 'Restant dû';
$lang['fleet_supplier_orders']        = 'Commandes de pièces';
$lang['fleet_supplier_other_costs']   = 'Autres coûts liés';
$lang['fleet_billable']               = 'Refacturable à un client (dépense facturable)';
$lang['fleet_reinvoice_client']       = 'Client à refacturer';
$lang['fleet_paid_amount']            = 'Payé';
$lang['fleet_remaining']              = 'Restant dû';
$lang['fleet_partial']                = 'Partiel';
$lang['fleet_pay']                    = 'Payer';
$lang['fleet_record_payment']         = 'Enregistrer un paiement';
$lang['fleet_payment_amount']         = 'Montant';
$lang['fleet_payment_date']           = 'Date du paiement';
$lang['fleet_payment_mode']           = 'Mode de paiement';
$lang['fleet_payment_recorded']       = 'Paiement enregistré';
$lang['fleet_payment_invalid']        = 'Montant de paiement invalide';
$lang['fleet_purchase_order']         = 'BON DE COMMANDE';

# Parts inventory workflow
$lang['fleet_unit']                   = 'Unité';
$lang['fleet_min_stock']              = 'Stock minimum';
$lang['fleet_stock']                  = 'Stock';
$lang['fleet_in_stock']               = 'En stock';
$lang['fleet_low_stock']              = 'Stock faible';
$lang['fleet_catalog']                = 'Catalogue';
$lang['fleet_orders']                 = 'Commandes';
$lang['fleet_assignments_tab']        = 'Stock & affectations';
$lang['fleet_buy']                    = 'Acheter';
$lang['fleet_place_order']            = 'Passer la commande';
$lang['fleet_new_order']              = 'Nouvelle commande';
$lang['fleet_new_assignment']         = 'Affecter depuis le stock';
$lang['fleet_order']                  = 'Commande';
$lang['fleet_order_date']             = 'Date de commande';
$lang['fleet_receive']                = 'Réceptionner';
$lang['fleet_cancel_order']           = 'Annuler';
$lang['fleet_no_orders']              = 'Aucune commande pour le moment.';
$lang['fleet_no_assignments']         = 'Aucune affectation pour le moment.';
$lang['fleet_assigned_to']            = 'Affecté à';
$lang['fleet_assigned_to_other']      = 'Ou affecté à (autre)';
$lang['fleet_assignment_label']       = 'Affectation';
$lang['fleet_ostatus_ordered']        = 'Commandée';
$lang['fleet_ostatus_received']       = 'Reçue';
$lang['fleet_ostatus_cancelled']      = 'Annulée';
$lang['fleet_order_placed']           = 'Commande passée';
$lang['fleet_add_line']               = 'Ajouter une ligne';
$lang['fleet_lines_count']            = 'Articles';
$lang['fleet_order_no_lines']         = 'Ajoutez au moins une ligne d\'article';
$lang['fleet_order_received_done']    = 'Commande réceptionnée et ajoutée au stock';
$lang['fleet_order_cancelled']        = 'Commande annulée';
$lang['fleet_part_assigned_done']     = 'Pièce affectée depuis le stock';
$lang['fleet_not_enough_stock']       = 'Stock insuffisant (disponible : %s)';

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

# Driver profile
$lang['fleet_driver_profile']         = 'Fiche du chauffeur';
$lang['fleet_personal_info']          = 'Informations personnelles';
$lang['fleet_staff_account']          = 'Compte utilisateur';
$lang['fleet_age']                    = 'Âge';
$lang['fleet_years']                  = 'ans';
$lang['fleet_date_of_birth']          = 'Date de naissance';
$lang['fleet_national_id']            = 'N° pièce d\'identité';
$lang['fleet_blood_type']             = 'Groupe sanguin';
$lang['fleet_hire_date']              = 'Date d\'embauche';
$lang['fleet_emergency_contact']      = 'Contact d\'urgence';
$lang['fleet_emergency_phone']        = 'Téléphone d\'urgence';
$lang['fleet_employment_emergency']   = 'Emploi & urgence';
$lang['fleet_license']                = 'Permis de conduire';
$lang['fleet_license_number']         = 'N° de permis';
$lang['fleet_license_category']       = 'Catégorie de permis';
$lang['fleet_license_issue_date']     = 'Date de délivrance';
$lang['fleet_license_expiry']         = 'Date d\'expiration';
$lang['fleet_valid']                  = 'Valide';
$lang['fleet_expired']                = 'Expiré';
$lang['fleet_expiring_soon']          = 'Expire bientôt';
$lang['fleet_location']               = 'Lieu';
$lang['fleet_client']                 = 'Client';
$lang['fleet_yes']                    = 'Oui';
$lang['fleet_no']                     = 'Non';

# Driver accidents
$lang['fleet_accidents']              = 'Accidents';
$lang['fleet_accident']               = 'Accident / incident';
$lang['fleet_add_accident']           = 'Déclarer un accident';
$lang['fleet_no_accidents']           = 'Aucun accident enregistré';
$lang['fleet_severity']               = 'Gravité';
$lang['fleet_severity_minor']         = 'Léger';
$lang['fleet_severity_moderate']      = 'Modéré';
$lang['fleet_severity_severe']        = 'Grave';
$lang['fleet_at_fault']               = 'Responsable';
$lang['fleet_third_party']            = 'Tiers impliqué';

# Driver license reminders
$lang['fleet_license_reminders']            = 'Permis de conduire à renouveler';
$lang['fleet_license_due_notification']     = 'Permis de %s à renouveler (expire le %s)';
$lang['fleet_set_license_notify_days']      = 'Alerte permis (jours avant)';
$lang['fleet_set_license_notify_days_help'] = 'Nombre de jours avant l\'expiration d\'un permis de conduire à partir duquel un rappel est généré et le personnel notifié.';

# Planning & booking
$lang['fleet_planning']                       = 'Planning';
$lang['fleet_no_vehicles']                    = 'Aucun véhicule';
$lang['fleet_booking_conflict']               = 'Réservation impossible : ce véhicule est déjà réservé du %s au %s (location %s). Modifiez les dates ou le véhicule.';

# E-mail notifications
$lang['fleet_set_notifications']              = 'Notifications';
$lang['fleet_set_email_notifications']        = 'Activer les notifications par e-mail';
$lang['fleet_set_email_notifications_help']   = 'Envoie un e-mail (en plus de la notification interne) lorsqu\'un rappel de document véhicule ou de permis arrive à échéance.';
$lang['fleet_set_notification_emails']        = 'Destinataires supplémentaires';
$lang['fleet_set_notification_emails_help']   = 'Adresses e-mail supplémentaires à prévenir, séparées par une virgule. Le personnel autorisé à voir la flotte est déjà notifié.';

# Rental contract & inspection (état des lieux)
$lang['fleet_deposit']                  = 'Caution / dépôt de garantie';
$lang['fleet_without_driver']           = 'Sans chauffeur';
$lang['fleet_contract']                 = 'Contrat de location';
$lang['fleet_contract_lessor']          = 'Le loueur';
$lang['fleet_contract_lessee']          = 'Le locataire';
$lang['fleet_contract_read_approved']   = 'Lu et approuvé';
$lang['fleet_contract_terms']           = 'Le locataire reconnaît avoir reçu le véhicule désigné ci-dessus en bon état de marche et s\'engage à le restituer dans le même état, aux date et lieu convenus. Le carburant, les amendes, contraventions et tout dommage non couvert par l\'assurance restent à la charge du locataire. La caution pourra être retenue en tout ou partie en cas de dégâts constatés à la restitution. Le présent contrat est régi par le droit sénégalais.';
$lang['fleet_inspection']               = 'État des lieux';
$lang['fleet_inspections']              = 'États des lieux (départ / retour)';
$lang['fleet_inspection_checkout']      = 'État des lieux — Départ';
$lang['fleet_inspection_checkin']       = 'État des lieux — Retour';
$lang['fleet_inspection_save_first']    = 'Enregistrez d\'abord cet état des lieux pour pouvoir ajouter des photos.';
$lang['fleet_inspection_not_done']      = 'Non réalisé';
$lang['fleet_fuel_level']               = 'Niveau de carburant';
$lang['fleet_fuel_empty']               = 'Vide (0/8)';
$lang['fleet_fuel_full']                = 'Plein (8/8)';
$lang['fleet_exterior_condition']       = 'État extérieur';
$lang['fleet_interior_condition']       = 'État intérieur';
$lang['fleet_damages']                  = 'Dégâts constatés';
$lang['fleet_photos']                   = 'Photos';
$lang['fleet_upload']                   = 'Téléverser';

# Security deposit workflow
$lang['fleet_deposit_amount']           = 'Montant de la caution';
$lang['fleet_deposit_status_none']      = 'Non encaissée';
$lang['fleet_deposit_status_held']      = 'Encaissée';
$lang['fleet_deposit_status_returned']  = 'Restituée';
$lang['fleet_deposit_status_withheld']  = 'Retenue';
$lang['fleet_deposit_status_partial']   = 'Partiellement retenue';
$lang['fleet_deposit_held_date']        = 'Date d\'encaissement';
$lang['fleet_deposit_returned_date']    = 'Date de restitution';
$lang['fleet_deposit_withheld']         = 'Montant retenu';
$lang['fleet_deposit_returned_amount']  = 'Montant restitué';
$lang['fleet_deposit_hold']             = 'Encaisser la caution';
$lang['fleet_deposit_settle']           = 'Restituer / solder la caution';
$lang['fleet_deposit_closed']           = 'Caution soldée';
$lang['fleet_deposit_held_done']        = 'Caution encaissée';
$lang['fleet_deposit_settled_done']     = 'Caution soldée';
$lang['fleet_deposit_reason']           = 'Motif de la retenue';
$lang['fleet_deposit_withheld_help']    = 'Montant à conserver (dégâts, carburant, amendes...). Laissez 0 pour tout restituer.';

# Vehicle documents
$lang['fleet_documents']                = 'Documents';
$lang['fleet_document']                 = 'Document';
$lang['fleet_document_type']            = 'Type de document';
$lang['fleet_document_title']           = 'Libellé';
$lang['fleet_issue_date']               = 'Date de délivrance';
$lang['fleet_expiry_date']              = 'Date d\'expiration';
$lang['fleet_file']                     = 'Fichier';
$lang['fleet_document_uploaded']        = 'Document ajouté';
$lang['fleet_no_documents']             = 'Aucun document';
$lang['fleet_doctype_registration']     = 'Carte grise';
$lang['fleet_doctype_insurance']        = 'Attestation d\'assurance';
$lang['fleet_doctype_technical_inspection'] = 'Visite technique';
$lang['fleet_doctype_vignette']         = 'Vignette';
$lang['fleet_doctype_permit']           = 'Autorisation / licence';
$lang['fleet_doctype_other']            = 'Autre';

# Fines / contraventions (PV)
$lang['fleet_fines']                    = 'Amendes / PV';
$lang['fleet_fine']                     = 'Amende / contravention';
$lang['fleet_add_fine']                 = 'Ajouter une amende';
$lang['fleet_no_fines']                 = 'Aucune amende enregistrée';
$lang['fleet_fine_number']              = 'N° de PV';
$lang['fleet_fine_type']                = 'Type d\'infraction';
$lang['fleet_amount']                   = 'Montant';
$lang['fleet_rebill_client']            = 'Refacturer au client';
$lang['fleet_rebill']                   = 'Refacturer';
$lang['fleet_fine_invoice_title']       = 'Contravention %s';
$lang['fleet_log_fine_added']           = 'Amende ajoutée : %s';
$lang['fleet_atype_fine']               = 'Amende';
$lang['fleet_ftype_speeding']           = 'Excès de vitesse';
$lang['fleet_ftype_parking']            = 'Stationnement';
$lang['fleet_ftype_red_light']          = 'Feu rouge';
$lang['fleet_ftype_documents']          = 'Défaut de documents';
$lang['fleet_ftype_phone']              = 'Téléphone au volant';
$lang['fleet_ftype_other']              = 'Autre';
$lang['fleet_fstatus_pending']          = 'À régler';
$lang['fleet_fstatus_paid']             = 'Réglée';
$lang['fleet_fstatus_contested']        = 'Contestée';
$lang['fleet_fstatus_cancelled']        = 'Annulée';

# Vehicle archiving (soft delete)
$lang['fleet_archived']                 = 'Archivés';
$lang['fleet_active_vehicles']          = 'Véhicules actifs';
$lang['fleet_archive']                  = 'Désactiver / archiver';
$lang['fleet_restore']                  = 'Réactiver';
$lang['fleet_archive_confirm']          = 'Désactiver ce véhicule ? Il sera archivé (son historique est conservé) et pourra être réactivé à tout moment.';
$lang['fleet_vehicle_archived']         = 'Véhicule archivé';
$lang['fleet_vehicle_restored']         = 'Véhicule réactivé';
$lang['fleet_log_vehicle_archived']     = 'Véhicule archivé (désactivé)';
$lang['fleet_log_vehicle_restored']     = 'Véhicule réactivé';

# Configurable contract terms
$lang['fleet_set_contract_terms']       = 'Termes du contrat (bas du contrat)';
$lang['fleet_set_contract_terms_help']  = 'Texte juridique imprimé en bas du contrat de location PDF. Laissez vide pour utiliser le texte par défaut.';

# Fuel dashboard filters
$lang['fleet_period_day']               = 'Jour';
$lang['fleet_period_week']              = 'Semaine';
$lang['fleet_all_stations']             = 'Toutes les stations';
$lang['fleet_fuel_by_station']          = 'Consommation par station';
$lang['fleet_no_station']               = 'Sans station';
$lang['fleet_no_data']                  = 'Aucune donnée pour cette période';
$lang['fleet_specific_date']            = 'Date précise';
$lang['fleet_all_fuel_types']          = 'Tous les carburants';

# Per-role feature access
$lang['fleet_set_role_access']          = 'Accès aux fonctionnalités par rôle';
$lang['fleet_set_role_access_help']     = 'Cochez les fonctionnalités visibles pour chaque rôle. Un rôle dont aucune case n\'est cochée n\'a accès à rien ; un rôle non configuré (toutes cochées) voit tout. Les administrateurs voient toujours tout. La permission de base « Gestion de flotte » (voir) reste requise pour accéder au module.';
$lang['fleet_toggle_all']               = 'Tout / rien';
$lang['fleet_role']                    = 'Rôle';
