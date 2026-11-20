<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Edu_Analytics_Event_Listener {

    /**
     * Register WordPress event listeners.
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
    }

    /**
     * Store a confirmed enrollment event.
     *
     * @param array $event Event data.
     */
    public static function handle_enrollment_confirmed( $event ) {

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

        $wpdb->insert(
            $table_name,
            [
                'event_uuid'    => $event_uuid,
                'enrollment_id' => $enrollment_id,
                'user_id'       => $user_id,
                'course_id'     => $course_id,
                'course_title'  => $course_title,
                'event_type'    => 'enrollment_confirmed',
                'occurred_at'   => $occurred_at,
                'recorded_at'   => current_time( 'mysql' ),
            ],
            [
                '%s',
                '%d',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
            ]
        );
    }


    /**
     * Store an unenrollment event.
     *
     * @param array $event Event data.
     */
    public static function handle_enrollment_unenrolled( $event ) {

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

        $wpdb->insert(
            $table_name,
            [
                'event_uuid'    => $event_uuid,
                'enrollment_id' => $enrollment_id,
                'user_id'       => $user_id,
                'course_id'     => $course_id,
                'course_title'  => $course_title,
                'event_type'    => 'enrollment_unenrolled',
                'occurred_at'   => $occurred_at,
                'recorded_at'   => current_time( 'mysql' ),
            ],
            [
                '%s',
                '%d',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
            ]
        );
    }

}

