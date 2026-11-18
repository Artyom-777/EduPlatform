<?php
/**
 * Template Name: My Account
 */

if ( ! is_user_logged_in() ) {
    wp_safe_redirect( wp_login_url( get_permalink() ) );
    exit;
}

$current_user = wp_get_current_user();

?>

<?php get_header(); ?>

<main class="my-account-page">
    <div class="my-account-container">

        <header class="my-account-header">
            <div class="my-account-header-copy">
                <h1>My Account</h1>
                <p>Manage your personal information and account security.</p>
            </div>

            <a class="my-account-home-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <span class="my-account-home-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" focusable="false">
                        <path d="M3 11.5 12 4l9 7.5"></path>
                        <path d="M5.5 10.5V20h13v-9.5"></path>
                        <path d="M9.5 20v-6h5v6"></path>
                    </svg>
                </span>
                <?php esc_html_e( 'Back to Home', 'eduplatform' ); ?>
            </a>
        </header>


        <?php
        $account_notice = isset( $_GET['account_notice'] )
            ? sanitize_key( wp_unslash( $_GET['account_notice'] ) )
            : '';

        if ( 'profile_updated' === $account_notice ) :
        ?>
            <div class="account-notice account-notice-success" role="status">
                Your profile has been successfully updated.
            </div>
        <?php elseif ( 'password_updated' === $account_notice ) : ?>
            <div class="account-notice account-notice-success" role="status">
                Your password has been successfully changed.
            </div>
        <?php endif; ?>


        <!-- Profile Information -->
        <section class="account-section">
            <div class="account-section-header">
                <h2>Profile Information</h2>
                <p>Update your personal information.</p>
            </div>

            <form method="post" class="account-profile-form">

                <?php wp_nonce_field( 'eduplatform_update_profile', 'eduplatform_profile_nonce' ); ?>

                <div class="account-form-grid">

                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            value="<?php echo esc_attr( $current_user->first_name ); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            value="<?php echo esc_attr( $current_user->last_name ); ?>"
                        >
                    </div>

                    <div class="form-group full-width">
                        <label for="account_email">Email Address</label>
                        <input
                            type="email"
                            id="account_email"
                            name="account_email"
                            value="<?php echo esc_attr( $current_user->user_email ); ?>"
                            required
                        >
                    </div>

                </div>

                <button type="submit" name="eduplatform_update_profile">
                    Save Changes
                </button>

            </form>
        </section>

        <!-- Change Password -->
        <section class="account-section">
            <div class="account-section-header">
                <h2>Change Password</h2>
                <p>Update your account password.</p>
            </div>

            <form method="post" class="account-password-form">

                <?php wp_nonce_field( 'eduplatform_change_password', 'eduplatform_password_nonce' ); ?>

                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit" name="eduplatform_change_password">
                    Update Password
                </button>

            </form>
        </section>

    </div>
</main>

<?php get_footer(); ?>
