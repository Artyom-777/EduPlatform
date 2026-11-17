<?php
/**
 * Reusable Active Course card.
 *
 * @package eduplatform-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$course_id = isset( $args['course_id'] ) ? absint( $args['course_id'] ) : get_the_ID();

if ( ! $course_id ) {
    return;
}

$course_url  = get_permalink( $course_id );
$price       = get_post_meta( $course_id, '_course_price', true );
$duration    = get_post_meta( $course_id, '_course_duration', true );
$rating      = get_post_meta( $course_id, '_course_rating', true );
$level       = get_post_meta( $course_id, '_course_level', true );
$instructor  = get_post_meta( $course_id, '_course_instructor', true );
$rating_num  = is_numeric( $rating ) ? (float) $rating : 0;
$image_id    = has_post_thumbnail( $course_id )
    ? (int) get_post_thumbnail_id( $course_id )
    : (int) eduplatform_get_course_content_image_id( $course_id );

if ( is_numeric( $price ) ) {
    $price_decimals = ( (float) $price === floor( (float) $price ) ) ? 0 : 2;
    $price_label    = '$' . number_format_i18n( (float) $price, $price_decimals );
} else {
    $price_label = '';
}
?>

<article class="dashboard-active-course">

    <a
        class="dashboard-active-course-image"
        href="<?php echo esc_url( $course_url ); ?>"
        aria-label="<?php echo esc_attr( get_the_title( $course_id ) ); ?>"
    >
        <?php if ( $image_id ) : ?>
            <?php
            echo wp_get_attachment_image(
                $image_id,
                'large',
                false,
                array(
                    'class'   => 'dashboard-active-course-thumbnail',
                    'loading' => 'lazy',
                    'alt'     => get_the_title( $course_id ),
                )
            );
            ?>
        <?php else : ?>
            <span class="dashboard-active-course-image-placeholder">EduPlatform</span>
        <?php endif; ?>
    </a>

    <div class="dashboard-active-course-body">

        <h3 class="dashboard-active-course-name">
            <a href="<?php echo esc_url( $course_url ); ?>">
                <?php echo esc_html( get_the_title( $course_id ) ); ?>
            </a>
        </h3>

        <p class="dashboard-active-course-instructor">
            <span><?php esc_html_e( 'Instructor', 'eduplatform' ); ?></span>
            <?php echo esc_html( $instructor ? $instructor : '—' ); ?>
        </p>

        <dl class="dashboard-active-course-meta">
            <div>
                <dt><?php esc_html_e( 'Price', 'eduplatform' ); ?></dt>
                <dd><?php echo esc_html( $price_label ? $price_label : '—' ); ?></dd>
            </div>

            <div>
                <dt><?php esc_html_e( 'Duration', 'eduplatform' ); ?></dt>
                <dd><?php echo esc_html( $duration ? $duration : '—' ); ?></dd>
            </div>

            <div>
                <dt><?php esc_html_e( 'Rating', 'eduplatform' ); ?></dt>
                <dd>
                    <?php
                    echo $rating_num > 0
                        ? esc_html( number_format_i18n( $rating_num, 1 ) ) . ' ★'
                        : '—';
                    ?>
                </dd>
            </div>

            <div>
                <dt><?php esc_html_e( 'Level', 'eduplatform' ); ?></dt>
                <dd><?php echo esc_html( $level ? $level : '—' ); ?></dd>
            </div>
        </dl>

    </div>

</article>
