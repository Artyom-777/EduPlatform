<?php

namespace CustomWooCommerce\Core;

use CustomWooCommerce\Database\Installer;

defined('ABSPATH') || exit;

class Activator
{
    /**
     * Run activation tasks.
     */
    public static function activate(): void
    {
        self::check_requirements();

        Installer::install();

        self::create_default_options();

        flush_rewrite_rules();
    }

    /**
     * Check system requirements.
     */
    private static function check_requirements(): void
    {
        if (version_compare(PHP_VERSION, '8.1', '<')) {
            wp_die(
                esc_html__(
                    'Custom WooCommerce requires PHP 8.1 or higher.',
                    'custom-woocommerce'
                )
            );
        }
    }

    /**
     * Create default options.
     */
    private static function create_default_options(): void
    {
        add_option('cwc_version', CWC_VERSION);
        add_option('cwc_installed', true);
    }
}

