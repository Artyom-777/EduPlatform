<?php get_header(); ?>

<main class="dashboard-content" id="main-content">

    <section class="hero-section">

        <div class="container hero-container">

            <div class="hero-content">

                <span class="hero-label">
                    Learn Without Limits
                </span>

                <h1>
                    Build Your Future With
                    <span>Online Learning</span>
                </h1>

                <p>
                    Discover professional courses, learn new skills
                    and take your career to the next level.
                </p>

                <div class="hero-actions">
                    <a href="<?php echo esc_url( home_url( '/courses/' ) ); ?>" class="btn-primary">
                                    Explore Courses
                    </a>

                    <a href="#" class="btn-secondary">
                        Learn More
                    </a>
                </div>

            </div>

            <div class="hero-image">
                <img
                    src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=900"
                    alt="Students learning together"
                >
            </div>

        </div>

    </section>

</main>

<?php get_footer(); ?>
