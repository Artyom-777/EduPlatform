<?php
/**
 * Login page template (slug: login).
 *
 * @package eduplatform-theme
 */

get_header( 'auth' );

$redirect_to = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : home_url( '/' );
$error       = isset( $_GET['login_error'] ) ? sanitize_key( wp_unslash( $_GET['login_error'] ) ) : '';
?>
<main class="auth-card-wrap" id="main-content">
    <div class="auth-card">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="auth-brand">EduPlatform</a>

        <h1 class="auth-title"><?php esc_html_e( 'Log in', 'eduplatform' ); ?></h1>
        <p class="auth-sub"><?php esc_html_e( 'Sign in to access your dashboard and courses.', 'eduplatform' ); ?></p>

        <?php if ( 'invalid' === $error ) : ?>
            <p class="auth-error" role="alert"><?php esc_html_e( 'Invalid username or password.', 'eduplatform' ); ?></p>
        <?php elseif ( 'empty' === $error ) : ?>
            <p class="auth-error" role="alert"><?php esc_html_e( 'Please enter username and password.', 'eduplatform' ); ?></p>
        <?php elseif ( 'security' === $error ) : ?>
            <p class="auth-error" role="alert"><?php esc_html_e( 'Security check failed. Try again.', 'eduplatform' ); ?></p>
        <?php endif; ?>

        <form class="auth-form" method="post" action="">
            <?php wp_nonce_field( 'eduplatform_login', 'eduplatform_login_nonce' ); ?>
            <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">

            <p class="auth-field">
                <label for="eduplatform_user_login"><?php esc_html_e( 'Username or email', 'eduplatform' ); ?></label>
                <input
                    type="text"
                    name="log"
                    id="eduplatform_user_login"
                    class="auth-input"
                    value=""
                    autocomplete="username"
                    required
                >
            </p>

            <p class="auth-field">
                <label for="eduplatform_user_pass"><?php esc_html_e( 'Password', 'eduplatform' ); ?></label>
                <input
                    type="password"
                    name="pwd"
                    id="eduplatform_user_pass"
                    class="auth-input"
                    autocomplete="current-password"
                    required
                >
            </p>

            <p class="auth-field auth-field--remember">
                <label>
                    <input type="checkbox" name="rememberme" value="forever">
                    <?php esc_html_e( 'Remember me', 'eduplatform' ); ?>
                </label>
            </p>

            <button type="submit" name="eduplatform_login_submit" class="auth-submit">
                <?php esc_html_e( 'Log in', 'eduplatform' ); ?>
            </button>
        </form>

        <p class="auth-switch">
            <?php esc_html_e( 'No account?', 'eduplatform' ); ?>
            <a href="<?php echo esc_url( eduplatform_register_url() ); ?>"><?php esc_html_e( 'Register', 'eduplatform' ); ?></a>
        </p>

    </div>
</main>

<?php
get_footer( 'auth' );
