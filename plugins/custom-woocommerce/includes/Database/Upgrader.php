<?php

namespace CustomWooCommerce\Database;

defined('ABSPATH') || exit;

class Upgrader
{
    /**
     * Check whether database upgrade is required.
     */
    public static function maybe_upgrade(): void
    {
        $installed_version = get_option(
            'cwc_db_version',
            '0.0.0'
        );

        if (
            version_compare(
                $installed_version,
                CWC_DB_VERSION,
                '>='
            )
        ) {
            return;
        }

        self::run_upgrade();
    }

    /**
     * Run database upgrade.
     */
    private static function run_upgrade(): void
    {
        Installer::install();
    }
}
