<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Edu_Analytics_Database {

    /**
     * Create or update analytics events table.
     */
    public static function create_table() {

        global $wpdb;

        $table_name      = $wpdb->prefix . 'edu_analytics_events';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            event_uuid char(36) NOT NULL,
            enrollment_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            course_id bigint(20) unsigned NOT NULL,
            course_title varchar(255) NOT NULL,
            event_type varchar(50) NOT NULL,
            occurred_at datetime NOT NULL,
            recorded_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY event_uuid (event_uuid),
            KEY enrollment_id (enrollment_id),
            UNIQUE KEY enrollment_event (enrollment_id, event_type),
            KEY event_type_occurred_at (event_type, occurred_at),
            KEY course_id_event_type (course_id, event_type),
            KEY user_id_event_type (user_id, event_type)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( $sql );

        update_option( 'edu_analytics_db_version', EDU_ANALYTICS_VERSION );
    }
  public static function maybe_upgrade() {
    $installed_version = get_option( 'edu_analytics_db_version' );

    if ( $installed_version !== EDU_ANALYTICS_VERSION ) {
        self::create_table();
    }
}
}
