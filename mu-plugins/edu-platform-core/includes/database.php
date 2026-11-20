<?php
/**
 * Create Enrollment database table.
 */
function edu_platform_create_enrollment_table() {

    global $wpdb;

    $table_name = $wpdb->prefix . 'edu_enrollments';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        user_id bigint(20) unsigned NOT NULL,
        course_id bigint(20) unsigned NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'active',
        enrolled_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY user_course (user_id, course_id),
        KEY user_id (user_id),
        KEY course_id (course_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta( $sql );
}


/**
 * Get Enrollment table name.
 *
 * @return string
 */
function edu_platform_enrollments_table() {

    global $wpdb;

    return $wpdb->prefix . 'edu_enrollments';
}

/**
 * Ensure Enrollment table exists.
 */
function edu_platform_maybe_create_enrollment_table() {

    if ( get_option( 'edu_platform_enrollments_db_version' ) === EDU_PLATFORM_CORE_VERSION ) {
        return;
    }

    edu_platform_create_enrollment_table();

    update_option(
        'edu_platform_enrollments_db_version',
        EDU_PLATFORM_CORE_VERSION
    );
}

add_action( 'init', 'edu_platform_maybe_create_enrollment_table', 5 );


/**
 * Create Payments database table.
 */
function edu_platform_create_payment_table() {

    global $wpdb;

    $table_name      = $wpdb->prefix . 'edu_payments';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        user_id bigint(20) unsigned NOT NULL,
        course_id bigint(20) unsigned NOT NULL,
        amount bigint(20) unsigned NOT NULL,
        currency char(3) NOT NULL DEFAULT 'usd',
        status varchar(20) NOT NULL DEFAULT 'pending',
        stripe_session_id varchar(255) DEFAULT NULL,
        stripe_payment_intent_id varchar(255) DEFAULT NULL,
        woo_order_id bigint(20) unsigned DEFAULT NULL,
        created_at datetime NOT NULL,
        paid_at datetime DEFAULT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY stripe_session_id (stripe_session_id),
        UNIQUE KEY stripe_payment_intent_id (stripe_payment_intent_id),
        KEY user_id (user_id),
        KEY course_id (course_id),
        KEY status (status),
        KEY woo_order_id (woo_order_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta( $sql );
}


/**
 * Get Payments table name.
 *
 * @return string
 */
function edu_platform_payments_table() {

    global $wpdb;

    return $wpdb->prefix . 'edu_payments';
}


/**
 * Ensure Payments table exists.
 */
function edu_platform_maybe_create_payment_table() {

    $db_version = '2';

    if ( get_option( 'edu_platform_payments_db_version' ) === $db_version ) {
        return;
    }

    edu_platform_create_payment_table();

    update_option(
        'edu_platform_payments_db_version',
        $db_version
    );
}

add_action( 'init', 'edu_platform_maybe_create_payment_table', 6 );
