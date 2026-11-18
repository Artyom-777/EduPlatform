<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function eduplatform_theme_setup() {

    add_theme_support( 'title-tag' );

    add_theme_support( 'post-thumbnails' );

    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );

    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'eduplatform' ),
    ) );

}

add_action( 'after_setup_theme', 'eduplatform_theme_setup' );



function eduplatform_enqueue_assets() {

    // Main compiled CSS.
    $main_css_path = get_template_directory() . '/assets/css/main.css';
    $main_css_ver  = file_exists( $main_css_path )
        ? (string) filemtime( $main_css_path )
        : false;

    wp_enqueue_style(
        'eduplatform-main',
        get_template_directory_uri() . '/assets/css/main.css',
        array(),
        $main_css_ver
    );

    // Dashboard user menu JavaScript.
    $dash_js_path = get_template_directory() . '/assets/js/dashboard-user-menu.js';

    if ( file_exists( $dash_js_path ) ) {
        wp_enqueue_script(
            'eduplatform-dashboard-user-menu',
            get_template_directory_uri() . '/assets/js/dashboard-user-menu.js',
            array(),
            (string) filemtime( $dash_js_path ),
            true
        );
    }

    // Stripe Checkout scripts: course pages only.
    if ( is_singular( 'course' ) ) {

        $unenrollment_js_path = get_template_directory() . '/assets/js/course-unenrollment.js';

        if ( file_exists( $unenrollment_js_path ) ) {
            wp_enqueue_script(
                'eduplatform-course-unenrollment',
                get_template_directory_uri() . '/assets/js/course-unenrollment.js',
                array(),
                (string) filemtime( $unenrollment_js_path ),
                true
            );
        }

        wp_enqueue_script(
            'stripe-js',
            'https://js.stripe.com/v3/',
            array(),
            null,
            true
        );

        $checkout_js_path = get_template_directory() . '/assets/js/course-checkout.js';

        if ( file_exists( $checkout_js_path ) ) {
            wp_enqueue_script(
                'eduplatform-course-checkout',
                get_template_directory_uri() . '/assets/js/course-checkout.js',
                array( 'stripe-js' ),
                (string) filemtime( $checkout_js_path ),
                true
            );

            wp_localize_script(
                'eduplatform-course-checkout',
                'eduPlatformCheckout',
                array(
                    'restUrl'       => esc_url_raw( rest_url( 'edu-platform/v1/' ) ),
                    'nonce'         => wp_create_nonce( 'wp_rest' ),
                    'publishableKey' => defined( 'EDU_PLATFORM_STRIPE_PUBLISHABLE_KEY' )
                        ? EDU_PLATFORM_STRIPE_PUBLISHABLE_KEY
                        : '',
                )
            );
        }
    }
}


/**
 * Body class for global dashboard layout.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function eduplatform_dashboard_body_class( $classes ) {
    if ( eduplatform_is_auth_page() ) {
        $classes[] = 'edu-auth-layout';

        return $classes;
    }

    $classes[] = 'edu-dashboard-layout';

    if ( is_front_page() ) {
        $classes[] = 'edu-dashboard-home';
    }

    if ( is_post_type_archive( 'course' ) || is_tax( 'course_category' ) ) {
        $classes[] = 'edu-dashboard-courses';
    }

    return $classes;
}

add_filter( 'body_class', 'eduplatform_dashboard_body_class' );

/**
 * Login page URL (theme page slug login).
 *
 * @return string
 */
    function eduplatform_login_url() {
        $page = get_page_by_path( 'login' );
        if ( $page ) {
            return get_permalink( $page );
        }

        return wp_login_url( home_url( '/' ) );
    }

    /**
     * Register page URL.
     *
     * @return string
     */
    function eduplatform_register_url() {
        $page = get_page_by_path( 'register' );
        if ( $page ) {
            return get_permalink( $page );
        }

        return wp_registration_url();
    }

/**
 * Whether the current request is login or register.
 *
 * @return bool
 */
function eduplatform_is_auth_page() {
    return is_page( array( 'login', 'register' ) );
}

/**
 * Create login/register pages and allow registration (once).
 */
function eduplatform_ensure_auth_pages() {
    $pages = array(
        'login'    => __( 'Login', 'eduplatform' ),
        'register' => __( 'Register', 'eduplatform' ),
    );

    foreach ( $pages as $slug => $title ) {
        $existing = get_page_by_path( $slug );
        if ( ! $existing ) {
            wp_insert_post(
                array(
                    'post_title'   => $title,
                    'post_name'      => $slug,
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'post_content'   => '',
                    'comment_status' => 'closed',
                )
            );
        }
    }

    if ( ! get_option( 'users_can_register' ) ) {
        update_option( 'users_can_register', 1 );
    }

    update_option( 'eduplatform_auth_pages_created', 1 );
}

add_action( 'after_switch_theme', 'eduplatform_ensure_auth_pages' );

add_action(
    'init',
    function () {
        if ( ! get_option( 'eduplatform_auth_pages_created' ) ) {
            eduplatform_ensure_auth_pages();
        }
    },
    20
);

/**
 * Guests may only view login and register; dashboard requires login.
 */
function eduplatform_gate_dashboard_for_guests() {
    if ( is_user_logged_in() ) {
        return;
    }

    if ( is_admin() ) {
        return;
    }

    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return;
    }

    if ( eduplatform_is_auth_page() ) {
        return;
    }

    $login_url = add_query_arg(
        'redirect_to',
        rawurlencode( eduplatform_get_current_url() ),
        eduplatform_login_url()
    );

    wp_safe_redirect( $login_url );
    exit;
}

add_action( 'template_redirect', 'eduplatform_gate_dashboard_for_guests', 1 );

/**
 * Logged-in users should not see login/register forms.
 */
