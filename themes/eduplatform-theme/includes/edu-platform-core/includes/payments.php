<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Stripe Checkout REST API endpoint.
 */
function edu_platform_register_payment_routes() {

    register_rest_route(
        'edu-platform/v1',
        '/checkout-session',
        array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'edu_platform_create_checkout_session',
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        )
    );
}

add_action( 'rest_api_init', 'edu_platform_register_payment_routes' );


/**
 * Create Stripe Embedded Checkout Session.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response
 */
function edu_platform_create_checkout_session( WP_REST_Request $request ) {

    global $wpdb;

    // Check Stripe configuration.
    if (
        ! defined( 'EDU_PLATFORM_STRIPE_SECRET_KEY' ) ||
        empty( EDU_PLATFORM_STRIPE_SECRET_KEY )
    ) {
        return new WP_REST_Response(
            array( 'message' => 'Payment configuration is missing.' ),
            500
        );
    }

    // Get and validate course ID.
    $course_id = absint( $request->get_param( 'course_id' ) );

    if (
        ! $course_id ||
        'course' !== get_post_type( $course_id ) ||
        'publish' !== get_post_status( $course_id )
    ) {
        return new WP_REST_Response(
            array( 'message' => 'Invalid course.' ),
            400
        );
    }

    $user_id = get_current_user_id();

    // Prevent duplicate enrollment.
    if ( edu_platform_user_is_enrolled( $user_id, $course_id ) ) {
        return new WP_REST_Response(
            array( 'message' => 'You are already enrolled in this course.' ),
            409
        );
    }

    // Read price from WordPress database, never from the browser.
    $price = get_post_meta( $course_id, '_course_price', true );

    if ( ! is_numeric( $price ) || (float) $price <= 0 ) {
        return new WP_REST_Response(
            array( 'message' => 'Course price is invalid.' ),
            400
        );
    }

    $amount = (int) round( (float) $price * 100 );

    if ( $amount < 1 ) {
        return new WP_REST_Response(
            array( 'message' => 'Course price is invalid.' ),
            400
        );
    }

    $payments_table = edu_platform_payments_table();

    // Create a pending payment record first.
    $inserted = $wpdb->insert(
        $payments_table,
        array(
            'user_id'    => $user_id,
            'course_id'  => $course_id,
            'amount'     => $amount,
            'currency'   => 'usd',
            'status'     => 'pending',
            'created_at' => current_time( 'mysql' ),
        ),
        array( '%d', '%d', '%d', '%s', '%s', '%s' )
    );

    if ( false === $inserted ) {
        return new WP_REST_Response(
            array( 'message' => 'Could not create payment record.' ),
            500
        );
    }

    $payment_id = (int) $wpdb->insert_id;

    $woo_order = null;

   try {

    // Get user and course information.
    $user = get_userdata( $user_id );

    $course_name = get_the_title( $course_id );

    $metadata = array(
        'payment_id' => (string) $payment_id,
        'user_id'    => (string) $user_id,
        'course_id'  => (string) $course_id,
    );

    $stripe = new \Stripe\StripeClient(
        EDU_PLATFORM_STRIPE_SECRET_KEY
    );
        $session = $stripe->checkout->sessions->create(
            array(
                'ui_mode' => 'embedded_page',
                'mode'    => 'payment',

                'customer_email' => $user->user_email,

                'client_reference_id' => (string) $payment_id,

                'line_items' => array(
                    array(
                        'price_data' => array(
                            'currency' => 'usd',
                            'product_data' => array(
                                'name' => $course_name,
                            ),
                            'unit_amount' => $amount,
                        ),
                        'quantity' => 1,
                    ),
                ),

                'metadata' => $metadata,

                'payment_intent_data' => array(
                    'metadata' => $metadata,
                ),

                'return_url' => add_query_arg(
                    array(
                        'payment_return' => '1',
                        'session_id' => '{CHECKOUT_SESSION_ID}',
                    ),
                    get_permalink( $course_id )
                ),
            ),
            array(
                'idempotency_key' => 'edu-platform-payment-' . $payment_id,
            )
        );

        if ( empty( $session->client_secret ) ) {
            throw new \RuntimeException(
                'Stripe did not return a client secret.'
            );
        }

        // Save Stripe Session ID.
        $updated = $wpdb->update(
            $payments_table,
            array(
                'stripe_session_id' => $session->id,
            ),
            array(
                'id' => $payment_id,
            ),
            array( '%s' ),
            array( '%d' )
        );

        if ( false === $updated ) {
            throw new \RuntimeException(
                'Could not save Stripe Session ID.'
            );
        }

        return new WP_REST_Response(
            array(
                'client_secret' => $session->client_secret,
            ),
            200
        );

    } catch ( \Throwable $error ) {

        // Do not expose internal Stripe errors to the browser.
        $wpdb->update(
            $payments_table,
            array( 'status' => 'failed' ),
            array( 'id' => $payment_id ),
            array( '%s' ),
            array( '%d' )
        );

        error_log(
            'EduPlatform Stripe error: ' . $error->getMessage()
        );

        return new WP_REST_Response(
            array( 'message' => 'Unable to initialize payment. Please try again.' ),
            500
        );
    }
}
