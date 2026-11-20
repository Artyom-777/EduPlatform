<?php

/**
 * Create and update Enrollment event history table.
 */
function edu_platform_create_enrollment_table() {

    global $wpdb;

    $table_name      = $wpdb->prefix . 'edu_enrollments';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        event_uuid char(36) NOT NULL,
        user_id bigint(20) unsigned NOT NULL,
        course_id bigint(20) unsigned NOT NULL,
        event_type varchar(20) NOT NULL,
        occurred_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY event_uuid (event_uuid),
        KEY user_course (user_id, course_id),
        KEY user_id (user_id),
        KEY course_id (course_id),
        KEY event_type (event_type)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta( $sql );

    // Remove the old UNIQUE index.
    $wpdb->query(
        "ALTER TABLE `$table_name` DROP INDEX user_course"
    );

    // Add a non-unique index if it does not exist.
    $index_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*)
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
            AND table_name = %s
            AND index_name = %s",
            $table_name,
            'user_course'
        )
    );

    if ( ! $index_exists ) {
        $wpdb->query(
            "ALTER TABLE `$table_name` ADD KEY user_course (user_id, course_id)"
        );
    }

    // Add new event columns to existing tables.
    $columns = [
        'event_uuid'  => "ALTER TABLE `$table_name` ADD event_uuid char(36) NOT NULL DEFAULT ''",
        'event_type'  => "ALTER TABLE `$table_name` ADD event_type varchar(20) NOT NULL DEFAULT 'enrolled'",
        'occurred_at' => "ALTER TABLE `$table_name` ADD occurred_at datetime DEFAULT NULL",
    ];

    foreach ( $columns as $column => $query ) {
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW COLUMNS FROM `$table_name` LIKE %s",
                $column
            )
        );

        if ( ! $exists ) {
            $wpdb->query( $query );
        }
    }

    // Preserve old enrollment records as enrolled events.
    $wpdb->query(
        "UPDATE `$table_name`
        SET event_uuid = UUID()
        WHERE event_uuid = ''"
    );

    $wpdb->query(
        "UPDATE `$table_name`
        SET occurred_at = enrolled_at
        WHERE occurred_at IS NULL"
    );
}


/**
 * Get Enrollment table name.
 */
function edu_platform_enrollments_table() {

    global $wpdb;

    return $wpdb->prefix . 'edu_enrollments';
}



/**
 * Ensure Enrollment event history table is updated.
 */
function edu_platform_maybe_create_enrollment_table() {

    global $wpdb;

    $db_version = '4';

    if ( get_option( 'edu_platform_enrollments_db_version' ) === $db_version ) {
        return;
    }

    $table_name = edu_platform_enrollments_table();

    // Ensure the new event-history structure exists.
    edu_platform_create_enrollment_table();

    // Verify required event columns exist.
    $required_columns = [
        'event_uuid',
        'event_type',
        'occurred_at',
    ];

    foreach ( $required_columns as $column ) {

        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW COLUMNS FROM `$table_name` LIKE %s",
                $column
            )
        );

        if ( ! $exists ) {
            return;
        }
    }

    // Make sure all existing events have valid UUIDs.
    $wpdb->query(
        "UPDATE `$table_name`
        SET event_uuid = UUID()
        WHERE event_uuid = '' OR event_uuid IS NULL"
    );

    // Preserve timestamps before removing old columns.
    $wpdb->query(
        "UPDATE `$table_name`
        SET occurred_at = enrolled_at
        WHERE occurred_at IS NULL
        AND enrolled_at IS NOT NULL"
    );

    // Remove legacy enrollment columns.
    $legacy_columns = [
        'status',
        'enrolled_at',
        'unenrolled_at',
    ];

    foreach ( $legacy_columns as $column ) {

        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW COLUMNS FROM `$table_name` LIKE %s",
                $column
            )
        );

        if ( $exists ) {
            $wpdb->query(
                "ALTER TABLE `$table_name` DROP COLUMN `$column`"
            );
        }
    }

    // Verify that legacy columns were removed successfully.
    foreach ( $legacy_columns as $column ) {

        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW COLUMNS FROM `$table_name` LIKE %s",
                $column
            )
        );

        if ( $exists ) {
            return;
        }
    }

    update_option(
        'edu_platform_enrollments_db_version',
        $db_version
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
