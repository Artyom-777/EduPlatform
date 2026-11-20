<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Edu_Analytics_Event_Listener {

    /**
     * Register event listeners and historical import.
     */
    public static function init() {

        add_action(
            'edu_platform_enrollment_confirmed',
            [ __CLASS__, 'handle_enrollment_confirmed' ],
            10,
            1
        );

        add_action(
            'edu_platform_enrollment_unenrolled',
            [ __CLASS__, 'handle_enrollment_unenrolled' ],
            10,
            1
        );

        add_action(
            'admin_init',
            [ __CLASS__, 'import_historical_events' ]
        );
    }

    /**
     * Store an event in Analytics.
     */
    private static function store_event( $event, $event_type ) {

        global $wpdb;

        if ( ! is_array( $event ) ) {
            return;
        }

        $required_fields = [
            'event_uuid',
            'enrollment_id',
            'user_id',
            'course_id',
            'course_title',
            'occurred_at',
        ];

        foreach ( $required_fields as $field ) {
            if ( ! isset( $event[ $field ] ) ) {
                return;
            }
        }

        $event_uuid    = sanitize_text_field( $event['event_uuid'] );
        $enrollment_id = absint( $event['enrollment_id'] );
        $user_id       = absint( $event['user_id'] );
        $course_id     = absint( $event['course_id'] );
        $course_title  = sanitize_text_field( $event['course_title'] );
        $occurred_at   = sanitize_text_field( $event['occurred_at'] );

        if (
            ! wp_is_uuid( $event_uuid ) ||
            ! $enrollment_id ||
            ! $user_id ||
            ! $course_id ||
            '' === $course_title
        ) {
            return;
        }

        $table_name = $wpdb->prefix . 'edu_analytics_events';

        $wpdb->query(
            $wpdb->prepare(
                "INSERT IGNORE INTO {$table_name}
                (
                    event_uuid,
                    enrollment_id,
                    user_id,
                    course_id,
                    course_title,
                    event_type,
                    occurred_at,
                    recorded_at
                )
                VALUES ( %s, %d, %d, %d, %s, %s, %s, %s )",
                $event_uuid,
                $enrollment_id,
                $user_id,
                $course_id,
                $course_title,
                $event_type,
                $occurred_at,
                current_time( 'mysql' )
            )
        );
    }

    /**
     * Store a confirmed enrollment event.
     */
    public static function handle_enrollment_confirmed( $event ) {

        self::store_event( $event, 'enrollment_confirmed' );
    }

    /**
     * Store an unenrollment event.
     */
    public static function handle_enrollment_unenrolled( $event ) {

        self::store_event( $event, 'enrollment_unenrolled' );
    }

    /**
     * Import historical events in batches.
     */
    public static function import_historical_events() {

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( get_option( 'edu_analytics_history_import_complete' ) ) {
            return;
        }

        global $wpdb;

        $source_table = $wpdb->prefix . 'edu_enrollments';

        $analytics_table = $wpdb->prefix . 'edu_analytics_events';

        $table_exists = $wpdb->get_var(
            $wpdb->prepare(
                'SHOW TABLES LIKE %s',
                $source_table
            )
        );

        if ( $table_exists !== $source_table ) {
            return;
        }

        $last_id = (int) get_option(
            'edu_analytics_history_import_last_id',
            0
        );

        $batch_size = 200;

        $events = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT *
                FROM {$source_table}
                WHERE id > %d
                ORDER BY id ASC
                LIMIT %d",
                $last_id,
                $batch_size
            ),
            ARRAY_A
        );

        if ( empty( $events ) ) {

            update_option(
                'edu_analytics_history_import_complete',
                1
            );

            delete_option( 'edu_analytics_history_import_last_id' );

            return;
        }

        foreach ( $events as $event ) {

            $event_type = '';

            if ( 'enrolled' === $event['event_type'] ) {
                $event_type = 'enrollment_confirmed';
            } elseif ( 'unenrolled' === $event['event_type'] ) {
                $event_type = 'enrollment_unenrolled';
            }

            if ( '' === $event_type ) {
                $last_id = (int) $event['id'];
                continue;
            }

            $course_title = get_the_title(
                (int) $event['course_id']
            );

            if ( ! $course_title ) {
                $course_title = 'Deleted course';
            }

            self::store_event(
                [
                    'event_uuid'    => $event['event_uuid'],
                    'enrollment_id' => $event['id'],
                    'user_id'       => $event['user_id'],
                    'course_id'     => $event['course_id'],
                    'course_title'  => $course_title,
                    'occurred_at'   => $event['occurred_at'],
                ],
                $event_type
            );

            $last_id = (int) $event['id'];
        }

        update_option(
            'edu_analytics_history_import_last_id',
            $last_id
        );
    }
}


