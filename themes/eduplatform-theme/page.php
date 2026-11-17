<?php
/**
 * Default page template (About, Contact, etc.).
 *
 * @package eduplatform-theme
 */

get_header();
?>

<main class="dashboard-content dashboard-page" id="main-content">

    <?php
    while ( have_posts() ) :
        the_post();
        ?>
        <article <?php post_class( 'dashboard-page-article' ); ?>>
            <h1 class="dashboard-page-title"><?php the_title(); ?></h1>
            <div class="dashboard-page-content entry-content">
                <?php the_content(); ?>
            </div>
        </article>
        <?php
    endwhile;
    ?>

</main>

<?php
get_footer();
