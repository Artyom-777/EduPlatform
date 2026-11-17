<?php


$locations = get_field('locations');


get_header();
?>

<section class="page">
    <div class="container">

        <h1><?php the_title(); ?></h1>

        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

            <?php the_content(); ?>


            <?php var_dump($locations); ?>

        <?php endwhile; else: endif; ?>

    </div>
</section>

<?php get_footer(); ?>
