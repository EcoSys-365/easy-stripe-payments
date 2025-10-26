<?php

defined('ABSPATH') || exit; // Direct access is prohibited!
 
/**
 * recurring-payment-process.php
 *
 * Verify the payment token and handle the Stripe Checkout session result.
 *
 * This logic ensures that the redirect from Stripe is valid and initiated
 * from the same user session that created the payment.
 *
 * Steps:
 * 1. Check if an "espad_payment_token" is present in the URL.
 * 2. Compare it securely with the session-stored token using hash_equals().
 *    This prevents timing attacks and ensures the redirect wasn’t tampered with.
 * 3. If the tokens match, validate the Stripe session_id format and retrieve
 *    the Checkout Session from Stripe’s API.
 * 4. Check the payment status — only proceed if it’s "succeeded" or "paid".
 * 5. Redirect the user to the success page if payment is confirmed.
 * 6. Handle any API or validation errors gracefully and display proper messages.
 *
 * Security:
 * - Sanitizes all incoming GET parameters.
 * - Fails early if the session token doesn’t match (protection against CSRF).
 * - Prevents direct access or invalid session references.
 */
   
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Instead of nonce verification, we are using token validation because this is a redirect from Stripe 
if ( isset($_GET['espad_payment_token']) ) {
         
    $returned_token = sanitize_text_field( wp_unslash($_GET['espad_payment_token']));
    
    if ( isset($_SESSION['espad_payment_token']) && hash_equals($_SESSION['espad_payment_token'], $returned_token) ) { 
    
        if ( isset($_GET['session_id']) && preg_match( '/^cs_(test|live)_[a-zA-Z0-9]+$/', sanitize_text_field( wp_unslash($_GET['session_id']) ) ) ) { 
 
            // Load the Stripe manager class if not already loaded
            class_exists( 'ESPAD\Stripe\StripeESPADManager' ) || espad_stripe_manager_init(); 

            $session_id = sanitize_text_field( wp_unslash( $_GET['session_id']) );
            // phpcs:enable WordPress.Security.NonceVerification.Recommended -- Instead of nonce verification, we are using token validation because this is a redirect from Stripe

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
        
    } else {
        
        wp_die('Security check failed.');
        
    } 

}


