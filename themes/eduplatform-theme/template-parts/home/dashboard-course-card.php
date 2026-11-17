<?php
/**
 * One course card.
 *
 * Uses the current post or a passed course ID.
 *
 * @package eduplatform-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

        /**
         * Get course data.
         */
        $course_id = isset( $args['course_id'] )
            ? absint( $args['course_id'] )
            : get_the_ID();

        $course_url     = get_permalink( $course_id );
        $course_title   = get_the_title( $course_id );
        $course_excerpt = wp_trim_words(
            get_post_field( 'post_content', $course_id ),
            18
        );

        /**
         * Get course dates from ACF.
         */
        $start_date_raw = function_exists( 'get_field' )
            ? get_field( 'start_date', $course_id )
            : get_post_meta( $course_id, 'start_date', true );
        $end_date_raw   = function_exists( 'get_field' )
            ? get_field( 'end_date', $course_id )
            : get_post_meta( $course_id, 'end_date', true );

        $start_date = '';
        $end_date   = '';

        $format_course_date = static function ( $raw_date ) {
            if ( ! $raw_date ) {
                return '';
            }

            foreach ( array( '!Ymd', '!Y-m-d', '!d/m/Y' ) as $format ) {
                $date = DateTimeImmutable::createFromFormat( $format, (string) $raw_date );

                if ( $date ) {
                    return $date->format( 'd/m/Y' );
                }
            }

            return '';
        };

        $start_date = $format_course_date( $start_date_raw );
        $end_date   = $format_course_date( $end_date_raw );

        /**
         * Get course image.
         *
         * Priority:
         * 1. Featured Image.
         * 2. First Gutenberg Image block.
         */

        $course_image = '';

        if ( has_post_thumbnail( $course_id ) ) {

            $course_image = get_the_post_thumbnail(
                $course_id,
                'large',
                array(
                    'class'   => 'dashboard-course-thumbnail',
                    'loading' => 'lazy',
                )
            );

        } else {

    $content = get_post_field( 'post_content', $course_id );
    $blocks  = parse_blocks( $content );

    /**
     * Recursively find the first Image block.
     */
    $find_image_id = function ( $blocks ) use ( &$find_image_id ) {

        foreach ( $blocks as $block ) {

            if (
                'core/image' === $block['blockName'] &&
                ! empty( $block['attrs']['id'] )
            ) {
                return absint( $block['attrs']['id'] );
            }

            if ( ! empty( $block['innerBlocks'] ) ) {

                $image_id = $find_image_id(
                    $block['innerBlocks']
                );

                if ( $image_id ) {
                    return $image_id;
                }
            }
        }

        return 0;
    };

    $image_id = $find_image_id( $blocks );

    if ( $image_id ) {

        $course_image = wp_get_attachment_image(
            $image_id,
            'large',
            false,
            array(
                'class'   => 'dashboard-course-thumbnail',
                'loading' => 'lazy',
            )
        );
    }
}
?>

<article class="dashboard-course-card">

    <a
        class="dashboard-course-image"
        href="<?php echo esc_url( $course_url ); ?>"
        aria-label="<?php echo esc_attr( $course_title ); ?>"
    >

        <?php if ( $course_image ) : ?>

            <?php
            echo $course_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>

        <?php else : ?>

            <div class="dashboard-course-image-placeholder">
                <span>EduPlatform</span>
            </div>

        <?php endif; ?>

        <span class="dashboard-course-image-arrow" aria-hidden="true">
            ↗
        </span>

    </a>

    <div class="dashboard-course-card-content">

        <div class="dashboard-course-card-info">

            <h3 class="dashboard-course-card-title">
                <a href="<?php echo esc_url( $course_url ); ?>">
                    <?php echo esc_html( $course_title ); ?>
                </a>
            </h3>

            <p class="dashboard-course-card-description">
                <?php echo esc_html( $course_excerpt ); ?>
            </p>

            <?php if ( $start_date || $end_date ) : ?>

            <div class="dashboard-course-card-dates">

                <?php if ( $start_date ) : ?>
                    <div class="dashboard-course-card-date dashboard-course-card-date--start">
                        <span class="dashboard-course-card-date-icon" aria-hidden="true"></span>
                        <span class="dashboard-course-card-date-copy">
                            <span><?php esc_html_e( 'Start Date', 'eduplatform' ); ?></span>
                            <strong><?php echo esc_html( $start_date ); ?></strong>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ( $end_date ) : ?>
                    <div class="dashboard-course-card-date dashboard-course-card-date--end">
                        <span class="dashboard-course-card-date-icon" aria-hidden="true"></span>
                        <span class="dashboard-course-card-date-copy">
                            <span><?php esc_html_e( 'End Date', 'eduplatform' ); ?></span>
                            <strong><?php echo esc_html( $end_date ); ?></strong>
                        </span>
                    </div>
                <?php endif; ?>

            </div>

<?php endif; ?>

        </div>

        <div class="dashboard-course-card-footer">

            <a
                class="dashboard-course-continue"
                href="<?php echo esc_url( $course_url ); ?>"
            >
                <?php esc_html_e( 'Continue learning', 'eduplatform' ); ?>
                <span aria-hidden="true">→</span>
            </a>

        </div>

    </div>

</article>
