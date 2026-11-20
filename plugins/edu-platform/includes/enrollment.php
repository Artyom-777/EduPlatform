<?php
/**
 * Check whether a user is enrolled in a course.
 *
 * @param int $user_id
 * @param int $course_id
 * @return bool
 */
function edu_platform_user_is_enrolled( $user_id, $course_id ) {

    global $wpdb;

    $user_id   = (int) $user_id;
    $course_id = (int) $course_id;

    if ( $user_id < 1 || $course_id < 1 ) {
        return false;
    }

    $table = edu_platform_enrollments_table();

    $found = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$table} WHERE user_id = %d AND course_id = %d AND status = 'active' LIMIT 1",
            $user_id,
            $course_id
        )
    );

    return ! empty( $found );
}


/**
 * Get course IDs the user is enrolled in.
 *
 * @param int $user_id
 * @return int[]
 */
function edu_platform_get_user_enrolled_course_ids( $user_id = 0 ) {

    global $wpdb;

    $user_id = $user_id ? (int) $user_id : get_current_user_id();

    if ( $user_id < 1 ) {
        return array();
    }

    $table = edu_platform_enrollments_table();

    $ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT course_id FROM {$table} WHERE user_id = %d AND status = 'active' ORDER BY enrolled_at DESC",
            $user_id
        )
    );

    return array_values( array_map( 'intval', $ids ) );
}


/**
 * Enroll a user in a course.
 *
 * @param int $user_id
 * @param int $course_id
 * @return bool
 */
function edu_platform_enroll_user( $user_id, $course_id ) {

    global $wpdb;

    $user_id   = (int) $user_id;
    $course_id = (int) $course_id;

    if (
        $user_id < 1 ||
        $course_id < 1 ||
        'course' !== get_post_type( $course_id )
    ) {
        return false;
    }

    if ( edu_platform_user_is_enrolled( $user_id, $course_id ) ) {
        return true;
    }

    $table = edu_platform_enrollments_table();
    $now = current_time('mysql');

    $inserted = $wpdb->insert(
        $table,
        [
            'user_id'     => $user_id,
            'course_id'   => $course_id,
            'status'      => 'active',
            'enrolled_at' => $now,
        ],
        [ '%d', '%d', '%s', '%s' ]
    );
    
    if ( false === $inserted ) {
        return false;
    }
    
    $enrollment_id = (int) $wpdb->insert_id;
    
    $course_title = get_the_title( $course_id );
    
    $event_uuid = wp_generate_uuid4();
    
    do_action(
        'edu_platform_enrollment_confirmed',
        [
            'event_uuid'    => $event_uuid,
            'enrollment_id' => $enrollment_id,
            'user_id'       => $user_id,
            'course_id'     => $course_id,
            'course_title'  => $course_title,
            'occurred_at'   => $now,
        ]
    );
    
    return true;
}

/**
 * Remove a user's enrollment record for a course.
 *
 * @param int $user_id
 * @param int $course_id
 * @return bool
 */

 function edu_platform_unenroll_user( $user_id, $course_id ) {

    global $wpdb;

    $user_id   = (int) $user_id;
    $course_id = (int) $course_id;

    if (
        $user_id < 1 ||
        $course_id < 1 ||
        'course' !== get_post_type( $course_id )
    ) {
        return false;
    }

    $table = edu_platform_enrollments_table();

    // Retrieve the existing enrollment before deleting it.
    $enrollment = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id FROM {$table} WHERE user_id = %d AND course_id = %d",
            $user_id,
            $course_id
        ),
        ARRAY_A
    );

    if ( ! $enrollment ) {
        return false;
    }

    $enrollment_id = (int) $enrollment['id'];

    $course_title = get_the_title( $course_id );

    $occurred_at = current_time( 'mysql' );

    // Delete the enrollment.
    $deleted = $wpdb->delete(
        $table,
        [
            'id' => $enrollment_id,
        ],
        [ '%d' ]
    );

    if ( 1 !== $deleted ) {
        return false;
    }

    // Notify analytics after successful deletion.
    do_action(
        'edu_platform_enrollment_unenrolled',
        [
            'event_uuid'    => wp_generate_uuid4(),
            'enrollment_id' => $enrollment_id,
            'user_id'       => $user_id,
            'course_id'     => $course_id,
            'course_title'  => $course_title,
            'occurred_at'   => $occurred_at,
        ]
    );

    return true;
}



