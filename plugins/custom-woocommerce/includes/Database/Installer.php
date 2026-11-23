<?php

namespace CustomWooCommerce\Database;

defined('ABSPATH') || exit;

class Installer
{
    /**
     * Install database tables.
     */
    public static function install(): void
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $tables = Tables::get_tables();

        foreach ($tables as $table_sql) {
            dbDelta($table_sql);
        }

        update_option('cwc_db_version', CWC_DB_VERSION);
    }
}
