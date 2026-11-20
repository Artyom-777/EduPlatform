<?php
/**
 * Plugin Name: Education Analytics
 * Description: Educational analytics for EduPlatform.
 * Version: 1.0.0
 * Author: EduPlatform
 * Text Domain: education-analytics
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'EDU_ANALYTICS_VERSION', '1.1.0' );
define( 'EDU_ANALYTICS_PATH', plugin_dir_path( __FILE__ ) );
define( 'EDU_ANALYTICS_URL', plugin_dir_url( __FILE__ ) );

require_once EDU_ANALYTICS_PATH . 'includes/class-database.php';
require_once EDU_ANALYTICS_PATH . 'includes/class-event-listener.php';
require_once EDU_ANALYTICS_PATH . 'includes/class-analytics.php';

add_action(
    'plugins_loaded',
    [ 'Edu_Analytics_Database', 'maybe_upgrade' ]
);

register_activation_hook(
    __FILE__,
    [ 'Edu_Analytics_Database', 'create_table' ]
);

Edu_Analytics_Event_Listener::init();
Edu_Analytics::init();
