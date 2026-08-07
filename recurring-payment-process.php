<?php

defined('ABSPATH') || exit; // Direct access is prohibited!
   
/**
 *
 * recurring-payment-process.php
 *
 * Handles the return from Stripe Checkout.
 *
 * The Stripe Checkout Session is retrieved using the returned session ID.
 * The customer is redirected to the success page only after the payment
 * status has been verified through the Stripe API.
 *
 * Security:
 * - Sanitizes all incoming GET parameters.
 * - Validates the Stripe Checkout Session ID format.
 * - Verifies the payment status directly through the Stripe API.
 */
      
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Instead of nonce verification, we are checking the status of each payment because this is a redirect from Stripe 
if ( isset($_GET['espad_payment_token']) ) {
         
    $returned_token = sanitize_text_field( wp_unslash($_GET['espad_payment_token']));
     
    if ( isset($_GET['session_id']) && preg_match( '/^cs_(test|live)_[a-zA-Z0-9]+$/', sanitize_text_field( wp_unslash($_GET['session_id']) ) ) ) { 

        // Load the Stripe manager class if not already loaded
        class_exists( 'ESPAD\Stripe\StripeESPADManager' ) || espad_stripe_manager_init(); 

        $session_id = sanitize_text_field( wp_unslash( $_GET['session_id']) );
        // phpcs:enable WordPress.Security.NonceVerification.Recommended -- Instead of nonce verification, we are checking the status of each payment because this is a redirect from Stripe

        $session = \Stripe\Checkout\Session::retrieve($session_id);

    } else {

        echo esc_html(__( 'No session_id supported.', 'easy-stripe-payments' ));

        exit;

    }

    if ( $session ) {

        try {

            $payment_status = $session->payment_status; 

            if ( $payment_status !== 'succeeded' && $payment_status !== 'paid' ) {        

                echo esc_html( "<p>Payment is still pending. Status: {$payment_status}</p>" );

                return;

            } else {

                wp_redirect( home_url( '?espad_stripe_status=success' ) );

                exit;              

            }

        } catch (\Stripe\Exception\ApiErrorException $e) {

            echo esc_html( "<p>Stripe API Error: " . esc_html($e->getMessage()) . "</p>" );

        }

    } else {

        echo esc_html(__( 'No session found.', 'easy-stripe-payments' ));

        exit;

    }

}


