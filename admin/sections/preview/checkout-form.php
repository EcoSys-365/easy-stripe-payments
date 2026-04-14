<?php defined( 'ABSPATH' ) || exit; ?>

<!-- Display a payment form -->
<form id="payment-form" class="desktop" method="POST">
    
    <?php 
    
    $create_checkout_url = ESPAD_SITE_URL . '/wp-json/espad-stripe/v1/create'; 
     
    // Generate Token if not already exists
    if ( ! isset($_SESSION['espad_payment_token']) ) {

        $espad_token = bin2hex(random_bytes(16)); // 32-character hex string

        $_SESSION['espad_payment_token'] = $espad_token;    

    } else {
         
        $espad_token = sanitize_text_field( $_SESSION['espad_payment_token']);
        
    }    
     
    ?>
    
    <input type="hidden" id="create-checkout-url" name="create-checkout-url" value="<?php echo esc_html( $create_checkout_url ); ?>" />
    <input type="hidden" id="home-url" name="home-url" value="<?php echo esc_html( ESPAD_SITE_URL ); ?>" />
    <input type="hidden" id="current-url" name="current-url" value="<?php echo esc_html( ESPAD_CURRENT_URL ); ?>" />
    <input type="hidden" id="stripe-public-key" name="stripe-public-key" value="<?php echo esc_html( $stripe_public_key ); ?>" />
    <input type="hidden" id="espad-form-id" name="espad-form-id" value="<?php echo esc_html( $selected_form_id ); ?>" />
    <input type="hidden" id="success-url" name="success-url" value="<?php echo esc_html( $success_url ); ?>" />
    <input type="hidden" id="cancel-url" name="cancel-url" value="<?php echo esc_html( $cancel_url ); ?>" />
    <input type="hidden" id="stripe-metadata-campaign" name="stripe-metadata-campaign" value="<?php echo esc_html( $stripe_metadata_campaign ); ?>" />
    <input type="hidden" id="stripe-metadata-project" name="stripe-metadata-project" value="<?php echo esc_html( $stripe_metadata_project ); ?>" />
    <input type="hidden" id="stripe-metadata-product" name="stripe-metadata-product" value="<?php echo esc_html( $stripe_metadata_product ); ?>" />
    <input type="hidden" id="currency" name="currency" value="<?php echo esc_html( $currency ); ?>" />
    <input type="hidden" id="color" name="color" value="<?php echo esc_html( $color ); ?>" />
    <input type="hidden" id="choosed_fields" name="choosed_fields" value="<?php echo esc_html( $choosed_fields ); ?>" />
    <input type="hidden" id="fix_amount" name="fix_amount" value="<?php echo esc_html( $fix_amount ); ?>" />
    <input type="hidden" id="espad-form-lang" name="espad-form-lang" value="<?php echo esc_html( $lang ); ?>" />
    <input type="hidden" id="espad-payment-layout" name="espad-payment-layout" value="<?php echo esc_html( $payment_layout ); ?>" />
    <input type="hidden" id="espad_payment_token" name="espad_payment_token" value="<?php echo esc_html( $espad_token ); ?>" />
    <input type="hidden" id="espad_amount_type" name="espad_amount_type" value="<?php echo esc_html( $amount_type ); ?>" />
    <input type="hidden" id="espad_checkout_mode" name="espad_checkout_mode" value="<?php echo esc_html( $mode ); ?>" />
  
        <?php if ( $mode == 'Campaign' ): ?>

            <div class="espad-payment-wrapper">

                <div class="campaign-image-box">
                    
                    <?php
                        echo wp_kses(
                            espad_plugin_image($campaign_image, 'Campaign Image', ['class' => 'campaign-image']),
                            ESPAD_ALLOWED_IMG_TAGS
                        );
                    ?>                    

                    <?php if ( isset($campaign_goal_amount) && $campaign_goal_amount !== '' && is_numeric($campaign_goal_amount) ): ?>

                        <div class="progress-container">

                            <div class="progress-label">
                                <?php echo esc_html(__( 'Our Goal', 'easy-stripe-payments' )); ?>: <strong><?php echo esc_html( format_campaign_amount($campaign_goal_amount, $currency) ); ?> <?php echo esc_html( $currency ); ?></strong>
                            </div>

                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" 
                                     data-progress="<?php echo esc_html( calculate_progress_bar($campaign_current_amount, $campaign_goal_amount) ); ?>">
                                </div>
                            </div>

                            <p><?php echo esc_html(__( 'Amount Raised', 'easy-stripe-payments' )); ?>: <strong><?php echo esc_html( format_campaign_amount($campaign_current_amount, $currency) ); ?> <?php echo esc_html( $currency ); ?></strong></p>

                        </div>

                    <?php endif; ?>
                    
                    <p><?php echo esc_html( $description ); ?></p>

                </div>

                <div class="espad-payment-box">
                    
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

        <?php else: ?>        

            <div>

                <div>

        <?php endif; ?> 

            <?php if ( $amount_type != 'fix_amount' ) : ?>
 
                <div id="prices_box" class="mb-3">

                    <div class="btn-group" role="group">

                        <?php if ( ($amount_type == 'select_amount' || $amount_type == 'select_and_variable_amount') && !empty($prices) ) : ?>

                            <?php foreach ( $prices as $index => $price ) : 
                                $id = 'amount-' . $price;
                                $value = intval($price) * 100; // Stripe-Cents
                                $checked = $index === 0 ? 'checked' : '';
                            ?>
                                <input 
                                       type="radio" 
                                       class="btn-check" 
                                       name="amount" 
                                       id="<?php echo esc_attr($id); ?>" 
                                       value="<?php echo esc_attr($value); ?>" 
                                       data-value="<?php echo esc_attr($price); ?>" 
                                       autocomplete="off" 
                                       <?php echo esc_html( $checked ); ?>>
                                <label 
                                       class="btn btn-outline-primary" 
                                       for="<?php echo esc_attr($id); ?>">
                                       <?php echo esc_html( format_campaign_amount($price, $currency) ); ?>
                                </label>
                            <?php endforeach; ?>

                        <?php else: ?>

                            <input type="radio" class="btn-check" name="amount" id="amount-10" value="1000" data-value="10" autocomplete="off" checked>
                            <label class="btn btn-outline-primary" for="amount-10">10</label>

                            <input type="radio" class="btn-check" name="amount" id="amount-20" value="2000" data-value="20" autocomplete="off">
                            <label class="btn btn-outline-primary" for="amount-20">20</label>

                            <input type="radio" class="btn-check" name="amount" id="amount-30" value="3000" data-value="30" autocomplete="off">
                            <label class="btn btn-outline-primary" for="amount-30">30</label>

                            <input type="radio" class="btn-check" name="amount" id="amount-40" value="4000" data-value="40" autocomplete="off">
                            <label class="btn btn-outline-primary" for="amount-40">40</label>

                            <input type="radio" class="btn-check" name="amount" id="amount-50" value="5000" data-value="50" autocomplete="off">
                            <label class="btn btn-outline-primary" for="amount-50">50</label>   

                        <?php endif; ?> 

                        <input type="number" id="amountInput" max="99999" placeholder="<?php echo esc_html(__( 'Enter amount', 'easy-stripe-payments' )); ?>" />

                    </div>

                </div>

            <?php endif; ?>

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
                    ?>
                    <?php echo esc_html( $payment_button ); ?> <sup></sup>
                </span>
            </button>

            <div id="payment-message" class="hidden"></div>

        </div> 

    </div>    
 
</form>
                
                