function eduplatform_redirect_authenticated_from_auth() {
    if ( ! is_user_logged_in() || ! eduplatform_is_auth_page() ) {
        return;
    }

    wp_safe_redirect( home_url( '/' ) );
    exit;
}

add_action( 'template_redirect', 'eduplatform_redirect_authenticated_from_auth', 2 );

/**
 * Current full URL for redirect after login.
 *
 * @return string
 */
function eduplatform_get_current_url() {
    $host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
    $uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
    if ( '' === $host ) {
        return home_url( '/' );
    }

    $scheme = is_ssl() ? 'https' : 'http';

    return $scheme . '://' . $host . $uri;
}

/**
 * Handle custom login form POST.
 */
function eduplatform_handle_login_post() {
    if ( ! isset( $_POST['eduplatform_login_submit'] ) ) {
        return;
    }

    if ( ! isset( $_POST['eduplatform_login_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eduplatform_login_nonce'] ) ), 'eduplatform_login' ) ) {
        wp_safe_redirect( add_query_arg( 'login_error', 'security', eduplatform_login_url() ) );
        exit;
    }

    $username = isset( $_POST['log'] ) ? sanitize_text_field( wp_unslash( $_POST['log'] ) ) : '';
    $password = isset( $_POST['pwd'] ) ? (string) wp_unslash( $_POST['pwd'] ) : '';
    $remember = ! empty( $_POST['rememberme'] );

    if ( '' === $username || '' === $password ) {
        wp_safe_redirect( add_query_arg( 'login_error', 'empty', eduplatform_login_url() ) );
        exit;
    }

    $user = wp_signon(
        array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember,
        ),
        is_ssl()
    );

    if ( is_wp_error( $user ) ) {
        wp_safe_redirect( add_query_arg( 'login_error', 'invalid', eduplatform_login_url() ) );
        exit;
    }

    $redirect = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : home_url( '/' );
    if ( ! $redirect || ! wp_validate_redirect( $redirect, false ) ) {
        $redirect = home_url( '/' );
    }

    wp_safe_redirect( $redirect );
    exit;
}

add_action( 'template_redirect', 'eduplatform_handle_login_post', 0 );

/**
 * Handle custom register form POST.
 */
function eduplatform_handle_register_post() {
    if ( ! isset( $_POST['eduplatform_register_submit'] ) ) {
        return;
    }

    if ( ! get_option( 'users_can_register' ) ) {
        wp_safe_redirect( add_query_arg( 'register_error', 'disabled', eduplatform_register_url() ) );
        exit;
    }

    if ( ! isset( $_POST['eduplatform_register_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eduplatform_register_nonce'] ) ), 'eduplatform_register' ) ) {
        wp_safe_redirect( add_query_arg( 'register_error', 'security', eduplatform_register_url() ) );
        exit;
    }

    $username = isset( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ), true ) : '';
    $email    = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
    $password = isset( $_POST['user_pass'] ) ? (string) wp_unslash( $_POST['user_pass'] ) : '';

    if ( '' === $username || '' === $email || '' === $password ) {
        wp_safe_redirect( add_query_arg( 'register_error', 'empty', eduplatform_register_url() ) );
        exit;
    }

    if ( ! is_email( $email ) ) {
        wp_safe_redirect( add_query_arg( 'register_error', 'email', eduplatform_register_url() ) );
        exit;
    }

    if ( strlen( $password ) < 6 ) {
        wp_safe_redirect( add_query_arg( 'register_error', 'weak', eduplatform_register_url() ) );
        exit;
    }

    if ( username_exists( $username ) ) {
        wp_safe_redirect( add_query_arg( 'register_error', 'username', eduplatform_register_url() ) );
        exit;
    }

    if ( email_exists( $email ) ) {
        wp_safe_redirect( add_query_arg( 'register_error', 'email_exists', eduplatform_register_url() ) );
        exit;
    }

    $user_id = wp_create_user( $username, $password, $email );

    if ( is_wp_error( $user_id ) ) {
        wp_safe_redirect( add_query_arg( 'register_error', 'failed', eduplatform_register_url() ) );
        exit;
    }

    wp_set_current_user( $user_id );
    wp_set_auth_cookie( $user_id, true );

    wp_safe_redirect( home_url( '/' ) );
    exit;
}

add_action( 'template_redirect', 'eduplatform_handle_register_post', 0 );

add_action(
    'wp_enqueue_scripts',
    'eduplatform_enqueue_assets'
);

function eduplatform_primary_menu_fallback() {
    $courses_url = get_post_type_archive_link( 'course' );
    if ( ! $courses_url ) {
        $courses_url = home_url( '/courses/' );
    }

    echo '<ul id="primary-menu" class="primary-menu">';
    echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'eduplatform' ) . '</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/about/' ) ) . '">' . esc_html__( 'About', 'eduplatform' ) . '</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/contact/' ) ) . '">' . esc_html__( 'Contact', 'eduplatform' ) . '</a></li>';
    echo '<li><a href="' . esc_url( $courses_url ) . '">' . esc_html__( 'Courses', 'eduplatform' ) . '</a></li>';
    echo '</ul>';
}

function eduplatform_enqueue_fonts() {
    wp_enqueue_style(
        'eduplatform-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
        array(),
        null
    );
}

add_action( 'wp_enqueue_scripts', 'eduplatform_enqueue_fonts', 5 );

/**
 * Parse the first image in course content (size + dimensions from the block editor).
 *
 * @return array{attachment_id: int, size: string, width: int, height: int, style: string}
 */
