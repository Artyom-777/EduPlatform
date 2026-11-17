<?php
/*
 * Template Name: Content Page
 */




get_header();
$image = get_field('feature_image');
$picture = $image['sizes']['large'];
$file = get_field('upload_a_file');
$embed = get_field('embed_a_video');

?>

<section class="page">
    <div class="container">

        <h1><?php the_title(); ?></h1>

        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

            <?php the_content(); ?>

        <?php endwhile; else: endif; ?>

        <?php if ($picture) : ?>
           <img src="<?php echo $picture;?>" alt="<?php the_title(); ?>" class="img-fluiddd">
        <?php endif; ?>

        <?php if ($file) : ?>
           <a href="<?php echo $file['url'];?>" download>Download the file</a>
        <?php endif; ?>

        <?php if ($embed) : ?>
           <div class="embed-container">
              <?php var_dump($embed);?>
           </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
