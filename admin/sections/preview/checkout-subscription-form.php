<?php defined( 'ABSPATH' ) || exit; ?>

<!-- Display a payment form -->
<form id="payment-form" class="desktop" method="POST">
     
    <?php 
     
    /*
     * Build the REST API endpoint URL with the selected form ID,
     * ensuring the ID is safely cast to an integer
    */
    $create_checkout_url = rest_url(
        'espad-stripe/v1/create-subscription/' . absint($selected_form_id)
    );    
       
    // Generate Token if not already exists
    if ( ! isset($_SESSION['espad_payment_token']) ) {

        $espad_token = bin2hex(random_bytes(16)); // 32-character hex string

        $_SESSION['espad_payment_token'] = $espad_token;    

    } else {
         
        $espad_token = sanitize_text_field( $_SESSION['espad_payment_token']);
        
    }    
     
    ?>      
    
    <input type="hidden" id="create-checkout-url" name="create-checkout-url" value="<?php echo esc_html( $create_checkout_url ); ?>" />
    <input type="hidden" id="espad_checkout_mode" name="espad_checkout_mode" value="<?php echo esc_html( $mode ); ?>" />
    <input type="hidden" id="espad-form-id" name="espad-form-id" value="<?php echo esc_html( $selected_form_id ); ?>" />
    <input type="hidden" id="current-url" name="current-url" value="<?php echo esc_html( ESPAD_CURRENT_URL ); ?>" />
    <input type="hidden" id="stripe-public-key" name="stripe-public-key" value="<?php echo esc_html( $stripe_public_key ); ?>" />
    <input type="hidden" id="currency" name="currency" value="<?php echo esc_html( $currency ); ?>" />
    <input type="hidden" id="color" name="color" value="<?php echo esc_html( $color ); ?>" />
    <input type="hidden" id="choosed_fields" name="choosed_fields" value="<?php echo esc_html( $choosed_fields ); ?>" /> 
    <input type="hidden" id="espad-payment-layout" name="espad-payment-layout" value="<?php echo esc_html( $payment_layout ); ?>" />
    <input type="hidden" id="espad_payment_token" name="espad_payment_token" value="<?php echo esc_html( $espad_token ); ?>" />
 
    <div id="payment-element-loading">Loading ...</div>
    <div id="payment-element" class="hidden"></div>
    <div id="payment-message" class="hidden"></div>  

    <?php

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
     
    <?php require_once ESPAD_PLUGIN_PATH . 'admin/sections/preview/element.php'; ?>
    
    <div class="panel panel-primary custom-fields">
        <div class="panel-body">

            <?php 

            require_once ESPAD_PLUGIN_PATH . 'admin/sections/preview/name-email.php';

            switch ( $choosed_fields ) {

                case 'name_email_address':
                    $required_fields_string = '';
                    require_once ESPAD_PLUGIN_PATH . 'admin/sections/preview/street-postal-code.php';
                    require_once ESPAD_PLUGIN_PATH . 'admin/sections/preview/city.php';
                    break;

                case 'name_email_address_telephone':
                    $required_fields_string = '';
                    require_once ESPAD_PLUGIN_PATH . 'admin/sections/preview/street-postal-code.php';
                    require_once ESPAD_PLUGIN_PATH . 'admin/sections/preview/city-telephone.php';
                    break;

                case 'name_email_address_required_fields':
                    $required_fields_string = 'required';
                    require_once ESPAD_PLUGIN_PATH . 'admin/sections/preview/street-postal-code.php';
                    require_once ESPAD_PLUGIN_PATH . 'admin/sections/preview/city.php';
                    break;

                case 'name_email_address_telephone_required_fields':
                    $required_fields_string = 'required';
                    require_once ESPAD_PLUGIN_PATH . 'admin/sections/preview/street-postal-code.php';
                    require_once ESPAD_PLUGIN_PATH . 'admin/sections/preview/city-telephone.php';
                    break;

            }

            ?>

        </div>
    </div>  
    
    <?php
    
        global $wpdb;

        $table_name = $wpdb->prefix . 'espad_forms';

        // Sanitize the form ID
        $form_id = absint($selected_form_id);

        // Get the field from DB
        $checkout_metadata = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT checkout_metadata_1 FROM {$table_name} WHERE id = %d",
                $form_id
            )
        ); 
    
        $subscription_data = json_decode($checkout_metadata, true);

        $subscription_amount   = $subscription_data['subscription_checkout_amount'] ?? 0; 
        $subscription_currency = strtoupper($subscription_data['subscription_checkout_currency'] ?? '');
    
    ?> 
      
    <button 
            id="submit" 
            style="background-color: <?php echo esc_html( $color ); ?>;" 
            onmouseover="this.style.backgroundColor='#424649'" 
            onmouseout="this.style.backgroundColor='<?php echo esc_html( $color ); ?>'"
            title="<?php echo esc_html(__( 'Protected payment powered by EcoSys365.com & Stripe', 'easy-stripe-payments' )); ?>">
        <div class="spinner hidden" id="spinner"></div>
        <span id="button-text">
            <?php
                echo wp_kses( 
                    espad_plugin_image('assets/images/schloss_icon.png', 'Safe', ['class' => 'info-icon', 'width' => 17]),
                    ESPAD_ALLOWED_IMG_TAGS
                );                       
             
            echo esc_html( $payment_button ); ?> <sup><?php echo esc_html( $subscription_amount ); ?> <?php echo esc_html( $subscription_currency ); ?></sup>
        </span>
    </button>    
       
    <div id="payment-message" class="hidden"></div>  
 
</form>
                
                