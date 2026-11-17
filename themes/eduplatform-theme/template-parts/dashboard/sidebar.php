<?php
/**
 * EduPlatform dashboard sidebar.
 *
 * @package eduplatform-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$nav_items = eduplatform_dashboard_nav_items();
$widget    = eduplatform_dashboard_sidebar_widget_data();
$countdown = eduplatform_dashboard_month_end_countdown();
?>

<aside class="dashboard-sidebar">

    <a class="dashboard-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
        <span class="dashboard-brand-mark">E</span>
        <span>EduPlatform</span>
    </a>

    <nav class="dashboard-navigation" aria-label="<?php esc_attr_e( 'Dashboard navigation', 'eduplatform' ); ?>">
        <ul class="dashboard-nav-list">
            <?php foreach ( $nav_items as $item ) : ?>
                <li>
                    <a
                        class="dashboard-nav-link<?php echo ! empty( $item['active'] ) ? ' is-active' : ''; ?>"
                        href="<?php echo esc_url( $item['url'] ); ?>"
                    >
                        <span
                            class="dashboard-nav-icon dashboard-nav-icon--<?php echo esc_attr( $item['icon'] ); ?>"
                            aria-hidden="true"
                        ></span>

                        <span><?php echo esc_html( $item['label'] ); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

</aside>