function eduplatform_parse_first_content_image( $post_id = 0 ) {
    $empty = array(
        'attachment_id' => 0,
        'size'          => 'full',
        'width'         => 0,
        'height'        => 0,
        'style'         => '',
    );

    $post_id = $post_id ? (int) $post_id : get_the_ID();
    if ( ! $post_id ) {
        return $empty;
    }

    $content = get_post_field( 'post_content', $post_id );
    if ( ! is_string( $content ) || '' === trim( $content ) ) {
        return $empty;
    }

    $figure_attrs = '';
    $img_attrs    = '';

    if ( preg_match( '/<figure[^>]*\bwp-block-image\b[^>]*>(.*?)<\/figure>/is', $content, $block ) ) {
        if ( preg_match( '/<figure([^>]*)>/i', $block[0], $fig ) ) {
            $figure_attrs = $fig[1];
        }
        if ( preg_match( '/<img([^>]+)>/i', $block[1], $img ) ) {
            $img_attrs = $img[1];
        }
    } elseif ( preg_match( '/<p[^>]*>\s*<img([^>]+)>\s*<\/p>/is', $content, $para ) ) {
        $img_attrs = $para[1];
    } elseif ( preg_match( '/<img([^>]+)>/i', $content, $img ) ) {
        $img_attrs = $img[1];
    }

    if ( '' === $img_attrs ) {
        return $empty;
    }

    $parsed = $empty;

    if ( preg_match( '/wp-image-(\d+)/i', $img_attrs, $id_match ) ) {
        $parsed['attachment_id'] = (int) $id_match[1];
    }

    $class_blob = $figure_attrs . ' ' . $img_attrs;
    if ( preg_match( '/\bsize-(thumbnail|medium|medium_large|large|full)\b/i', $class_blob, $size_match ) ) {
        $parsed['size'] = strtolower( $size_match[1] );
    }

    if ( preg_match( '/\bwidth=["\'](\d+)["\']/i', $img_attrs, $width_match ) ) {
        $parsed['width'] = (int) $width_match[1];
    }

    if ( preg_match( '/\bheight=["\'](\d+)["\']/i', $img_attrs, $height_match ) ) {
        $parsed['height'] = (int) $height_match[1];
    }

    if ( preg_match( '/style=["\']([^"\']+)["\']/i', $img_attrs, $style_match ) ) {
        $parsed['style'] = $style_match[1];
    } elseif ( '' !== $figure_attrs && preg_match( '/style=["\']([^"\']+)["\']/i', $figure_attrs, $style_match ) ) {
        $parsed['style'] = $style_match[1];
    }

    return $parsed;
}

/**
 * First image attachment ID from course post content (fallback when no featured image).
 */
function eduplatform_get_course_content_image_id( $post_id = 0 ) {
    $parsed = eduplatform_parse_first_content_image( $post_id );
    return $parsed['attachment_id'];
}

/**
 * Keep only safe CSS declarations from editor inline styles.
 */
function eduplatform_sanitize_inline_image_style( $style ) {
    $style = wp_strip_all_tags( (string) $style );
    $allowed = array();

    foreach ( explode( ';', $style ) as $rule ) {
        $rule = trim( $rule );
        if ( '' === $rule || false === strpos( $rule, ':' ) ) {
            continue;
        }

        list( $property, $value ) = array_map( 'trim', explode( ':', $rule, 2 ) );
        $property = strtolower( $property );

        if ( in_array( $property, array( 'width', 'height', 'max-width', 'min-width', 'max-height', 'min-height', 'aspect-ratio' ), true ) ) {
            $allowed[] = $property . ': ' . preg_replace( '/[^0-9a-z.%\-()\s]/i', '', $value );
        }
    }

    return implode( '; ', $allowed );
}

/**
 * Image attributes for the hero image (respect block editor width/height/style).
 */
function eduplatform_get_course_hero_image_attrs( $post_id = 0 ) {
    $post_id = $post_id ? (int) $post_id : get_the_ID();
    $parsed  = eduplatform_parse_first_content_image( $post_id );

    $attrs = array(
        'class'    => 'single-course-featured',
        'loading'  => 'eager',
        'decoding' => 'async',
    );

    $featured_id = (int) get_post_thumbnail_id( $post_id );
    $use_editor  = $parsed['attachment_id'] && ( ! $featured_id || $featured_id === $parsed['attachment_id'] );

    if ( ! $use_editor ) {
        return $attrs;
    }

    $style = eduplatform_sanitize_inline_image_style( $parsed['style'] );
    if ( '' !== $style ) {
        $attrs['style'] = $style;
        return $attrs;
    }

    if ( $parsed['width'] > 0 ) {
        $attrs['width'] = $parsed['width'];
    }

    if ( $parsed['height'] > 0 ) {
        $attrs['height'] = $parsed['height'];
    }

    return $attrs;
}

/**
 * Image size slug chosen in the editor for the first content image.
 */
function eduplatform_get_course_hero_image_size( $post_id = 0 ) {
    $post_id = $post_id ? (int) $post_id : get_the_ID();
    $parsed  = eduplatform_parse_first_content_image( $post_id );
    $featured_id = (int) get_post_thumbnail_id( $post_id );

    if ( $parsed['attachment_id'] && ( ! $featured_id || $featured_id === $parsed['attachment_id'] ) && $parsed['size'] ) {
        return $parsed['size'];
    }

    return 'full';
}

/**
 * Whether the course has a featured image or an image in the editor content.
 */
function eduplatform_course_has_display_image( $post_id = 0 ) {
    $post_id = $post_id ? (int) $post_id : get_the_ID();

    return has_post_thumbnail( $post_id ) || eduplatform_get_course_content_image_id( $post_id ) > 0;
}

/**
 * Featured image URL for archive cards (thumbnail or first content image).
 */
function eduplatform_get_course_image_url( $post_id = 0, $size = 'medium_large' ) {
    $post_id = $post_id ? (int) $post_id : get_the_ID();

    if ( has_post_thumbnail( $post_id ) ) {
        $url = get_the_post_thumbnail_url( $post_id, $size );
        return $url ? $url : '';
    }

    $attachment_id = eduplatform_get_course_content_image_id( $post_id );
    if ( $attachment_id ) {
        $url = wp_get_attachment_image_url( $attachment_id, $size );
        return $url ? $url : '';
    }

    return '';
}

