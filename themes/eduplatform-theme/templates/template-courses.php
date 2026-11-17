<?php
/**
 * Template Name: Courses
 * Template Post Type: page
 *
 * Courses page. Lists published course posts inside the dashboard.
 *
 * @package eduplatform-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$archive_url = get_permalink();
if ( ! $archive_url ) {
    $archive_url = home_url( '/courses/' );
}

$search_q      = eduplatform_get_course_search_query();
$courses_query = new WP_Query( eduplatform_courses_query_args() );
?>

<div class="dashboard-shell">

    <?php get_template_part( 'template-parts/dashboard/sidebar' ); ?>

    <div class="dashboard-main">

        <?php get_template_part( 'template-parts/dashboard/topbar' ); ?>

        <main id="main-content" class="dashboard-content dashboard-courses-page">

            <header class="dashboard-courses-head">
                <div>
                    <h1 class="dashboard-courses-title"><?php esc_html_e( 'Courses', 'eduplatform' ); ?></h1>
                    <p class="dashboard-courses-subtitle">
                        <?php
                        if ( '' !== $search_q ) {
                            printf(
                                /* translators: %s: search keywords */
                                esc_html__( 'Results for “%s”.', 'eduplatform' ),
                                esc_html( $search_q )
                            );
                        } else {
                            esc_html_e( 'Explore and continue your learning journey.', 'eduplatform' );
                        }
                        ?>
                    </p>
                </div>
            </header>

            <?php eduplatform_dashboard_category_filters( $archive_url ); ?>

            <?php if ( $courses_query->have_posts() ) : ?>

                <div class="dashboard-active-courses-list dashboard-course-catalog-grid">
                    <?php
                    while ( $courses_query->have_posts() ) :
                        $courses_query->the_post();
                        get_template_part(
                            'template-parts/home/dashboard',
                            'active-course-card',
                            array(
                                'course_id' => get_the_ID(),
                            )
                        );
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>

            <?php else : ?>

                <p class="dashboard-empty">
                    <?php
                    if ( '' !== $search_q ) {
                        esc_html_e( 'No courses match your search. Try different keywords.', 'eduplatform' );
                    } else {
                        esc_html_e( 'No courses found.', 'eduplatform' );
                    }
                    ?>
                </p>

            <?php endif; ?>

        </main>

    </div>

</div>

<?php
get_footer();
