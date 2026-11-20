<?php
/**
 * Add Course selection Meta Box to Lessons.
 */
function edu_platform_add_lesson_course_meta_box() {

    add_meta_box(
        'edu_lesson_course',
        'Select Course',
        'edu_platform_lesson_course_meta_box_html',
        'lesson',
        'side',
        'high'
    );
}

add_action( 'add_meta_boxes', 'edu_platform_add_lesson_course_meta_box' );


/**
 * Display Course selection field.
 */
function edu_platform_lesson_course_meta_box_html( $post ) {

    wp_nonce_field( 'edu_save_lesson_course', 'edu_lesson_course_nonce' );

    $selected_course = get_post_meta(
        $post->ID,
        '_lesson_course_id',
        true
    );

    $courses = get_posts(
        array(
            'post_type'      => 'course',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        )
    );
    ?>

    <select name="lesson_course_id" style="width:100%;">
        <option value="">Select a Course</option>

        <?php foreach ( $courses as $course ) : ?>
            <option
                value="<?php echo esc_attr( $course->ID ); ?>"
                <?php selected( $selected_course, $course->ID ); ?>
            >
                <?php echo esc_html( $course->post_title ); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php
}


/**
 * Save Lesson Course selection.
 */
function edu_platform_save_lesson_course( $post_id ) {

    if ( ! isset( $_POST['edu_lesson_course_nonce'] ) ) {
        return;
    }

    if (
        ! wp_verify_nonce(
            sanitize_text_field(
                wp_unslash( $_POST['edu_lesson_course_nonce'] )
            ),
            'edu_save_lesson_course'
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

    $course_id = isset( $_POST['lesson_course_id'] )
        ? absint( $_POST['lesson_course_id'] )
        : 0;

    if ( $course_id && 'course' === get_post_type( $course_id ) ) {
        update_post_meta( $post_id, '_lesson_course_id', $course_id );
    } else {
        delete_post_meta( $post_id, '_lesson_course_id' );
    }
}

add_action( 'save_post_lesson', 'edu_platform_save_lesson_course' );


/**
 * Add Lesson Order Meta Box.
 */
function edu_platform_add_lesson_order_meta_box() {

    add_meta_box(
        'edu_lesson_order',
        'Lesson Order',
        'edu_platform_lesson_order_meta_box_html',
        'lesson',
        'side',
        'high'
    );
}

add_action( 'add_meta_boxes', 'edu_platform_add_lesson_order_meta_box' );


/**
 * Display Lesson Order field.
 */
function edu_platform_lesson_order_meta_box_html( $post ) {

    wp_nonce_field( 'edu_save_lesson_order', 'edu_lesson_order_nonce' );

    $lesson_order = get_post_meta(
        $post->ID,
        '_lesson_order',
        true
    );
    ?>

    <p>
        <label for="lesson_order">
            <?php esc_html_e( 'Lesson Position', 'edu-platform' ); ?>
        </label>
    </p>

    <input
        type="number"
        id="lesson_order"
        name="lesson_order"
        value="<?php echo esc_attr( $lesson_order ); ?>"
        min="1"
        step="1"
        style="width:100%;"
    >

    <?php
}


/**
 * Save Lesson Order.
 */
function edu_platform_save_lesson_order( $post_id ) {

    if ( ! isset( $_POST['edu_lesson_order_nonce'] ) ) {
        return;
    }

    if (
        ! wp_verify_nonce(
            sanitize_text_field(
                wp_unslash( $_POST['edu_lesson_order_nonce'] )
            ),
            'edu_save_lesson_order'
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

    $order = isset( $_POST['lesson_order'] )
        ? absint( $_POST['lesson_order'] )
        : 0;

    if ( $order > 0 ) {
        update_post_meta( $post_id, '_lesson_order', $order );
    } else {
        delete_post_meta( $post_id, '_lesson_order' );
    }
}

add_action( 'save_post_lesson', 'edu_platform_save_lesson_order' );
