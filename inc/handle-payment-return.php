<?php

defined( 'ABSPATH' ) || exit;

// Check if the form has been submitted
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Instead of nonce verification, we are checking the status of each payment because this is a redirect from Stripe
if ( isset($_GET['espad_payment_token']) ) {
        
    $returned_token = sanitize_text_field( wp_unslash($_GET['espad_payment_token']));
    
    // Load the Stripe manager class if not already loaded
    class_exists( 'ESPAD\Stripe\StripeESPADManager' ) || espad_stripe_manager_init();     
        
    // Check if the redirect_status is set and equals "succeeded"
    if ( isset($_GET['payment_intent']) && ($payment_intent = sanitize_text_field( wp_unslash($_GET['payment_intent']) ) )  ) {
 
        // Verify the payment directly through the Stripe PaymentIntent status.
        try {

            $payment_intent_id = \Stripe\PaymentIntent::retrieve($payment_intent);
            
            if ( $payment_intent_id->status !== 'succeeded' || $payment_intent_id->amount <= 0 ) {
                
                wp_die('Your payment could not be completed. Please try again.');
                
            }            

        } catch (Exception $e) {
            wp_die('Stripe error: ' . esc_html($e->getMessage()));
        }  
        
        $redirect_status = isset( $_GET['redirect_status'] )
            ? sanitize_text_field(
                wp_unslash( $_GET['redirect_status'] )
            )
            : '';        

        if ( $redirect_status === 'succeeded' ) { 

            require_once ESPAD_PLUGIN_PATH . 'payment-process.php';

        } elseif ( $redirect_status === 'failed' ) {

            require_once ESPAD_PLUGIN_PATH . 'frontend/sections/payment-failed.php';

            // Redirect to cancel_url
            if ( isset( $cancel_url ) && ! empty( $cancel_url ) ) {
                espad_redirect_to_url( $cancel_url );
            }            

        }

    }        
     
}
// phpcs:enable WordPress.Security.NonceVerification.Recommended -- Instead of nonce verification, we are checking the status of each payment because this is a redirect from Stripe