<?php
get_header();

if ( have_posts() ) :
    while ( have_posts() ) :
        the_post();
        ?>

        <main class="single-lesson">
            <div class="container">

                <?php
                $lesson_id = get_the_ID();

                // Get the Course ID connected to this Lesson.
                $course_id = get_post_meta(
                    $lesson_id,
                    '_lesson_course_id',
                    true
                );

                $course_url = $course_id
                    ? get_permalink( $course_id )
                    : get_post_type_archive_link( 'course' );
                ?>

                <p class="single-post-back-wrap">
                    <a href="<?php echo esc_url( $course_url ); ?>"
                    class="single-post-back">
                        <span aria-hidden="true">←</span>
                        Back to Course
                    </a>
                </p>

                <h1><?php the_title(); ?></h1>

                    <div class="lesson-content">
                        <?php the_content(); ?>
                    </div>

                <?php
                // Get the current lesson information.
                $current_lesson_id = get_the_ID();
                $current_course_id = get_post_meta(
                    $current_lesson_id,
                    '_lesson_course_id',
                    true
                );

                $current_order = (int) get_post_meta(
                    $current_lesson_id,
                    '_lesson_order',
                    true
                );

                // Get all lessons belonging to this course.
                $lessons = new WP_Query(
                    array(
                        'post_type'      => 'lesson',
                        'post_status'    => 'publish',
                        'posts_per_page' => -1,
                        'meta_query'     => array(
                            array(
                                'key'     => '_lesson_course_id',
                                'value'   => $current_course_id,
                                'compare' => '=',
                            ),
                        ),
                        'meta_key'       => '_lesson_order',
                        'orderby'        => 'meta_value_num',
                        'order'          => 'ASC',
                    )
                );

                $lesson_ids = wp_list_pluck( $lessons->posts, 'ID' );

                $current_index = array_search(
                    $current_lesson_id,
                    $lesson_ids,
                    true
                );

                $previous_lesson = false;
                $next_lesson     = false;

                if ( false !== $current_index ) {
                    if ( $current_index > 0 ) {
                        $previous_lesson = get_post( $lesson_ids[ $current_index - 1 ] );
                    }

                    if ( $current_index < count( $lesson_ids ) - 1 ) {
                        $next_lesson = get_post( $lesson_ids[ $current_index + 1 ] );
                    }
                }

                wp_reset_postdata();
                ?>

                <div class="lesson-navigation">

                    <?php if ( $previous_lesson ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $previous_lesson ) ); ?>">
                            ← Previous Lesson
                        </a>
                    <?php endif; ?>

                    <?php if ( $next_lesson ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $next_lesson ) ); ?>">
                            Next Lesson →
                        </a>
                    <?php endif; ?>

                </div>

                    </div>
            </main>

            <?php
        endwhile;
    endif;

    get_footer();
