
<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Stripe webhook REST endpoint.
 */
function edu_platform_register_stripe_webhook_route() {

    register_rest_route(
        'edu-platform/v1',
        '/stripe-webhook',
        array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'edu_platform_handle_stripe_webhook',
            'permission_callback' => '__return_true',
        )
    );
}

add_action( 'rest_api_init', 'edu_platform_register_stripe_webhook_route' );


/**
 * Handle Stripe webhook events.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response
 */
function edu_platform_handle_stripe_webhook( WP_REST_Request $request ) {

    global $wpdb;

    if (
        ! defined( 'EDU_PLATFORM_STRIPE_WEBHOOK_SECRET' ) ||
        empty( EDU_PLATFORM_STRIPE_WEBHOOK_SECRET )
    ) {
        return new WP_REST_Response(
            array( 'message' => 'Webhook configuration is missing.' ),
            500
        );
    }

    $payload = $request->get_body();
    $signature = $request->get_header( 'stripe-signature' );

    // Verify Stripe signature.
    try {

        $event = \Stripe\Webhook::constructEvent(
            $payload,
            $signature,
            EDU_PLATFORM_STRIPE_WEBHOOK_SECRET
        );

    } catch ( \UnexpectedValueException $error ) {

        return new WP_REST_Response(
            array( 'message' => 'Invalid webhook payload.' ),
            400
        );

    } catch ( \Stripe\Exception\SignatureVerificationException $error ) {

        return new WP_REST_Response(
            array( 'message' => 'Invalid Stripe signature.' ),
            400
        );
    }

    // Handle only successful Checkout events.
    if (
        ! in_array(
            $event->type,
            array(
                'checkout.session.completed',
                'checkout.session.async_payment_succeeded',
            ),
            true
        )
    ) {
        return new WP_REST_Response(
            array( 'received' => true ),
            200
        );
    }

    $session = $event->data->object;

    // Do not enroll before payment is confirmed.
    if (
        'complete' !== $session->status ||
        'paid' !== $session->payment_status
    ) {
        return new WP_REST_Response(
            array( 'received' => true, 'payment' => 'not_paid' ),
            200
        );
    }

    $payment_id = isset( $session->metadata->payment_id )
        ? absint( $session->metadata->payment_id )
        : 0;

    $user_id = isset( $session->metadata->user_id )
        ? absint( $session->metadata->user_id )
        : 0;

    $course_id = isset( $session->metadata->course_id )
        ? absint( $session->metadata->course_id )
        : 0;

    if ( ! $payment_id || ! $user_id || ! $course_id ) {
        return new WP_REST_Response(
            array( 'message' => 'Payment metadata is invalid.' ),
            400
        );
    }

    $payments_table = edu_platform_payments_table();

    // Begin transaction to prevent duplicate fulfillment.
    $wpdb->query( 'START TRANSACTION' );

    try {

        $payment = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$payments_table} WHERE id = %d FOR UPDATE",
                $payment_id
            )
        );

        if ( ! $payment ) {
            throw new \RuntimeException( 'Payment record not found.' );
        }

        // Duplicate webhook: payment was already processed.
        if ( 'paid' === $payment->status ) {
            $wpdb->query( 'COMMIT' );

            return new WP_REST_Response(
                array( 'received' => true, 'already_processed' => true ),
                200
            );
        }

        // Verify the session and payment details against our database.
        if (
            (int) $payment->user_id !== $user_id ||
            (int) $payment->course_id !== $course_id ||
            $payment->stripe_session_id !== $session->id ||
            (int) $payment->amount !== (int) $session->amount_total ||
            strtolower( $payment->currency ) !== strtolower( $session->currency ) ||
            'usd' !== strtolower( $session->currency ) ||
            'payment' !== $session->mode
        ) {
            throw new \RuntimeException( 'Payment verification failed.' );
        }

        if (
            'course' !== get_post_type( $course_id ) ||
            'publish' !== get_post_status( $course_id )
        ) {
            throw new \RuntimeException( 'Course is not available.' );
        }

      // Enroll the user only after verified payment.
        if ( ! edu_platform_enroll_user( $user_id, $course_id ) ) {
            throw new \RuntimeException( 'Course enrollment failed.' );
        }

        $payment_intent_id = $session->payment_intent ?? null;

        if ( is_object( $payment_intent_id ) ) {
            $payment_intent_id = $payment_intent_id->id ?? null;
        }

        // Update EduPlatform payment.
        $updated = $wpdb->update(
            $payments_table,
            array(
                'status'                   => 'paid',
                'stripe_payment_intent_id' => $payment_intent_id,
                'paid_at'                  => current_time( 'mysql' ),
            ),
            array(
                'id' => $payment_id,
            ),
            array( '%s', '%s', '%s' ),
            array( '%d' )
        );

        if ( false === $updated ) {
            throw new \RuntimeException( 'Payment update failed.' );
        }

        // Update WooCommerce order.
        if ( ! function_exists( 'wc_get_order' ) ) {
            throw new \RuntimeException( 'WooCommerce is unavailable.' );
        }

        $order = wc_get_order( absint( $payment->woo_order_id ) );

        if ( ! $order ) {
            throw new \RuntimeException( 'WooCommerce order not found.' );
        }

        if (
            (int) $order->get_meta( '_edu_platform_payment_id' ) !== $payment_id
        ) {
            throw new \RuntimeException( 'WooCommerce order verification failed.' );
        }

        if ( ! $order->is_paid() ) {
            $order->payment_complete( $payment_intent_id );
        }

        $wpdb->query( 'COMMIT' );

        return new WP_REST_Response(
            array( 'received' => true, 'payment' => 'paid' ),
            200
        );

    } catch ( \Throwable $error ) {

        $wpdb->query( 'ROLLBACK' );

        error_log(
            'EduPlatform webhook error: ' . $error->getMessage()
        );

        return new WP_REST_Response(
            array( 'message' => 'Webhook processing failed.' ),
            500
        );
    }
}
