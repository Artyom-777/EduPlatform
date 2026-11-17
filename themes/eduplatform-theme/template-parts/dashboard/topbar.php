<?php
/**
 * EduPlatform dashboard topbar.
 *
 * @package eduplatform-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_menu = eduplatform_dashboard_user_menu_links();
$avatar    = eduplatform_dashboard_avatar_url();
$name      = eduplatform_dashboard_welcome_name();

$courses_url  = eduplatform_courses_page_url();
$search_value = eduplatform_get_course_search_query();
?>

<header class="dashboard-topbar">

    <details class="dashboard-user-menu">
        <summary class="dashboard-user-menu-trigger">

            <div class="dashboard-topbar-user">

                <img
                    class="dashboard-avatar"
                    src="<?php echo esc_url( $avatar ); ?>"
                    alt="<?php esc_attr_e( 'User avatar', 'eduplatform' ); ?>"
                >

                <span class="dashboard-welcome">
                    <?php
                    printf(
                        esc_html__( 'Welcome, %s', 'eduplatform' ),
                        esc_html( $name )
                    );
                    ?>
                </span>

            </div>

            <span class="dashboard-user-chevron" aria-hidden="true"></span>

        </summary>

        <div class="dashboard-user-dropdown">

            <?php foreach ( $user_menu as $item ) : ?>

                <a
                    class="dashboard-user-dropdown-link<?php echo 'logout' === $item['key'] ? ' dashboard-user-dropdown-link--logout' : ''; ?>"
                    href="<?php echo esc_url( $item['url'] ); ?>"
                >
                    <?php echo esc_html( $item['label'] ); ?>
                </a>

            <?php endforeach; ?>

        </div>
    </details>

    <form
        class="dashboard-search"
        method="get"
        action="<?php echo esc_url( $courses_url ); ?>"
        role="search"
    >
        <span class="dashboard-search-icon" aria-hidden="true"></span>

        <input
            class="dashboard-search-input"
            type="search"
            name="course_search"
            placeholder="<?php esc_attr_e( 'Search courses...', 'eduplatform' ); ?>"
            value="<?php echo esc_attr( $search_value ); ?>"
        >
    </form>

    <div class="dashboard-topbar-actions">

        <a
            class="dashboard-icon-btn"
            href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"
            aria-label="<?php esc_attr_e( 'Contact', 'eduplatform' ); ?>"
        >
            <span class="dashboard-icon-btn-glyph dashboard-icon-btn-glyph--chat"></span>
        </a>

        <a
            class="dashboard-icon-btn"
            href="<?php echo esc_url( $courses_url ); ?>"
            aria-label="<?php esc_attr_e( 'Browse courses', 'eduplatform' ); ?>"
        >
            <span class="dashboard-icon-btn-glyph dashboard-icon-btn-glyph--bell"></span>
        </a>

    </div>

</header>
