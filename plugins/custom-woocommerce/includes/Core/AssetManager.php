<?php

namespace CustomWooCommerce\Core;

defined('ABSPATH') || exit;

class AssetManager
{
    /**
     * Enqueue admin assets.
     */
    public function enqueue_admin_assets(string $hook_suffix): void
    {
        // Load assets only on Custom WooCommerce admin pages.
        if (!str_contains($hook_suffix, 'cwc-')) {
            return;
        }

        wp_enqueue_style(
            'cwc-admin',
            CWC_PLUGIN_URL . 'assets/css/admin.css',
            [],
            CWC_VERSION
        );

        wp_enqueue_script(
            'cwc-admin',
            CWC_PLUGIN_URL . 'assets/js/admin.js',
            [],
            CWC_VERSION,
            true
        );
    }

    /**
     * Enqueue frontend assets.
     */
    public function enqueue_frontend_assets(): void
    {
        // Load assets only on our product pages.
        if (
            !is_singular('cwc_product') &&
            !is_post_type_archive('cwc_product')
        ) {
            return;
        }

        wp_enqueue_style(
            'cwc-frontend',
            CWC_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            CWC_VERSION
        );

        wp_enqueue_script(
            'cwc-frontend',
            CWC_PLUGIN_URL . 'assets/js/frontend.js',
            [],
            CWC_VERSION,
            true
        );
    }
}
