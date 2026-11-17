<?php
get_header();
?>

<main class="dashboard-content error-page" id="main-content">

    <h1>404 - Page Not Found</h1>

    <p>Sorry, the page you are looking for does not exist.</p>

    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
        Go Back Home
    </a>

</main>

<?php
get_footer();
