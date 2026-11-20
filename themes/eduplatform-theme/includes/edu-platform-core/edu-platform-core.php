<?php
/**
 * Plugin Name: EduPlatform Core
 * Description: Core functionality for EduPlatform.
 * Version: 1.0.0
 * Author: EduPlatform
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check whether the original EduPlatform plugin is active.
 */
$active_plugins = (array) get_option( 'active_plugins', [] );

$network_active_plugins = (array) get_site_option(
    'active_sitewide_plugins',
    []
);

$edu_platform_active = (
    in_array(
        'edu-platform/edu-platform.php',
        $active_plugins,
        true
    )
    ||
    isset(
        $network_active_plugins['edu-platform/edu-platform.php']
    )
);

/**
 * Load Core only when the original plugin is inactive.
 */
if ( ! $edu_platform_active ) {

    define( 'EDU_PLATFORM_CORE_VERSION', '1.0.0' );

    define(
        'EDU_PLATFORM_CORE_PATH',
        __DIR__ . '/'
    );

    require_once EDU_PLATFORM_CORE_PATH . 'vendor/autoload.php';

    $files = [
        'post-types.php',
        'taxonomies.php',
        'course-meta.php',
        'lesson-meta.php',
        'database.php',
        'enrollment.php',
        'payments.php',
        'stripe-webhook.php',
        'team-members.php',
        'locations.php',
    ];

    foreach ( $files as $file ) {
        require_once EDU_PLATFORM_CORE_PATH . 'includes/' . $file;
    }
}