/**
 * Handle an authenticated course unenrollment request.
 */
function edu_platform_handle_course_unenrollment() {

    if ( ! is_user_logged_in() ) {
        auth_redirect();
    }

    if (
        ! isset( $_POST['edu_unenroll_nonce'] ) ||
        ! wp_verify_nonce(
            sanitize_text_field( wp_unslash( $_POST['edu_unenroll_nonce'] ) ),
            'edu_unenroll_course'
        )
    ) {
        wp_die(
            esc_html__( 'The unenrollment request could not be verified.', 'edu-platform' ),
            esc_html__( 'Invalid request', 'edu-platform' ),
            array( 'response' => 403 )
        );
    }

    $course_id = isset( $_POST['course_id'] )
        ? absint( $_POST['course_id'] )
        : 0;

    if ( $course_id < 1 || 'course' !== get_post_type( $course_id ) ) {
        wp_die(
            esc_html__( 'The selected course is invalid.', 'edu-platform' ),
            esc_html__( 'Invalid course', 'edu-platform' ),
            array( 'response' => 400 )
        );
    }

    $removed     = edu_platform_unenroll_user( get_current_user_id(), $course_id );
    $redirect_to = get_permalink( $course_id );

    if ( ! $redirect_to ) {
        $redirect_to = home_url( '/' );
    }

    wp_safe_redirect(
        add_query_arg(
            $removed ? 'unenrolled' : 'unenrollment_error',
            '1',
            $redirect_to
        )
    );
    exit;
}

add_action( 'admin_post_edu_platform_unenroll', 'edu_platform_handle_course_unenrollment' );


/**
 * Handle Enroll Now POST request.
 */
function edu_platform_handle_course_enrollment() {

    if ( ! isset( $_POST['edu_enroll_submit'] ) ) {
        return;
    }

    if ( ! is_user_logged_in() ) {

        $login = function_exists( 'eduplatform_login_url' )
            ? eduplatform_login_url()
            : wp_login_url( home_url( '/' ) );

        $dest = wp_get_referer()
            ? wp_get_referer()
            : home_url( '/' );

        $pending = isset( $_POST['course_id'] )
            ? absint( $_POST['course_id'] )
            : 0;

        if ( $pending > 0 ) {
            $permalink = get_permalink( $pending );

            if ( $permalink ) {
                $dest = $permalink;
            }
        }

        wp_safe_redirect(
            add_query_arg( 'redirect_to', rawurlencode( $dest ), $login )
        );

        exit;
    }

    if (
        ! isset( $_POST['edu_enroll_nonce'] ) ||
        ! wp_verify_nonce(
            sanitize_text_field( wp_unslash( $_POST['edu_enroll_nonce'] ) ),
            'edu_enroll_course'
        )
    ) {
        return;
    }

    $course_id = isset( $_POST['course_id'] )
        ? absint( $_POST['course_id'] )
        : 0;

    if ( $course_id < 1 || 'course' !== get_post_type( $course_id ) ) {
        return;
    }

    // Check if the course has a price and if it is greater than 0
    $price = get_post_meta( $course_id, '_course_price', true );

    if ( ! is_numeric( $price ) || (float) $price !== 0.0 ) {
        wp_safe_redirect(
            add_query_arg(
                'enrollment_error',
                'payment_required',
                get_permalink( $course_id )
            )
        );
    
        exit;
    }
    
    $enrolled = edu_platform_enroll_user(
        get_current_user_id(),
        $course_id
    );

    if ( $enrolled ) {
        wp_safe_redirect(
            add_query_arg( 'enrolled', '1', get_permalink( $course_id ) )
        );
    } else {
        wp_safe_redirect(
            add_query_arg( 'enrollment_error', '1', get_permalink( $course_id ) )
        );
    }

    exit;
}
