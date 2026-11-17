<?php
/**
 * Dashboard: My Courses.
 *
 * @package eduplatform-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$courses = eduplatform_dashboard_get_courses();
?>

<section class="dashboard-courses-section">

    <div class="dashboard-courses-head">
        <div class="dashboard-courses-heading">
            <span class="dashboard-section-label">
                <?php esc_html_e( 'YOUR LEARNING', 'eduplatform' ); ?>
            </span>

            <h1 class="dashboard-courses-title">
                <?php esc_html_e( 'My Courses', 'eduplatform' ); ?>
            </h1>

            <p class="dashboard-courses-subtitle">
                <?php esc_html_e( 'Continue learning and achieve your goals.', 'eduplatform' ); ?>
            </p>
        </div>

        <a
            class="dashboard-view-all"
            href="<?php echo esc_url( eduplatform_courses_page_url() ); ?>"
        >
            <?php esc_html_e( 'Explore all courses', 'eduplatform' ); ?>
            <span aria-hidden="true">→</span>
        </a>
    </div>

    <div class="dashboard-courses-grid">

        <?php if ( ! empty( $courses ) ) : ?>

            <?php

            foreach ( $courses as $course ) :
                get_template_part(
                    'template-parts/home/dashboard',
                    'course-card',
                    array(
                        'course_id' => $course->ID,
                    )
                );
            endforeach;

            ?>

        <?php else : ?>

            <div class="dashboard-courses-empty">
                <h3>
                    <?php esc_html_e( 'No courses yet', 'eduplatform' ); ?>
                </h3>

                <p>
                    <?php esc_html_e( 'Explore our courses and start learning today.', 'eduplatform' ); ?>
                </p>

                <a
                    class="dashboard-course-continue"
                    href="<?php echo esc_url( eduplatform_courses_page_url() ); ?>"
                >
                    <?php esc_html_e( 'Explore courses', 'eduplatform' ); ?>
                    <span aria-hidden="true">→</span>
                </a>
            </div>

        <?php endif; ?>

    </div>

</section>
