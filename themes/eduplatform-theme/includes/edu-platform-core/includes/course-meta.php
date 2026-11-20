<?php
/**
 * Add Course Information Meta Box.
 */
function edu_platform_add_course_meta_box() {

    add_meta_box(
        'edu_course_information',
        'Course Information',
        'edu_platform_course_meta_box_html',
        'course',
        'normal',
        'high'
    );
}

add_action( 'add_meta_boxes', 'edu_platform_add_course_meta_box' );


/**
 * Display Course Information fields.
 */
function edu_platform_course_meta_box_html( $post ) {

    wp_nonce_field( 'edu_save_course_info', 'edu_course_nonce' );

    $price        = get_post_meta( $post->ID, '_course_price', true );
    $level        = get_post_meta( $post->ID, '_course_level', true );
    $duration     = get_post_meta( $post->ID, '_course_duration', true );
    $instructor   = get_post_meta( $post->ID, '_course_instructor', true );
    $rating       = get_post_meta( $post->ID, '_course_rating', true );
    $review_count = get_post_meta( $post->ID, '_course_review_count', true );
    $lesson_count = get_post_meta( $post->ID, '_course_lesson_count', true );
    ?>

    <p>
        <label><?php esc_html_e( 'Average rating (0–5)', 'edu-platform' ); ?></label><br>
        <input
            type="number"
            name="course_rating"
            value="<?php echo esc_attr( $rating ); ?>"
            min="0"
            max="5"
            step="0.1"
            style="width:100%;"
            placeholder="4.8"
        >
    </p>

    <p>
        <label><?php esc_html_e( 'Review count', 'edu-platform' ); ?></label><br>
        <input
            type="number"
            name="course_review_count"
            value="<?php echo esc_attr( $review_count ); ?>"
            min="0"
            step="1"
            style="width:100%;"
            placeholder="245"
        >
    </p>

    <p>
        <label><?php esc_html_e( 'Number of lessons', 'edu-platform' ); ?></label><br>
        <input
            type="number"
            name="course_lesson_count"
            value="<?php echo esc_attr( $lesson_count ); ?>"
            min="0"
            step="1"
            style="width:100%;"
            placeholder="12"
        >
    </p>

    <p>
        <label><?php esc_html_e( 'Price ($)', 'edu-platform' ); ?></label><br>
        <input
            type="number"
            name="course_price"
            value="<?php echo esc_attr( $price ); ?>"
            min="0"
            step="0.01"
            style="width:100%;"
        >
    </p>

    <p>
        <label><?php esc_html_e( 'Level', 'edu-platform' ); ?></label><br>
        <select name="course_level" style="width:100%;">
            <option value=""><?php esc_html_e( 'Select Level', 'edu-platform' ); ?></option>
            <option value="Beginner" <?php selected( $level, 'Beginner' ); ?>>
                <?php esc_html_e( 'Beginner', 'edu-platform' ); ?>
            </option>
            <option value="Intermediate" <?php selected( $level, 'Intermediate' ); ?>>
                <?php esc_html_e( 'Intermediate', 'edu-platform' ); ?>
            </option>
            <option value="Advanced" <?php selected( $level, 'Advanced' ); ?>>
                <?php esc_html_e( 'Advanced', 'edu-platform' ); ?>
            </option>
        </select>
    </p>

    <p>
        <label><?php esc_html_e( 'Duration', 'edu-platform' ); ?></label><br>
        <input
            type="text"
            name="course_duration"
            value="<?php echo esc_attr( $duration ); ?>"
            placeholder="<?php esc_attr_e( '8 Weeks', 'edu-platform' ); ?>"
            style="width:100%;"
        >
    </p>

    <p>
        <label><?php esc_html_e( 'Instructor', 'edu-platform' ); ?></label><br>
        <input
            type="text"
            name="course_instructor"
            value="<?php echo esc_attr( $instructor ); ?>"
            placeholder="<?php esc_attr_e( 'Instructor name', 'edu-platform' ); ?>"
            style="width:100%;"
        >
    </p>

    <?php
}


/**
 * Save Course Information.
 */
function edu_platform_save_course_meta( $post_id ) {

    if ( ! isset( $_POST['edu_course_nonce'] ) ) {
        return;
    }

    if (
        ! wp_verify_nonce(
            sanitize_text_field( wp_unslash( $_POST['edu_course_nonce'] ) ),
            'edu_save_course_info'
        )
    ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $fields = array(
        'course_price'        => '_course_price',
        'course_level'        => '_course_level',
        'course_duration'     => '_course_duration',
        'course_instructor'   => '_course_instructor',
        'course_rating'       => '_course_rating',
        'course_review_count' => '_course_review_count',
        'course_lesson_count' => '_course_lesson_count',
    );

    foreach ( $fields as $field => $meta_key ) {

        if ( ! isset( $_POST[ $field ] ) ) {
            continue;
        }

        if (
            in_array(
                $field,
                array(
                    'course_rating',
                    'course_review_count',
                    'course_lesson_count',
                    'course_price',
                ),
                true
            )
        ) {
            $raw = wp_unslash( $_POST[ $field ] );

            if ( '' === $raw || null === $raw ) {
                delete_post_meta( $post_id, $meta_key );
                continue;
            }

            $value = is_numeric( $raw ) ? floatval( $raw ) : '';

            if ( 'course_review_count' === $field || 'course_lesson_count' === $field ) {
                $value = ( '' !== $value && $value >= 0 )
                    ? (int) round( $value )
                    : '';
            }

            if ( 'course_rating' === $field ) {
                $value = ( '' !== $value && $value >= 0 && $value <= 5 )
                    ? round( $value, 1 )
                    : '';
            }

            if ( 'course_price' === $field ) {
                $value = ( $value >= 0 ) ? $value : '';
            }

            if ( '' !== $value ) {
                update_post_meta( $post_id, $meta_key, $value );
            }

            continue;
        }

        $value = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );

        update_post_meta( $post_id, $meta_key, $value );
    }
}

add_action( 'save_post_course', 'edu_platform_save_course_meta' );