/**
 * Output course hero image markup for the single template.
 */
function eduplatform_the_course_hero_image( $size = 'full' ) {
    $post_id       = get_the_ID();
    $size          = eduplatform_get_course_hero_image_size( $post_id );
    $attrs         = eduplatform_get_course_hero_image_attrs( $post_id );
    $attachment_id = has_post_thumbnail( $post_id ) ? get_post_thumbnail_id( $post_id ) : eduplatform_get_course_content_image_id( $post_id );

    if ( ! $attachment_id ) {
        return;
    }

    echo wp_get_attachment_image( (int) $attachment_id, $size, false, $attrs );
}

/**
 * Remove the first inline image from content when it is shown in the hero area.
 */
function eduplatform_strip_first_content_image( $content ) {
    if ( ! is_string( $content ) || '' === trim( $content ) ) {
        return $content;
    }

    $content = preg_replace( '/<figure[^>]*\bwp-block-image\b[^>]*>.*?<\/figure>/is', '', $content, 1 );

    if ( ! preg_match( '/<figure[^>]*\bwp-block-image\b[^>]*>/i', $content ) ) {
        $content = preg_replace( '/<p[^>]*>\s*<img[^>]+>\s*<\/p>/is', '', $content, 1 );
    }

    return $content;
}

function eduplatform_single_course_content( $content ) {
    if ( ! is_singular( 'course' ) || ! is_string( $content ) ) {
        return $content;
    }

    if ( ! eduplatform_course_has_display_image() ) {
        return $content;
    }

    return eduplatform_strip_first_content_image( $content );
}

add_filter( 'the_content', 'eduplatform_single_course_content', 12 );

/**
 * Stable hue (0–359) for category badge color from term slug.
 */
function eduplatform_term_badge_hue( $slug ) {
    $slug = (string) $slug;
    if ( '' === $slug ) {
        return 210;
    }
    return (int) ( abs( crc32( $slug ) ) % 360 );
}

/**
 * Filter main query: course search + optional category (?course_search= &course_cat=).
 */
function eduplatform_pre_get_course_archives( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    if ( ! $query->is_post_type_archive( 'course' ) && ! $query->is_tax( 'course_category' ) ) {
        return;
    }

    if ( $query->is_page( 'courses' ) ) {
        return;
    }

    if ( $query->is_post_type_archive( 'course' ) ) {
        $cat = eduplatform_get_course_category_filter();
        if ( '' !== $cat ) {
            $query->set(
                'tax_query',
                array(
                    array(
                        'taxonomy' => 'course_category',
                        'field'    => 'slug',
                        'terms'    => $cat,
                    ),
                )
            );
        }
    }

    $search = eduplatform_get_course_search_query();
    if ( '' !== $search ) {
        $query->set( 's', $search );
        $query->set( 'eduplatform_course_catalog', true );

        $home_course_ids = eduplatform_dashboard_home_course_ids();
        $query->set( 'post__in', ! empty( $home_course_ids ) ? $home_course_ids : array( 0 ) );
    }
}

add_action( 'pre_get_posts', 'eduplatform_pre_get_course_archives' );

/**
 * Course archive filter bar (categories + search GET form).
 *
 * @param string $action_url Form action URL (archive or current term).
 */
function eduplatform_course_explore_filters( $action_url ) {
    if ( '' === $action_url ) {
        $action_url = get_post_type_archive_link( 'course' );
    }
    if ( ! $action_url ) {
        return;
    }

    $terms = get_terms(
        array(
            'taxonomy'   => 'course_category',
            'hide_empty' => true,
        )
    );

    $current_cat = isset( $_GET['course_cat'] ) ? sanitize_title( wp_unslash( $_GET['course_cat'] ) ) : '';
    $search_q    = isset( $_GET['course_search'] ) ? sanitize_text_field( wp_unslash( $_GET['course_search'] ) ) : '';

    ?>
    <div class="course-explore-filters">
        <div class="course-explore-filters-inner">
            <div class="course-filter-label"><?php esc_html_e( 'Filter by:', 'eduplatform' ); ?></div>
            <nav class="course-filter-pills" aria-label="<?php esc_attr_e( 'Course categories', 'eduplatform' ); ?>">
                <?php
                $all_active = ( '' === $current_cat );
                $all_url    = remove_query_arg( array( 'course_cat', 'paged' ), $action_url );
                ?>
                <a class="course-filter-pill<?php echo $all_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $all_url ); ?>">
                    <?php esc_html_e( 'All', 'eduplatform' ); ?>
                </a>
                <?php if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) : ?>
                    <?php foreach ( $terms as $term ) : ?>
                        <?php
                        $term_url = add_query_arg(
                            array(
                                'course_cat' => $term->slug,
                            ),
                            remove_query_arg( 'paged', $action_url )
                        );
                        $is_active = ( $current_cat === $term->slug );
                        ?>
                        <a class="course-filter-pill<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $term_url ); ?>">
                            <?php echo esc_html( $term->name ); ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </nav>
            <form class="course-explore-search" method="get" action="<?php echo esc_url( $action_url ); ?>" role="search">
                <?php if ( '' !== $current_cat ) : ?>
                    <input type="hidden" name="course_cat" value="<?php echo esc_attr( $current_cat ); ?>">
                <?php endif; ?>
                <label class="screen-reader-text" for="course_search_input"><?php esc_html_e( 'Search courses', 'eduplatform' ); ?></label>
                <span class="course-search-icon" aria-hidden="true">⌕</span>
                <input
                    id="course_search_input"
                    type="search"
                    name="course_search"
                    value="<?php echo esc_attr( $search_q ); ?>"
                    placeholder="<?php esc_attr_e( 'Search courses…', 'eduplatform' ); ?>"
                    autocomplete="off"
                >
            </form>
        </div>
    </div>
    <?php
}

