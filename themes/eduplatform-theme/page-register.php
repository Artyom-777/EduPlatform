<?php
/**
 * Register page template (slug: register).
 *
 * @package eduplatform-theme
 */

get_header( 'auth' );

$error = isset( $_GET['register_error'] ) ? sanitize_key( wp_unslash( $_GET['register_error'] ) ) : '';
?>
<main class="auth-card-wrap" id="main-content">
    <div class="auth-card">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="auth-brand">EduPlatform</a>

        <h1 class="auth-title"><?php esc_html_e( 'Create account', 'eduplatform' ); ?></h1>
        <p class="auth-sub"><?php esc_html_e( 'Register to enroll in courses and use the dashboard.', 'eduplatform' ); ?></p>

        <?php if ( 'disabled' === $error ) : ?>
            <p class="auth-error" role="alert"><?php esc_html_e( 'Registration is currently disabled.', 'eduplatform' ); ?></p>
        <?php elseif ( 'empty' === $error ) : ?>
            <p class="auth-error" role="alert"><?php esc_html_e( 'Please fill in all fields.', 'eduplatform' ); ?></p>
        <?php elseif ( 'email' === $error ) : ?>
            <p class="auth-error" role="alert"><?php esc_html_e( 'Please enter a valid email address.', 'eduplatform' ); ?></p>
        <?php elseif ( 'username' === $error ) : ?>
            <p class="auth-error" role="alert"><?php esc_html_e( 'That username is already taken.', 'eduplatform' ); ?></p>
        <?php elseif ( 'email_exists' === $error ) : ?>
            <p class="auth-error" role="alert"><?php esc_html_e( 'That email is already registered.', 'eduplatform' ); ?></p>
        <?php elseif ( 'weak' === $error ) : ?>
            <p class="auth-error" role="alert"><?php esc_html_e( 'Password must be at least 8 characters.', 'eduplatform' ); ?></p>
        <?php elseif ( 'security' === $error ) : ?>
            <p class="auth-error" role="alert"><?php esc_html_e( 'Security check failed. Try again.', 'eduplatform' ); ?></p>
        <?php elseif ( 'failed' === $error ) : ?>
            <p class="auth-error" role="alert"><?php esc_html_e( 'Could not create account. Try again.', 'eduplatform' ); ?></p>
        <?php endif; ?>

        <form class="auth-form" method="post" action="">
            <?php wp_nonce_field( 'eduplatform_register', 'eduplatform_register_nonce' ); ?>

            <p class="auth-field">
                <label for="eduplatform_reg_username"><?php esc_html_e( 'Username', 'eduplatform' ); ?></label>
                <input
                    type="text"
                    name="user_login"
                    id="eduplatform_reg_username"
                    class="auth-input"
                    autocomplete="username"
                    required
                >
            </p>

            <p class="auth-field">
                <label for="eduplatform_reg_email"><?php esc_html_e( 'Email', 'eduplatform' ); ?></label>
                <input
                    type="email"
                    name="user_email"
                    id="eduplatform_reg_email"
                    class="auth-input"
                    autocomplete="email"
                    required
                >
            </p>

            <p class="auth-field">
                <label for="eduplatform_reg_pass"><?php esc_html_e( 'Password', 'eduplatform' ); ?></label>
                <input
                    type="password"
                    name="user_pass"
                    id="eduplatform_reg_pass"
                    class="auth-input"
                    autocomplete="new-password"
                    minlength="6"
                    required
                >
            </p>

            <button type="submit" name="eduplatform_register_submit" class="auth-submit">
                <?php esc_html_e( 'Register', 'eduplatform' ); ?>
            </button>
        </form>

        <p class="auth-switch">
            <?php esc_html_e( 'Already have an account?', 'eduplatform' ); ?>
            <a href="<?php echo esc_url( eduplatform_login_url() ); ?>"><?php esc_html_e( 'Log in', 'eduplatform' ); ?></a>
        </p>
    </div>
</main>

<?php
get_footer( 'auth' );
