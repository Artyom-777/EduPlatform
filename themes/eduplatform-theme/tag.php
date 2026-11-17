<?php
/**
 * Tag archive template.
 *
 * @package My_Custom_Theme
 */

get_header();
?>

<main class="container tag-archive">

    <header class="archive-header">
        <h1>
            Tag: <?php single_tag_title(); ?>
        </h1>

        <?php the_archive_description(); ?>
    </header>

    <?php if (have_posts()) : ?>

        <div class="posts-list">

            <?php while (have_posts()) : the_post(); ?>

                <article <?php post_class('post-card'); ?>>

                    <?php if (has_post_thumbnail()) : ?>
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail('medium'); ?>
                        </a>
                    <?php endif; ?>

                    <h2>
                        <a href="<?php the_permalink(); ?>">
                            <?php the_title(); ?>
                        </a>
                    </h2>

                    <?php the_excerpt(); ?>

                </article>

            <?php endwhile; ?>

        </div>

        <?php the_posts_pagination(); ?>

    <?php else : ?>

        <p>No posts found for this tag.</p>

    <?php endif; ?>

</main>

<?php get_footer(); ?>
