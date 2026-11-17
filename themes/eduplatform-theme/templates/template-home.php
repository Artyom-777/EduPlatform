<?php
/**
 * Template Name: EduPlatform Home
 * Template Post Type: page
 *
 * @package eduplatform-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

?>

<div class="dashboard-shell">

    <?php get_template_part( 'template-parts/dashboard/sidebar' ); ?>

    <div class="dashboard-main">

        <?php get_template_part( 'template-parts/dashboard/topbar' ); ?>

        <main id="main-content" class="edu-home dashboard-content">

            <?php get_template_part( 'template-parts/home/dashboard-my-courses' ); ?>

            <?php get_template_part( 'template-parts/home/dashboard-active-courses' ); ?>

            <?php get_template_part( 'template-parts/home/dashboard-insights' ); ?>

        </main>

    </div>

</div>

<?php
get_footer();