/**
 * Toolbar: section title + result count.
 *
 * @param string $title Result title.
 */
function eduplatform_course_explore_toolbar( $title ) {
    global $wp_query;
    $found = (int) $wp_query->found_posts;
    ?>
    <div class="course-explore-toolbar">
        <h2 class="course-explore-toolbar-title"><?php echo esc_html( $title ); ?></h2>
        <p class="course-explore-toolbar-count" aria-live="polite">
            <?php
            printf(
                /* translators: %d: number of courses */
                esc_html( _n( '%d course', '%d courses', $found, 'eduplatform' ) ),
                $found
            );
            ?>
        </p>
    </div>
    <?php
}

/**
 * Whether the current request is a courses listing or single course view.
 *
 * @return bool
 */
function eduplatform_dashboard_is_courses_route() {
    return is_post_type_archive( 'course' ) || is_singular( 'course' ) || is_tax( 'course_category' ) || is_page( 'courses' );
}

/**
 * URL for the courses listing. The course type has no archive, so this is the Courses page.
 *
 * @return string
 */
function eduplatform_courses_page_url() {
    $archive = get_post_type_archive_link( 'course' );
    if ( $archive ) {
        return $archive;
    }

    $page = get_page_by_path( 'courses' );
    if ( $page ) {
        return get_permalink( $page );
    }

    return home_url( '/courses/' );
}

/**
 * Current course search string from the topbar (?course_search=).
 *
 * @return string
 */
function eduplatform_get_course_search_query() {
    if ( ! isset( $_GET['course_search'] ) ) {
        return '';
    }

    return sanitize_text_field( wp_unslash( $_GET['course_search'] ) );
}

/**
 * Active course category slug from URL (?course_cat= or taxonomy archive).
 *
 * @return string
 */
function eduplatform_get_course_category_filter() {
    if ( is_tax( 'course_category' ) ) {
        $term = get_queried_object();
        if ( $term && ! is_wp_error( $term ) ) {
            return $term->slug;
        }
    }

    if ( isset( $_GET['course_cat'] ) ) {
        return sanitize_title( wp_unslash( $_GET['course_cat'] ) );
    }

    return '';
}

/**
 * Query arguments for listing courses (search + category from GET).
 *
 * @return array<string, mixed>
 */
function eduplatform_courses_query_args() {
    $args = array(
        'post_type'              => 'course',
        'post_status'            => 'publish',
        'posts_per_page'         => -1,
        'orderby'                => 'date',
        'order'                  => 'DESC',
        'update_post_meta_cache' => true,
        'update_post_term_cache' => true,
        'eduplatform_course_catalog' => true,
    );

    $search = eduplatform_get_course_search_query();
    if ( '' !== $search ) {
        $args['s'] = $search;

        $home_course_ids = eduplatform_dashboard_home_course_ids();
        $args['post__in'] = ! empty( $home_course_ids ) ? $home_course_ids : array( 0 );
    }

    $cat = eduplatform_get_course_category_filter();
    if ( '' !== $cat && ! is_tax( 'course_category' ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'course_category',
                'field'    => 'slug',
                'terms'    => $cat,
            ),
        );
    }

    return $args;
}

/**
 * Include instructor meta in course catalog keyword search.
 *
 * @param string    $search  Search SQL fragment.
 * @param WP_Query  $query   Query instance.
 * @return string
 */
function eduplatform_course_catalog_search_sql( $search, $query ) {
    if ( ! $query->get( 'eduplatform_course_catalog' ) || ! $query->get( 's' ) ) {
        return $search;
    }

    global $wpdb;

    $term = $query->get( 's' );
    if ( ! is_string( $term ) || '' === $term ) {
        return $search;
    }

    $like = '%' . $wpdb->esc_like( $term ) . '%';

    $search .= $wpdb->prepare(
        " OR ({$wpdb->posts}.ID IN (
            SELECT post_id FROM {$wpdb->postmeta}
            WHERE meta_key = '_course_instructor'
            AND meta_value LIKE %s
        ))",
        $like
    );

    return $search;
}

add_filter( 'posts_search', 'eduplatform_course_catalog_search_sql', 10, 2 );

/**
 * Sidebar navigation items with active state.
 *
 * @return array<int, array<string, mixed>>
 */
function eduplatform_dashboard_nav_items() {
    $courses_url = get_post_type_archive_link( 'course' );
    if ( ! $courses_url ) {
        $courses_url = home_url( '/courses/' );
    }

    $contact_url = home_url( '/contact/' );
    $about_url   = home_url( '/about/' );

    return array(
        array(
            'label'  => __( 'Home', 'eduplatform' ),
            'url'    => home_url( '/' ),
            'icon'   => 'home',
            'active' => is_front_page(),
        ),
        array(
            'label'  => __( 'Courses', 'eduplatform' ),
            'url'    => $courses_url,
            'icon'   => 'courses',
            'active' => eduplatform_dashboard_is_courses_route(),
        ),
    );
}

/**
 * Sidebar footer widget copy (from live catalog stats).
 *
 * @return array<string, string>
 */
function eduplatform_dashboard_sidebar_widget_data() {
    $stats       = eduplatform_dashboard_stats();
    $courses_url = get_post_type_archive_link( 'course' );
    if ( ! $courses_url ) {
        $courses_url = home_url( '/courses/' );
    }

    return array(
        'title'      => __( 'Keep learning', 'eduplatform' ),
        'note'       => sprintf(
            /* translators: 1: course count, 2: lesson count */
            __( '%1$d courses and %2$d   in your catalog.', 'eduplatform' ),
            (int) $stats['total_courses'],
            (int) $stats['total_lessons']
        ),
        'link_url'   => $courses_url,
        'link_label' => __( 'Browse courses', 'eduplatform' ),
    );
}

