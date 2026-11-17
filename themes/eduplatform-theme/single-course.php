<?php get_header(); ?>

<main class="dashboard-content single-course-page" id="main-content">

    <div class="container single-course-container">

        <?php if ( have_posts() ) : ?>

            <?php while ( have_posts() ) : the_post(); ?>

                <?php
                    $back_fallback = get_post_type_archive_link( 'course' );

                    if ( ! $back_fallback ) {
                        $courses_page  = get_page_by_path( 'courses' );
                        $back_fallback = $courses_page ? get_permalink( $courses_page ) : home_url( '/courses/' );
                    }

                    $course_id    = get_the_ID();
                    $current_url  = get_permalink( $course_id );
                    $start_date = get_field( 'start_date', $course_id );
                    $end_date   = get_field( 'end_date', $course_id );
                    $referrer_url = wp_get_referer();
                    $back_url     = $referrer_url
                        ? wp_validate_redirect( $referrer_url, $back_fallback )
                        : $back_fallback;

                    if ( untrailingslashit( $back_url ) === untrailingslashit( $current_url ) ) {
                        $back_url = $back_fallback;
                    }
                ?>

                <p class="single-post-back-wrap single-course-back-wrap">
                    <a
                        href="<?php echo esc_url( $back_url ); ?>"
                        class="single-post-back"
                    >
                        <span class="single-post-back-icon" aria-hidden="true">←</span>
                        <?php esc_html_e( 'Go back', 'eduplatform' ); ?>
                    </a>
                </p>

                <article <?php post_class( 'single-course' ); ?>>

                    <header class="single-course-header">

                        <span class="hero-label">
                            <?php esc_html_e( 'Course Details', 'eduplatform' ); ?>
                        </span>

                        <h1 class="single-course-title">
                            <?php the_title(); ?>
                        </h1>

                        <?php if ( has_excerpt() ) : ?>
                            <p class="single-course-tagline">
                                <?php echo esc_html( get_the_excerpt() ); ?>
                            </p>
                        <?php endif; ?>

                        <?php if ( $start_date || $end_date ) : ?>

                        <div class="course-dates">

                            <?php if ( $start_date ) : ?>
                                <span class="course-start-date">
                                    <strong>Start Date:</strong>
                                    <?php
                                    $date = DateTimeImmutable::createFromFormat( '!Ymd', $start_date );

                                    if ( $date ) {
                                        echo esc_html( $date->format( get_option( 'date_format' ) ) );
                                    }
                                    ?>
                                </span>
                            <?php endif; ?>

                            <?php if ( $end_date ) : ?>
                                <span class="course-end-date">
                                    <strong>End Date:</strong>
                                    <?php
                                    $date = DateTimeImmutable::createFromFormat( '!Ymd', $start_date );

                                    if ( $date ) {
                                        echo esc_html( $date->format( get_option( 'date_format' ) ) );
                                    }
                                    ?>
                                </span>
                            <?php endif; ?>

                        </div>

                        <?php endif; ?>

                        <?php
                        $price        = get_post_meta( $course_id, '_course_price', true );
                        $level        = get_post_meta( $course_id, '_course_level', true );
                        $duration     = get_post_meta( $course_id, '_course_duration', true );
                        $instructor   = get_post_meta( $course_id, '_course_instructor', true );
                        $rating       = get_post_meta( $course_id, '_course_rating', true );
                        $review_count = get_post_meta( $course_id, '_course_review_count', true );

                        $rating_num  = is_numeric( $rating ) ? (float) $rating : 0;
                        $reviews_num = is_numeric( $review_count ) ? (int) $review_count : 0;

                        if ( $price !== '' || $level || $duration || $instructor || $rating_num > 0 ) :
                        ?>

                            <div class="course-meta course-meta--single">

                                <?php if ( $price !== '' || $level || $duration || $rating_num > 0 ) : ?>

                                    <div class="course-meta-row">

                                        <?php if ( $price !== '' ) : ?>
                                            <span class="course-price">
                                                <strong><?php esc_html_e( 'Price', 'eduplatform' ); ?></strong>
                                                $<?php echo esc_html( $price ); ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if ( $rating_num > 0 ) : ?>
                                            <span class="course-chip course-rating">
                                                <strong><?php esc_html_e( 'Rating', 'eduplatform' ); ?></strong>

                                                <?php
                                                echo esc_html( number_format_i18n( $rating_num, 1 ) );
                                                echo ' ★';

                                                if ( $reviews_num > 0 ) {
                                                    echo ' (' . esc_html( number_format_i18n( $reviews_num ) ) . ')';
                                                }
                                                ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if ( $level ) : ?>
                                            <span class="course-chip course-level">
                                                <strong><?php esc_html_e( 'Level', 'eduplatform' ); ?></strong>
                                                <?php echo esc_html( $level ); ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if ( $duration ) : ?>
                                            <span class="course-chip course-duration">
                                                <strong><?php esc_html_e( 'Duration', 'eduplatform' ); ?></strong>
                                                <?php echo esc_html( $duration ); ?>
                                            </span>
                                        <?php endif; ?>

                                    </div>

                                <?php endif; ?>

                                <?php if ( $instructor ) : ?>
                                    <div class="course-meta-instructor">
                                        <strong><?php esc_html_e( 'Instructor', 'eduplatform' ); ?></strong>
                                        <?php echo esc_html( $instructor ); ?>
                                    </div>
                                <?php endif; ?>

                            </div>

                        <?php endif; ?>

                        <div class="course-enrollment">

                            <?php if ( is_user_logged_in() ) : ?>

                                <?php if ( function_exists( 'edu_platform_user_is_enrolled' ) && edu_platform_user_is_enrolled( get_current_user_id(), $course_id ) ) : ?>

                                    <p class="course-enrollment-status course-enrollment-status--enrolled">
                                        <?php esc_html_e( 'You are enrolled in this course.', 'eduplatform' ); ?>
                                    </p>

                                <?php else : ?>

                                    <form class="course-enrollment-form" method="post">

                                        <?php wp_nonce_field( 'edu_enroll_course', 'edu_enroll_nonce' ); ?>

                                        <input
                                            type="hidden"
                                            name="course_id"
                                            value="<?php echo esc_attr( (string) $course_id ); ?>"
                                        >

                                        <button type="submit" name="edu_enroll_submit" class="course-enrollment-btn">
                                            <?php esc_html_e( 'Enroll Now', 'edu-platform' ); ?>
                                        </button>

                                    </form>

                                <?php endif; ?>

                            <?php else : ?>

                                <p class="course-enrollment-status course-enrollment-status--guest">
                                    <a
                                        class="course-enrollment-login"
                                        href="<?php echo esc_url( add_query_arg( 'redirect_to', rawurlencode( get_permalink( $course_id ) ), eduplatform_login_url() ) ); ?>"
                                    >
                                        <?php esc_html_e( 'Log in to enroll', 'eduplatform' ); ?>
                                    </a>
                                </p>

                            <?php endif; ?>

                            <?php if ( isset( $_GET['enrolled'] ) && '1' === $_GET['enrolled'] ) : ?>
                                <p class="course-enrollment-notice" role="status">
                                    <?php esc_html_e( 'Enrollment successful.', 'eduplatform' ); ?>
                                </p>
                            <?php endif; ?>

                        </div>

                    </header>

                    <div class="single-course-body">

                        <?php if ( '' !== trim( get_post()->post_content ) ) : ?>

                            <div class="single-course-description entry-content">
                                <?php the_content(); ?>
                            </div>

                        <?php endif; ?>

                    </div>

                    <?php if ( eduplatform_course_has_display_image() ) : ?>

                        <figure class="single-course-media">
                            <?php eduplatform_the_course_hero_image( 'full' ); ?>
                        </figure>

                    <?php endif; ?>


                    <!-- Course Lessons -->

                    <?php
                    $lessons_query = new WP_Query(
                        array(
                            'post_type'      => 'lesson',
                            'post_status'    => 'publish',
                            'posts_per_page' => -1,

                            'meta_query'     => array(
                                array(
                                    'key'     => '_lesson_course_id',
                                    'value'   => $course_id,
                                    'compare' => '=',
                                ),
                            ),

                            'meta_key'       => '_lesson_order',
                            'orderby'        => 'meta_value_num',
                            'order'          => 'ASC',
                        )
                    );
                    ?>

                    <section class="course-lessons">

                        <h2 class="course-lessons-title">
                            <?php esc_html_e( 'Course Lessons', 'eduplatform' ); ?>
                        </h2>

                        <?php if ( $lessons_query->have_posts() ) : ?>

                            <div class="course-lessons-list">

                                <?php while ( $lessons_query->have_posts() ) : ?>

                                    <?php $lessons_query->the_post(); ?>

                                    <a
                                        href="<?php the_permalink(); ?>"
                                        class="course-lesson-item"
                                    >

                                        <span class="course-lesson-number">
                                            <?php
                                            echo esc_html(
                                                get_post_meta( get_the_ID(), '_lesson_order', true )
                                            );
                                            ?>
                                        </span>

                                        <span class="course-lesson-name">
                                            <?php the_title(); ?>
                                        </span>

                                        <span class="course-lesson-arrow" aria-hidden="true">
                                            →
                                        </span>

                                    </a>

                                <?php endwhile; ?>

                            </div>

                            <?php wp_reset_postdata(); ?>

                        <?php else : ?>

                            <p class="course-lessons-empty">
                                <?php esc_html_e( 'No lessons available yet.', 'eduplatform' ); ?>
                            </p>

                        <?php endif; ?>

                    </section>

                </article>

            <?php endwhile; ?>

        <?php endif; ?>

    </div>

</main>

<?php get_footer(); ?>
