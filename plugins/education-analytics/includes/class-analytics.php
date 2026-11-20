<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Edu_Analytics {

    /**
     * Register admin menu.
     */
    public static function init() {

        add_action(
            'admin_menu',
            [ __CLASS__, 'register_admin_menu' ]
        );
    }

    /**
     * Add Analytics page to WordPress admin.
     */
    public static function register_admin_menu() {

        add_menu_page(
            __( 'Education Analytics', 'education-analytics' ),
            __( 'Education Analytics', 'education-analytics' ),
            'manage_options',
            'education-analytics',
            [ __CLASS__, 'render_dashboard' ],
            'dashicons-chart-bar',
            30
        );
    }

/**
 * Get enrollment statistics directly from EduPlatform Core.
 *
 * @return array
 */
public static function get_statistics() {

    global $wpdb;

    $table_name = $wpdb->prefix . 'edu_enrollments';

    $total_confirmed = (int) $wpdb->get_var(
        "SELECT COUNT(*)
        FROM {$table_name}
        WHERE event_type = 'enrolled'"
    );

    $total_unenrolled = (int) $wpdb->get_var(
        "SELECT COUNT(*)
        FROM {$table_name}
        WHERE event_type = 'unenrolled'"
    );

    $active_enrollments = (int) $wpdb->get_var(
        "SELECT COUNT(*)
        FROM {$table_name} AS current_event
        INNER JOIN (
            SELECT user_id, course_id, MAX(id) AS latest_id
            FROM {$table_name}
            GROUP BY user_id, course_id
        ) AS latest
        ON current_event.id = latest.latest_id
        WHERE current_event.event_type = 'enrolled'"
    );

    $unique_students = (int) $wpdb->get_var(
        "SELECT COUNT(DISTINCT user_id)
        FROM {$table_name}
        WHERE event_type = 'enrolled'"
    );

    return [
        'total_confirmed'    => $total_confirmed,
        'total_unenrolled'   => $total_unenrolled,
        'active_enrollments' => $active_enrollments,
        'unique_students'    => $unique_students,
    ];
}


/**
 * Get enrollment statistics grouped by course.
 *
 * @return array
 */
public static function get_course_statistics() {

    global $wpdb;

    $table_name = $wpdb->prefix . 'edu_enrollments';
    $posts_table = $wpdb->posts;

    $sql = "
        SELECT
            events.course_id,
            COALESCE(
                NULLIF(posts.post_title, ''),
                CONCAT('Deleted course #', events.course_id)
            ) AS course_title,

            SUM(
                CASE
                    WHEN events.event_type = 'enrolled'
                    THEN 1 ELSE 0
                END
            ) AS total_confirmed,

            SUM(
                CASE
                    WHEN events.event_type = 'unenrolled'
                    THEN 1 ELSE 0
                END
            ) AS total_unenrolled,

            SUM(
                CASE
                    WHEN latest.event_type = 'enrolled'
                    THEN 1 ELSE 0
                END
            ) AS active_enrollments

        FROM {$table_name} AS events

        LEFT JOIN {$posts_table} AS posts
            ON posts.ID = events.course_id
            AND posts.post_type = 'course'

        LEFT JOIN (
            SELECT current_event.user_id,
                   current_event.course_id,
                   current_event.event_type
            FROM {$table_name} AS current_event
            INNER JOIN (
                SELECT user_id, course_id, MAX(id) AS latest_id
                FROM {$table_name}
                GROUP BY user_id, course_id
            ) AS latest_event
                ON current_event.id = latest_event.latest_id
        ) AS latest
            ON latest.user_id = events.user_id
            AND latest.course_id = events.course_id
            AND latest.event_type = 'enrolled'
            AND events.event_type = 'enrolled'

        GROUP BY events.course_id, posts.post_title

        ORDER BY total_confirmed DESC
    ";

    return $wpdb->get_results( $sql, ARRAY_A );
}


    /**
     * Render Analytics dashboard.
     */
    public static function render_dashboard() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die(
                esc_html__( 'You do not have permission to access this page.', 'education-analytics' )
            );
        }

        $stats = self::get_statistics();
        $course_stats = self::get_course_statistics();
        ?>
               
        <div class="wrap">

            <h1>
                <?php esc_html_e( 'Education Analytics', 'education-analytics' ); ?>
            </h1>

            <p>
                <?php esc_html_e( 'Enrollment statistics overview.', 'education-analytics' ); ?>
            </p>

            <div class="notice notice-info inline">
                <p>
                    <?php esc_html_e( 'Statistics are retrieved directly from the database.', 'education-analytics' ); ?>
                </p>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px; margin-top:25px;">

                <?php
                $cards = [
                    'total_confirmed' => __( 'Total Confirmed Enrollments', 'education-analytics' ),
                    'active_enrollments' => __( 'Active Enrollments', 'education-analytics' ),
                    'total_unenrolled' => __( 'Total Unenrollments', 'education-analytics' ),
                    'unique_students' => __( 'Unique Students', 'education-analytics' ),
                ];
                ?>

                <?php foreach ( $cards as $key => $label ) : ?>

                    <div style="background:#fff; border:1px solid #dcdcde; padding:22px; border-radius:6px;">

                        <h2 style="margin-top:0;">
                            <?php echo esc_html( $label ); ?>
                        </h2>

                        <p style="font-size:32px; font-weight:600; margin-bottom:0;">
                            <?php echo esc_html( number_format_i18n( $stats[ $key ] ) ); ?>
                        </p>

                    </div>

                <?php endforeach; ?>

            </div>
                
<h2 style="margin-top:35px;">
    <?php esc_html_e( 'Course Statistics', 'education-analytics' ); ?>
</h2>

<table class="widefat striped">
    <thead>
        <tr>
            <th><?php esc_html_e( 'Course', 'education-analytics' ); ?></th>
            <th><?php esc_html_e( 'Total Enrollments', 'education-analytics' ); ?></th>
            <th><?php esc_html_e( 'Unenrollments', 'education-analytics' ); ?></th>
            <th><?php esc_html_e( 'Active Enrollments', 'education-analytics' ); ?></th>
        </tr>
    </thead>

    <tbody>

        <?php if ( ! empty( $course_stats ) ) : ?>

            <?php foreach ( $course_stats as $course ) : ?>

                <tr>
                    <td>
                        <?php echo esc_html( $course['course_title'] ); ?>
                    </td>

                    <td>
                        <?php echo esc_html( number_format_i18n( (int) $course['total_confirmed'] ) ); ?>
                    </td>

                    <td>
                        <?php echo esc_html( number_format_i18n( (int) $course['total_unenrolled'] ) ); ?>
                    </td>

                    <td>
                        <?php echo esc_html( number_format_i18n( (int) $course['active_enrollments'] ) ); ?>
                    </td>
                </tr>

            <?php endforeach; ?>

        <?php else : ?>

            <tr>
                <td colspan="4">
                    <?php esc_html_e( 'No enrollment data available yet.', 'education-analytics' ); ?>
                </td>
            </tr>

        <?php endif; ?>

    </tbody>
</table>

        </div>

        <?php
    }
}
