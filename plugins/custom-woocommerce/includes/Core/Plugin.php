<?php

namespace CustomWooCommerce\Core;

use CustomWooCommerce\Database\Upgrader;

defined('ABSPATH') || exit;

class Plugin
{
    /**
     * Hook manager instance.
     */
    private HookManager $hook_manager;
    private AssetManager $asset_manager;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->hook_manager = new HookManager();
        $this->asset_manager = new AssetManager();
    }

    /**
     * Initialize plugin.
     */
    public function init(): void
    {
        Upgrader::maybe_upgrade();
    
        $this->define_hooks();
    
        $this->hook_manager->register();
    }

    /**
     * Define plugin hooks.
     */
    private function define_hooks(): void
    {
        $this->hook_manager->add_action(
            'init',
            $this,
            'load_textdomain'
        );
    
        $this->hook_manager->add_action(
            'admin_enqueue_scripts',
            $this->asset_manager,
            'enqueue_admin_assets'
        );
    
        $this->hook_manager->add_action(
            'wp_enqueue_scripts',
            $this->asset_manager,
            'enqueue_frontend_assets'
        );
    }

    /**
     * Load translations.
     */
    public function load_textdomain(): void
    {
        load_plugin_textdomain(
            'custom-woocommerce',
            false,
            dirname(CWC_PLUGIN_BASENAME) . '/languages'
        );
    }
}
