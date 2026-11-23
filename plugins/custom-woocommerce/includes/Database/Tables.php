<?php

namespace CustomWooCommerce\Database;

defined('ABSPATH') || exit;

class Tables
{
    /**
     * Get database table definitions.
     */
    public static function get_tables(): array
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sessions_table = $wpdb->prefix . 'cwc_sessions';

        return [
            'sessions' => "
                CREATE TABLE {$sessions_table} (
                    session_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    session_key varchar(191) NOT NULL,
                    session_value longtext NOT NULL,
                    session_expiry bigint(20) unsigned NOT NULL,
                    PRIMARY KEY  (session_id),
                    UNIQUE KEY session_key (session_key),
                    KEY session_expiry (session_expiry)
                ) $charset_collate;
            ",
        ];
    }
}
