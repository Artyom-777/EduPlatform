<?php
/**
 * Courses archive — dashboard layout.
 *
 * @package eduplatform-theme
 */

get_header();

$archive_url = get_post_type_archive_link( 'course' );
if ( ! $archive_url ) {
    $archive_url = home_url( '/courses/' );
}
?>

<div class="dashboard-shell">

    <?php get_template_part( 'template-parts/dashboard/sidebar' ); ?>

    <div class="dashboard-main">

        <?php get_template_part( 'template-parts/dashboard/topbar' ); ?>

<main class="dashboard-content dashboard-courses-page" id="main-content">

    <header class="dashboard-courses-head">
        <div>
            <h1 class="dashboard-courses-title"><?php esc_html_e( 'Courses', 'eduplatform' ); ?></h1>
            <p class="dashboard-courses-subtitle">
                <?php esc_html_e( 'Explore and continue your learning journey.', 'eduplatform' ); ?>
            </p>
        </div>
    </header>

    <?php eduplatform_dashboard_category_filters( $archive_url ); ?>

    <?php if ( have_posts() ) : ?>

        <div class="dashboard-active-courses-list dashboard-course-catalog-grid">
            <?php
            while ( have_posts() ) :
                the_post();
                get_template_part(
                    'template-parts/home/dashboard',
                    'active-course-card',
                    array(
                        'course_id' => get_the_ID(),
                    )
                );
            endwhile;
            ?>
        </div>

        <nav class="dashboard-pagination" aria-label="<?php esc_attr_e( 'Courses pagination', 'eduplatform' ); ?>">
            <?php
            the_posts_pagination(
                array(
                    'mid_size'  => 2,
                    'prev_text' => __( '← Previous', 'eduplatform' ),
                    'next_text' => __( 'Next →', 'eduplatform' ),
                )
            );
            ?>
        </nav>

    <?php else : ?>

        <p class="dashboard-empty"><?php esc_html_e( 'No courses found.', 'eduplatform' ); ?></p>

    <?php endif; ?>

</main>

    </div>

</div>

<?php
get_footer();
