<?php

namespace CustomWooCommerce\Core;

defined('ABSPATH') || exit;

class Autoloader
{
    /**
     * Namespace prefix.
     */
    private string $prefix = 'CustomWooCommerce\\';

    /**
     * Base directory.
     */
    private string $base_dir;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->base_dir = CWC_PLUGIN_DIR . 'includes/';
    }

    /**
     * Register autoloader.
     */
    public function register(): void
    {
        spl_autoload_register(
            [$this, 'load_class']
        );
    }

    /**
     * Load class automatically.
     */
    public function load_class(string $class): void
    {
        // Check namespace prefix.
        if (!str_starts_with($class, $this->prefix)) {
            return;
        }

        // Remove namespace prefix.
        $relative_class = substr(
            $class,
            strlen($this->prefix)
        );

        // Convert namespace separators to directory separators.
        $file = $this->base_dir
            . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class)
            . '.php';

        // Load file if it exists.
        if (file_exists($file)) {
            require_once $file;
        }
    }
}
