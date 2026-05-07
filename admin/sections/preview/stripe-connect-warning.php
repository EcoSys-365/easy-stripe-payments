<?php 

defined( 'ABSPATH' ) || exit; 

// Check if Stripe Connect is active
$espad_stripe_access_token = \get_option( 'espad_stripe_connect_access_token', '' );

if ( empty( $espad_stripe_access_token ) ) {

    echo '<div class="espad_warning_box">';

        echo esc_html__( 
            'Please go to Settings and click "Connect with Stripe". Alternatively, you can continue using the "Standard Stripe Checkout" with your API keys.', 
            'easy-stripe-payments' 
        );

    echo '</div>';

}  

?>