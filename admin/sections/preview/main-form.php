<?php 

defined( 'ABSPATH' ) || exit; 
 
$enqueue_inline_css = function() use ( $color, $amount_type ) { 
       
    // Dynamic colour with fallback
    $color        = esc_html( $color ?? '#0D8889' );
    $amount_type  = $amount_type ?? 'fix_amount';
      
    // Dynamic CSS
    $custom_css = "
    #espad_page .espad-advanced-checkout-switch__radio:checked + .btn,
    #espad_page :not(.espad-advanced-checkout-switch__radio) + .btn:active,
    #espad_page #payment-form .espad-advanced-checkout-switch label.btn:hover,
    #espad_page .btn-check:checked + .btn,
    #espad_page :not(.btn-check) + .btn:active,
    #espad_page .btn:first-child:active,
    #espad_page .btn.active,
    #espad_page .btn.show,
    #espad_page #prices_box label.btn:hover,
    #espad_page .progress-bar-fill {
        background-color: {$color} !important;
        color: #fff !important;
    }
    #espad_page .btn-outline-primary,
    #espad_page .progress-label strong {
        color: {$color} !important;
    }
    #espad_page input.form-control:focus {
        border: 2px solid {$color} !important;
        outline: none !important;
        box-shadow: none !important;
    }
    #espad_page #amountInput:focus {
        outline: 2px solid {$color} !important;
        outline-offset: 0;
        box-shadow: none !important;
    }
    ";

    /* Amount-type rules */
    if ( $amount_type != 'fix_amount' ) {

        if ( isset( $amount_type ) && $amount_type === 'variable_amount' ) {
            $custom_css .= "
                input.btn-check,
                label.btn-outline-primary {
                    display: none !important;
                }

                #amountInput {
                    padding: 13px;
                    border-left: 1px solid #ccc;
                }
            ";
        } else if ( isset( $amount_type ) && $amount_type === 'select_amount' ) {
            $custom_css .= "
                #amountInput {
                    display: none;
                }
            ";
        }

    }
    
    if ( ! empty( $custom_css ) ) {
        if ( is_admin() ) {
            echo '<style id="espd-dynamic-checkout-css">' . wp_strip_all_tags( $custom_css ) . '</style>';
        } else {
            wp_add_inline_style( 'espd-checkout-css', $custom_css );
        }
    }    
 
}; 
      
if ( is_admin() ) {
    // Admin Dashboard
    $enqueue_inline_css();
} else {
    // Frontend
    add_action( 'wp_enqueue_scripts', $enqueue_inline_css );
}
     
// Check if the form has been submitted
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Instead of nonce verification, we are checking the status of each payment because this is a redirect from Stripe 
if ( isset($_GET['espad_payment_token']) ) {
        
    $returned_token = sanitize_text_field( wp_unslash($_GET['espad_payment_token']));
    
    // Load the Stripe manager class if not already loaded
    class_exists( 'ESPAD\Stripe\StripeESPADManager' ) || espad_stripe_manager_init();     
        
    // Check if the redirect_status is set and equals "succeeded"
    if ( isset($_GET['payment_intent']) && ($payment_intent = sanitize_text_field( wp_unslash($_GET['payment_intent']) ) )  ) {

        // Instead of Session Token verification we check the payment intent status of each payment
        try {

            $payment_intent_id = \Stripe\PaymentIntent::retrieve($payment_intent);

            if ( $payment_intent_id->status === 'succeeded' && $payment_intent_id->amount > 0 ) {

            } else {

                wp_die('Your payment could not be completed. Please try again.');

            }

        } catch (Exception $e) {
            wp_die('Stripe error: ' . esc_html($e->getMessage()));
        }            

        if ( isset($_GET['redirect_status']) && sanitize_text_field( wp_unslash($_GET['redirect_status']) ) === 'succeeded' ) { 

            require_once ESPAD_PLUGIN_PATH . 'payment-process.php';

        } else if ( isset($_GET['redirect_status']) && sanitize_text_field( wp_unslash($_GET['redirect_status']) ) === 'failed' ) {

            require_once ESPAD_PLUGIN_PATH . 'frontend/sections/payment-failed.php';

            // Redirect to cancel_url
            espad_redirect_to_url($cancel_url);  

        }

    }        
     
}
// phpcs:enable WordPress.Security.NonceVerification.Recommended -- Instead of nonce verification, we are using token validation because this is a redirect from Stripe
 
if ( $mode === "Subscription" ) {
    
    require_once ESPAD_PLUGIN_PATH . 'admin/sections/preview/checkout-subscription-form.php';
    
} else if ( $mode === "Advanced" ) {

    require_once ESPAD_PLUGIN_PATH . 'admin/sections/preview/checkout-advanced-form.php';
    
} else {

    require_once ESPAD_PLUGIN_PATH . 'admin/sections/preview/checkout-form.php';
    
}