/**
 * Category filter chips for dashboard courses page.
 *
 * @param string $context_url Current archive or term URL (for form context).
 */
function eduplatform_dashboard_category_filters( $context_url = '' ) {
    $archive_url = $context_url ? $context_url : eduplatform_courses_page_url();
    $search_q    = eduplatform_get_course_search_query();
    $active_slug = eduplatform_get_course_category_filter();

    $terms = get_terms(
        array(
            'taxonomy'   => 'course_category',
            'hide_empty' => true,
            'number'     => 6,
            'orderby'    => 'count',
            'order'      => 'DESC',
        )
    );

    if ( is_wp_error( $terms ) ) {
        $terms = array();
    }
    ?>
    <div class="dashboard-courses-filters" role="navigation" aria-label="<?php esc_attr_e( 'Course categories', 'eduplatform' ); ?>">
        <div class="dashboard-filter-pills">
            <?php
            $all_url = remove_query_arg( array( 'course_cat', 'paged' ), $archive_url );
            if ( '' !== $search_q ) {
                $all_url = add_query_arg( 'course_search', $search_q, $all_url );
            }
            ?>
            <a
                href="<?php echo esc_url( $all_url ); ?>"
                class="dashboard-filter-pill<?php echo '' === $active_slug ? ' is-active' : ''; ?>"
            >
                <?php esc_html_e( 'All', 'eduplatform' ); ?>
            </a>

            <?php foreach ( $terms as $term ) : ?>
                <?php
                if ( is_tax( 'course_category' ) ) {
                    $term_link = get_term_link( $term );
                    if ( is_wp_error( $term_link ) ) {
                        continue;
                    }
                    if ( '' !== $search_q ) {
                        $term_link = add_query_arg( 'course_search', $search_q, $term_link );
                    }
                } else {
                    $term_link = add_query_arg(
                        'course_cat',
                        $term->slug,
                        remove_query_arg( array( 'paged', 'course_cat' ), $archive_url )
                    );
                    if ( '' !== $search_q ) {
                        $term_link = add_query_arg( 'course_search', $search_q, $term_link );
                    }
                }
                $is_active = $active_slug === $term->slug;
                ?>
                <a
                    href="<?php echo esc_url( $term_link ); ?>"
                    class="dashboard-filter-pill<?php echo $is_active ? ' is-active' : ''; ?>"
                >
                    <span class="dashboard-filter-pill-dot" style="--pill-hue: <?php echo esc_attr( (string) eduplatform_term_badge_hue( $term->slug ) ); ?>;" aria-hidden="true"></span>
                    <?php echo esc_html( $term->name ); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <a href="<?php echo esc_url( $archive_url ); ?>" class="dashboard-filter-see-all">
            <?php esc_html_e( 'See all', 'eduplatform' ); ?>
        </a>
    </div>
    <?php
}

/**
 * Account actions shown in the dashboard welcome dropdown.
 *
 * @return array<int, array{key: string, label: string, url: string}>
 */
function eduplatform_dashboard_user_menu_links() {
    $redirect = home_url( '/' );

    if ( is_user_logged_in() ) {
        return array(
            array(
                'key'   => 'password',
                'label' => __( 'Change password', 'eduplatform' ),
                'url'   => admin_url( 'profile.php' ),
            ),
            array(
                'key'   => 'logout',
                'label' => __( 'Log out', 'eduplatform' ),
                'url'   => wp_logout_url( $redirect ),
            ),
        );
    }

    return array(
        array(
            'key'   => 'login',
            'label' => __( 'Log in', 'eduplatform' ),
            'url'   => eduplatform_login_url(),
        ),
    );
}

/**
 * Display name for dashboard welcome line.
 *
 * @return string
 */
function eduplatform_dashboard_welcome_name() {
    if ( is_user_logged_in() ) {
        $user = wp_get_current_user();

        if ( $user->display_name ) {
            return $user->display_name;
        }
    }

    return get_bloginfo( 'name' );
}

/**
 * Avatar URL for dashboard header.
 *
 * @return string
 */
function eduplatform_dashboard_avatar_url() {
    if ( is_user_logged_in() ) {
        $url = get_avatar_url( get_current_user_id(), array( 'size' => 96 ) );
        if ( $url ) {
            return $url;
        }
    }

    $custom_logo_id = get_theme_mod( 'custom_logo' );
    if ( $custom_logo_id ) {
        $logo = wp_get_attachment_image_url( (int) $custom_logo_id, 'thumbnail' );
        if ( $logo ) {
            return $logo;
        }
    }

    return get_template_directory_uri() . '/assets/images/dashboard-avatar-default.svg';
}

/**
 * Courses shown in “My Courses” on home when the user is logged in.
 *
 * All published courses in the catalog are available to logged-in users.
 *
 * @param int $user_id User ID (0 = current).
 * @return WP_Post[]
 */
function eduplatform_get_my_courses_for_user( $user_id = 0 ) {
    $user_id = $user_id ? (int) $user_id : get_current_user_id();
    if ( $user_id < 1 ) {
        return array();
    }

    $course_ids = function_exists( 'edu_platform_get_user_enrolled_course_ids' )
        ? edu_platform_get_user_enrolled_course_ids( $user_id )
        : array();

    if ( empty( $course_ids ) ) {
        return array();
    }

    $query = new WP_Query(
        array(
            'post_type'              => 'course',
            'post_status'            => 'publish',
            'post__in'               => $course_ids,
            'posts_per_page'         => -1,
            'orderby'                => 'post__in',
            'no_found_rows'          => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
        )
    );

    return $query->posts;
}

