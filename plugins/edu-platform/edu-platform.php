<?php
/**
 * Plugin Name: EduPlatform
 * Description: Course management system for EduPlatform.
 * Version: 1.0.0
 * Author: EduPlatform
 * Text Domain: edu-platform
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Plugin constants.
 */
define( 'EDU_PLATFORM_VERSION', '1.0.0' );
define( 'EDU_PLATFORM_PATH', plugin_dir_path( __FILE__ ) );
define( 'EDU_PLATFORM_URL', plugin_dir_url( __FILE__ ) );
/**
 * Load Composer dependencies.
 */
require_once EDU_PLATFORM_PATH . 'vendor/autoload.php';

/**
 * Load plugin files.
 */
require_once EDU_PLATFORM_PATH . 'includes/post-types.php';
require_once EDU_PLATFORM_PATH . 'includes/taxonomies.php';
require_once EDU_PLATFORM_PATH . 'includes/course-meta.php';
require_once EDU_PLATFORM_PATH . 'includes/lesson-meta.php';
require_once EDU_PLATFORM_PATH . 'includes/database.php';
require_once EDU_PLATFORM_PATH . 'includes/enrollment.php';
require_once EDU_PLATFORM_PATH . 'includes/payments.php';
require_once EDU_PLATFORM_PATH . 'includes/stripe-webhook.php';
require_once EDU_PLATFORM_PATH . 'includes/team-members.php';
require_once EDU_PLATFORM_PATH . 'includes/locations.php';

/**
 * Plugin activation.
 */
function edu_platform_activate() {

    edu_platform_register_course_post_type();

    edu_platform_register_course_taxonomy();

    edu_platform_register_lesson_post_type();

    edu_platform_create_enrollment_table();

    edu_platform_create_payment_table();

    update_option(
        'edu_platform_enrollments_db_version',
        EDU_PLATFORM_VERSION
    );

    flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'edu_platform_activate' );


/**
 * Plugin deactivation.
 */
function edu_platform_deactivate() {

    flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'edu_platform_deactivate' );
