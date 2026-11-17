<?php
get_header();
?>

<main class="dashboard-content single-post-page" id="main-content">

    <div class="container single-post-container">

        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>

                <?php
                $referer       = wp_get_referer( false );
                $has_referer   = $referer && wp_validate_redirect( $referer, false );
                $back_fallback = home_url( '/' );
                $back_href     = $has_referer ? $referer : $back_fallback;
                ?>

                <p class="single-post-back-wrap">
                    <a
                        href="<?php echo esc_url( $back_href ); ?>"
                        class="single-post-back"
                        <?php if ( ! $has_referer ) : ?>
                            role="button"
                            onclick="event.preventDefault(); if (window.history.length > 1) { history.back(); } else { window.location.href = '<?php echo esc_js( $back_fallback ); ?>'; }"
                        <?php endif; ?>
                    >
                        <span class="single-post-back-icon" aria-hidden="true">←</span>
                        <?php esc_html_e( 'Go back', 'eduplatform' ); ?>
                    </a>
                </p>

                <article <?php post_class( 'single-post' ); ?>>

                    <header class="single-post-header">
                        <span class="hero-label"><?php esc_html_e( 'Article', 'eduplatform' ); ?></span>
                        <h1 class="single-post-title"><?php the_title(); ?></h1>
                    </header>

                    <?php if ( has_post_thumbnail() ) : ?>
                        <figure class="single-post-media">
                            <?php
                            the_post_thumbnail(
                                'large',
                                array(
                                    'class'   => 'single-post-featured-img',
                                    'loading' => 'eager',
                                )
                            );
                            ?>
                        </figure>
                    <?php endif; ?>

                    <div class="entry-content single-post-content">
                        <?php the_content(); ?>
                    </div>

                    <footer class="single-post-footer">
                        <div class="post-categories post-meta-block">
                            <span class="meta-label"><?php esc_html_e( 'Categories:', 'eduplatform' ); ?></span>
                            <?php the_category( ', ' ); ?>
                        </div>

                        <div class="post-tags post-meta-block">
                            <?php
                            the_tags(
                                '<span class="meta-label">' . esc_html__( 'Tags:', 'eduplatform' ) . ' </span>',
                                ', '
                            );
                            ?>
                        </div>
                    </footer>

                </article>

            <?php endwhile; ?>
        <?php endif; ?>

    </div>

</main>

<?php
get_footer();
