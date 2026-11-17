<?php
/**
 * Dashboard: Active Courses.
 *
 * @package eduplatform-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$courses = eduplatform_dashboard_get_active_courses();
?>

<section class="dashboard-active-courses">

    <div class="dashboard-active-courses-head">
        <div>
            <h2>
                <?php esc_html_e( 'Active Courses', 'eduplatform' ); ?>
            </h2>

            <p>
                <?php esc_html_e( 'Your learning activity', 'eduplatform' ); ?>
            </p>
        </div>

        <span class="dashboard-active-courses-count">
            <?php echo esc_html( (string) count( $courses ) ); ?>
            <?php esc_html_e( 'Courses', 'eduplatform' ); ?>
        </span>
    </div>

    <?php if ( ! empty( $courses ) ) : ?>

        <div class="dashboard-active-courses-list">

            <?php foreach ( $courses as $course ) : ?>

                <?php
                get_template_part(
                    'template-parts/home/dashboard',
                    'active-course-card',
                    array(
                        'course_id' => $course->ID,
                    )
                );
                ?>

            <?php endforeach; ?>

        </div>

    <?php else : ?>

        <p>
            <?php esc_html_e( 'You have no active courses yet.', 'eduplatform' ); ?>
        </p>

    <?php endif; ?>

</section>