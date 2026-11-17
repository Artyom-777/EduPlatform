<?php
/**
 * Dashboard: Insights.
 *
 * @package eduplatform-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$stats        = eduplatform_dashboard_stats();
$monthly      = eduplatform_dashboard_courses_by_month();
$chart_points = eduplatform_dashboard_chart_points( $monthly );
$achievements = eduplatform_dashboard_achievements( $stats );
?>

<section class="dashboard-insights">

    <div class="dashboard-insights-head">
        <h2><?php esc_html_e( 'Learning Insights', 'eduplatform' ); ?></h2>
        <p><?php esc_html_e( 'Overview of your learning platform.', 'eduplatform' ); ?></p>
    </div>

    <div class="dashboard-stats-grid">

        <article class="dashboard-stat-card">
            <span><?php esc_html_e( 'Total Courses', 'eduplatform' ); ?></span>
            <strong><?php echo esc_html( (string) $stats['total_courses'] ); ?></strong>
        </article>

        <article class="dashboard-stat-card">
            <span><?php esc_html_e( 'Total Lessons', 'eduplatform' ); ?></span>
            <strong><?php echo esc_html( (string) $stats['total_lessons'] ); ?></strong>
        </article>

        <article class="dashboard-stat-card">
            <span><?php esc_html_e( 'Average Rating', 'eduplatform' ); ?></span>
            <strong>
                <?php echo esc_html( number_format_i18n( (float) $stats['rating_average'], 1 ) ); ?>
                / 5
            </strong>
        </article>

    </div>

    <div class="dashboard-chart-card">

        <h3><?php esc_html_e( 'Courses Published This Year', 'eduplatform' ); ?></h3>

        <svg
            class="dashboard-chart"
            viewBox="0 0 400 170"
            preserveAspectRatio="none"
            role="img"
            aria-label="<?php esc_attr_e( 'Monthly course publishing chart', 'eduplatform' ); ?>"
        >
            <polyline
                points="<?php echo esc_attr( $chart_points['area'] ); ?>"
                fill="rgba(111, 76, 255, 0.12)"
                stroke="none"
            />

            <polyline
                points="<?php echo esc_attr( $chart_points['line'] ); ?>"
                fill="none"
                stroke="currentColor"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round"
            />
        </svg>

        <p>
            <?php
            if ( ! empty( $stats['peak_month_label'] ) ) {
                printf(
                    esc_html__( 'Most active month: %1$s (%2$d courses)', 'eduplatform' ),
                    esc_html( $stats['peak_month_label'] ),
                    (int) $stats['peak_month_count']
                );
            } else {
                esc_html_e( 'No courses have been published this year yet.', 'eduplatform' );
            }
            ?>
        </p>

    </div>

    <div class="dashboard-achievements">

        <h3><?php esc_html_e( 'Platform Milestones', 'eduplatform' ); ?></h3>

        <p>
            <?php
            printf(
                esc_html__( '%1$d of %2$d milestones achieved', 'eduplatform' ),
                (int) $achievements['earned'],
                (int) $achievements['total']
            );
            ?>
        </p>

        <div class="dashboard-achievements-list">

            <?php foreach ( $achievements['items'] as $item ) : ?>

                <div class="dashboard-achievement<?php echo $item['earned'] ? ' is-earned' : ''; ?>">

                    <span class="dashboard-achievement-icon">
                        <?php echo esc_html( $item['icon'] ); ?>
                    </span>

                    <span class="dashboard-achievement-label">
                        <?php echo esc_html( $item['label'] ); ?>
                    </span>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>