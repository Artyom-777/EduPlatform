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
        $attributes_table = $wpdb->prefix . 'cwc_attributes';
        $attribute_terms_table = $wpdb->prefix . 'cwc_attribute_terms';

        return [

            // Sessions table.
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

            // Global product attributes.
            'attributes' => "
                CREATE TABLE {$attributes_table} (
                    attribute_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    attribute_name varchar(200) NOT NULL,
                    attribute_label varchar(200) NOT NULL,
                    attribute_order int(11) NOT NULL DEFAULT 0,
                    PRIMARY KEY  (attribute_id),
                    UNIQUE KEY attribute_name (attribute_name)
                ) $charset_collate;
            ",

            // Attribute options / terms.
            'attribute_terms' => "
                CREATE TABLE {$attribute_terms_table} (
                    term_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    attribute_id bigint(20) unsigned NOT NULL,
                    name varchar(200) NOT NULL,
                    slug varchar(200) NOT NULL,
                    term_order int(11) NOT NULL DEFAULT 0,
                    PRIMARY KEY  (term_id),
                    UNIQUE KEY attribute_slug (attribute_id, slug),
                    KEY attribute_id (attribute_id)
                ) $charset_collate;
            ",
        ];
    }
}

