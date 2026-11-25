<?php
/**
 * Plugin Name: Custom WooCommerce
 * Description: An independent eCommerce engine for WordPress.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Text Domain: custom-woocommerce
 * Domain Path: /languages
 */

namespace CustomWooCommerce;

defined('ABSPATH') || exit;

/**
 * Plugin constants.
 */
define('CWC_VERSION', '1.0.0');
define('CWC_DB_VERSION', '1.1.0');

define('CWC_PLUGIN_FILE', __FILE__);

define('CWC_PLUGIN_DIR', plugin_dir_path(__FILE__));

define('CWC_PLUGIN_URL', plugin_dir_url(__FILE__));

define('CWC_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Load core Plugin class.
 */
require_once CWC_PLUGIN_DIR . 'includes/Core/Autoloader.php';

$autoloader = new Core\Autoloader();

$autoloader->register();

$plugin = new Core\Plugin();

/**
 * Initialize plugin.
 */

register_activation_hook(
    CWC_PLUGIN_FILE,
    [Core\Activator::class, 'activate']
);

register_deactivation_hook(
    CWC_PLUGIN_FILE,
    [Core\Deactivator::class, 'deactivate']
);

/**
 * Start plugin.
 */
add_action(
    'plugins_loaded',
    [$plugin, 'init']
);