/**
 * Fetch published courses for the home dashboard.
 *
 * @param int $limit Max courses.
 * @return WP_Post[]
 */

 function eduplatform_dashboard_get_courses( $limit = 4 ) {

    if ( ! is_user_logged_in() ) {
        return array();
    }

    $user_id = get_current_user_id();

    $course_ids = edu_platform_get_user_enrolled_course_ids( $user_id );

    if ( empty( $course_ids ) ) {
        return array();
    }

    $query = new WP_Query(
        array(
            'post_type'              => 'course',
            'post_status'            => 'publish',
            'post__in'               => $course_ids,
            'posts_per_page'         => max( 1, (int) $limit ),
            'orderby'                => 'post__in',
            'no_found_rows'          => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
        )
    );

    return $query->posts;
}

function eduplatform_dashboard_get_active_courses( $limit = -1 ) {

    $query = new WP_Query(
        array(
            'post_type'              => 'course',
            'post_status'            => 'publish',
            'posts_per_page'         => (int) $limit,
            'orderby'                => 'date',
            'order'                  => 'DESC',
            'no_found_rows'          => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
        )
    );

    return $query->posts;
}

/**
 * Course IDs shown on the home dashboard (My Courses + Active Courses use the same list).
 *
 * @param int $limit Same limit as {@see eduplatform_dashboard_get_courses()}.
 * @return int[]
 */
function eduplatform_dashboard_home_course_ids( $limit = 4 ) {
    $courses = eduplatform_dashboard_get_courses( $limit );

    if ( empty( $courses ) ) {
        return array();
    }

    return array_values(
        array_filter(
            array_map(
                static function ( $course ) {
                    return isset( $course->ID ) ? (int) $course->ID : 0;
                },
                $courses
            )
        )
    );
}

/**
 * Aggregate stats from published courses.
 *
 * @return array<string, mixed>
 */
function eduplatform_dashboard_stats() {
    $counts        = wp_count_posts( 'course' );
    $total_courses = isset( $counts->publish ) ? (int) $counts->publish : 0;

    global $wpdb;

    $total_lessons = (int) $wpdb->get_var(
        "SELECT COALESCE(SUM(CAST(pm.meta_value AS UNSIGNED)), 0)
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE p.post_type = 'course'
        AND p.post_status = 'publish'
        AND pm.meta_key = '_course_lesson_count'
        AND pm.meta_value REGEXP '^[0-9]+$'"
    );

    $rating_avg = (float) $wpdb->get_var(
        "SELECT AVG(CAST(pm.meta_value AS DECIMAL(4,2)))
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE p.post_type = 'course'
        AND p.post_status = 'publish'
        AND pm.meta_key = '_course_rating'
        AND pm.meta_value REGEXP '^[0-9]+(\\.[0-9]+)?$'"
    );

    $monthly      = eduplatform_dashboard_courses_by_month( (int) gmdate( 'Y' ) );
    $peak_month   = 0;
    $peak_count   = 0;
    $month_labels = array(
        1  => __( 'January', 'eduplatform' ),
        2  => __( 'February', 'eduplatform' ),
        3  => __( 'March', 'eduplatform' ),
        4  => __( 'April', 'eduplatform' ),
        5  => __( 'May', 'eduplatform' ),
        6  => __( 'June', 'eduplatform' ),
        7  => __( 'July', 'eduplatform' ),
        8  => __( 'August', 'eduplatform' ),
        9  => __( 'September', 'eduplatform' ),
        10 => __( 'October', 'eduplatform' ),
        11 => __( 'November', 'eduplatform' ),
        12 => __( 'December', 'eduplatform' ),
    );

    foreach ( $monthly as $month => $count ) {
        if ( $count > $peak_count ) {
            $peak_count = $count;
            $peak_month = (int) $month;
        }
    }

    return array(
        'total_courses'    => $total_courses,
        'total_lessons'    => $total_lessons,
        'rating_average'   => $rating_avg,
        'peak_month_label' => $peak_count > 0 && isset( $month_labels[ $peak_month ] ) ? $month_labels[ $peak_month ] : '',
        'peak_month_count' => $peak_count,
    );
}

/**
 * Published course counts by month for a calendar year.
 *
 * @param int|null $year Year (defaults to current).
 * @return array<int, int> Month 1–12 => count.
 */
function eduplatform_dashboard_courses_by_month( $year = null ) {
    $year   = $year ? (int) $year : (int) gmdate( 'Y' );
    $counts = array_fill( 1, 12, 0 );

    $query = new WP_Query(
        array(
            'post_type'              => 'course',
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'date_query'             => array(
                array(
                    'year' => $year,
                ),
            ),
        )
    );

    foreach ( $query->posts as $post_id ) {
        $month = (int) get_the_date( 'n', $post_id );
        if ( $month >= 1 && $month <= 12 ) {
            ++$counts[ $month ];
        }
    }

    return $counts;
}

/**
 * Build SVG point strings for the monthly chart.
 *
 * @param array<int, int> $monthly Month => count.
 * @return array{line: string, area: string}
 */
function eduplatform_dashboard_chart_points( $monthly ) {
    $max    = max( 1, max( $monthly ) );
    $width  = 400;
    $base_y = 150;
    $top_y  = 20;
    $line   = array();

    for ( $m = 1; $m <= 12; $m++ ) {
        $x      = ( ( $m - 1 ) / 11 ) * $width;
        $v      = isset( $monthly[ $m ] ) ? (int) $monthly[ $m ] : 0;
        $y      = $base_y - ( ( $v / $max ) * ( $base_y - $top_y ) );
        $line[] = round( $x, 1 ) . ',' . round( $y, 1 );
    }

    $area = $line;
    $area[] = $width . ',' . $base_y;
    $area[] = '0,' . $base_y;

    return array(
        'line' => implode( ' ', $line ),
        'area' => implode( ' ', $area ),
    );
}

/**
 * Countdown to end of month + courses published this month.
 *
 * @return array{days: int, hours: int, minutes: int, new_courses: int}
 */
