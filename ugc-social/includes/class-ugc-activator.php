<?php
if (!defined('ABSPATH')) exit;

class UGC_Activator {

    public static function activate() {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $charset_collate = $wpdb->get_charset_collate();

        $profiles = $wpdb->prefix . 'ugc_profiles';
        $likes    = $wpdb->prefix . 'ugc_likes';
        $reports  = $wpdb->prefix . 'ugc_reports';

        $sql_profiles = "CREATE TABLE $profiles (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            visitor_uuid VARCHAR(64) NOT NULL,
            display_name VARCHAR(60) NOT NULL,
            avatar_attachment_id BIGINT UNSIGNED NULL,
            pin_hash VARCHAR(255) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY visitor_uuid (visitor_uuid),
            UNIQUE KEY display_name (display_name)
        ) $charset_collate;";

        $sql_likes = "CREATE TABLE $likes (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT UNSIGNED NOT NULL,
            profile_id BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY post_profile (post_id, profile_id),
            KEY post_id (post_id),
            KEY profile_id (profile_id)
        ) $charset_collate;";

        $sql_reports = "CREATE TABLE $reports (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT UNSIGNED NOT NULL,
            reporter_profile_id BIGINT UNSIGNED NULL,
            reason VARCHAR(120) NOT NULL,
            details TEXT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'open',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY status (status)
        ) $charset_collate;";

        dbDelta($sql_profiles);
        dbDelta($sql_likes);
        dbDelta($sql_reports);

        // Cron schedule hourly
        if (!wp_next_scheduled('ugc_social_recalc_trending')) {
            wp_schedule_event(time() + 300, 'hourly', 'ugc_social_recalc_trending');
        }

        // Flush rewrite for CPT
        UGC_CPT::register();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        // Keep data; just remove cron.
        $timestamp = wp_next_scheduled('ugc_social_recalc_trending');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ugc_social_recalc_trending');
        }
        flush_rewrite_rules();
    }
}