function eduplatform_dashboard_month_end_countdown() {
    $now     = time();
    $end     = strtotime( 'last day of this month 23:59:59' );
    $diff    = max( 0, $end - $now );
    $days    = (int) floor( $diff / DAY_IN_SECONDS );
    $hours   = (int) floor( ( $diff % DAY_IN_SECONDS ) / HOUR_IN_SECONDS );
    $minutes = (int) floor( ( $diff % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS );

    $query = new WP_Query(
        array(
            'post_type'              => 'course',
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'date_query'             => array(
                array(
                    'year'  => (int) gmdate( 'Y' ),
                    'month' => (int) gmdate( 'n' ),
                ),
            ),
        )
    );

    return array(
        'days'        => $days,
        'hours'       => $hours,
        'minutes'     => $minutes,
        'new_courses' => count( $query->posts ),
    );
}

/**
 * Milestone badges from catalog stats (no fake user progress).
 *
 * @param array<string, mixed> $stats From eduplatform_dashboard_stats().
 * @return array{earned: int, total: int, items: array<int, array{label: string, icon: string, earned: bool}>}
 */
function eduplatform_dashboard_achievements( $stats ) {
    $total_courses = (int) $stats['total_courses'];
    $total_lessons = (int) $stats['total_lessons'];
    $rating_avg    = (float) $stats['rating_average'];

    $items = array(
        array(
            'label'  => __( 'First course live', 'eduplatform' ),
            'icon'   => '1',
            'earned' => $total_courses >= 1,
        ),
        array(
            'label'  => __( '3+ courses', 'eduplatform' ),
            'icon'   => '3',
            'earned' => $total_courses >= 3,
        ),
        array(
            'label'  => __( '10+ lessons total', 'eduplatform' ),
            'icon'   => '10',
            'earned' => $total_lessons >= 10,
        ),
        array(
            'label'  => __( '4.0+ avg. rating', 'eduplatform' ),
            'icon'   => '★',
            'earned' => $rating_avg >= 4.0,
        ),
    );

    $earned = 0;
    foreach ( $items as $item ) {
        if ( $item['earned'] ) {
            ++$earned;
        }
    }

    return array(
        'earned' => $earned,
        'total'  => count( $items ),
        'items'  => $items,
    );
}

/**
 * EduPlatform: Handle My Account profile updates.
 */
function eduplatform_handle_account_profile_update() {

    if ( ! is_user_logged_in() || ! isset( $_POST['eduplatform_update_profile'] ) ) {
        return;
    }

    if (
        ! isset( $_POST['eduplatform_profile_nonce'] ) ||
        ! wp_verify_nonce(
            sanitize_text_field( wp_unslash( $_POST['eduplatform_profile_nonce'] ) ),
            'eduplatform_update_profile'
        )
    ) {
        wp_die( 'Security verification failed.' );
    }

    $user_id = get_current_user_id();

    $first_name = isset( $_POST['first_name'] )
        ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) )
        : '';

    $last_name = isset( $_POST['last_name'] )
        ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) )
        : '';

    $email = isset( $_POST['account_email'] )
        ? sanitize_email( wp_unslash( $_POST['account_email'] ) )
        : '';

    if ( ! is_email( $email ) ) {
        wp_die( 'Please enter a valid email address.' );
    }

    $result = wp_update_user( array(
        'ID'         => $user_id,
        'first_name' => $first_name,
        'last_name'  => $last_name,
        'user_email' => $email,
    ) );

    if ( is_wp_error( $result ) ) {
        wp_die( esc_html( $result->get_error_message() ) );
    }

    $redirect_url = add_query_arg(
        'account_notice',
        'profile_updated',
        wp_get_referer() ?: home_url( '/my-account/' )
    );

    wp_safe_redirect( $redirect_url );
    exit;
}
add_action( 'template_redirect', 'eduplatform_handle_account_profile_update' );


/**
 * EduPlatform: Handle account password changes.
 */
function eduplatform_handle_account_password_change() {

    if ( ! is_user_logged_in() || ! isset( $_POST['eduplatform_change_password'] ) ) {
        return;
    }

    if (
        ! isset( $_POST['eduplatform_password_nonce'] ) ||
        ! wp_verify_nonce(
            sanitize_text_field( wp_unslash( $_POST['eduplatform_password_nonce'] ) ),
            'eduplatform_change_password'
        )
    ) {
        wp_die( 'Security verification failed.' );
    }

    $user = wp_get_current_user();

    $current_password = isset( $_POST['current_password'] )
        ? (string) wp_unslash( $_POST['current_password'] )
        : '';

    $new_password = isset( $_POST['new_password'] )
        ? (string) wp_unslash( $_POST['new_password'] )
        : '';

    $confirm_password = isset( $_POST['confirm_password'] )
        ? (string) wp_unslash( $_POST['confirm_password'] )
        : '';

    if ( ! wp_check_password( $current_password, $user->user_pass, $user->ID ) ) {
        wp_die( 'Your current password is incorrect.' );
    }

    if ( strlen( $new_password ) < 6 ) {
        wp_die( 'Your new password must contain at least 6 characters.' );
    }

    if ( $new_password !== $confirm_password ) {
        wp_die( 'The new passwords do not match.' );
    }

    if ( $current_password === $new_password ) {
        wp_die( 'Your new password must be different from your current password.' );
    }

    wp_set_password( $new_password, $user->ID );

    // Keep the current user logged in after changing the password.
    wp_set_auth_cookie( $user->ID, true );

    $redirect_url = add_query_arg(
        'account_notice',
        'password_updated',
        wp_get_referer() ?: home_url( '/my-account/' )
    );

    wp_safe_redirect( $redirect_url );
    exit;
}
add_action( 'template_redirect', 'eduplatform_handle_account_password_change' );

