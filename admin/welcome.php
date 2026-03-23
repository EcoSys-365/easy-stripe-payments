<?php defined( 'ABSPATH' ) || exit; ?>

<!-- Ladeanimation -->
<div id="espad-loading-overlay">
  <div class="loader"></div>
</div>

<?php espad_check_first_visit(); ?>

<div id="content">

    <?php 

        $tooltip_text_stripe = __( "Stripe is not a traditional bank account, but a Payment Gateway.\nThis means: Funds from customer payments are not stored permanently in a Stripe balance account.\nInstead, available amounts are automatically transferred to your linked bank account through what are called payouts.", 'easy-stripe-payments' );

        /* *************************** */
        /* STRIPE CONNECT - CONNECTION */
        /* *************************** */
        if ( defined('ESPAD_STRIPE_CONNECTED_ACCOUNT_ACCESS') && ESPAD_STRIPE_CONNECTED_ACCOUNT_ACCESS ) {

            require ESPAD_PLUGIN_PATH . 'admin/sections/welcome_headline.php'; 

            require ESPAD_PLUGIN_PATH . 'admin/sections/stripe_connect_api.php';

            require ESPAD_PLUGIN_PATH . 'admin/sections/stripe_api_2.php';        

        }
    
        /* *************************** */
        /* STRIPE API KEY - CONNECTION */
        /* *************************** */
        if (
            (!defined('ESPAD_STRIPE_CONNECTED_ACCOUNT_ACCESS') || !ESPAD_STRIPE_CONNECTED_ACCOUNT_ACCESS)
            && defined('ESPAD_STRIPE_ACCESS')
            && ESPAD_STRIPE_ACCESS
        ) {    

            require ESPAD_PLUGIN_PATH . 'admin/sections/welcome_headline.php';
                                                                                                                             
            require ESPAD_PLUGIN_PATH . 'admin/sections/stripe_api.php';
                                                                        
            require ESPAD_PLUGIN_PATH . 'admin/sections/stripe_api_2.php';
        
        } else if (
            !(
                ( defined('ESPAD_STRIPE_ACCESS') && ESPAD_STRIPE_ACCESS ) ||
                ( defined('ESPAD_STRIPE_CONNECTED_ACCOUNT_ACCESS') && ESPAD_STRIPE_CONNECTED_ACCOUNT_ACCESS )
            )
        ) {
            
            espad_plugin_is_not_connected_to_stripe();
        
        ?>

            <div class="notice notice-info is-dismissible"> 
                <p>
                    <?php echo esc_html(__( 'Please connect your Stripe account to start accepting payments.', 'easy-stripe-payments' )); ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=espd_main&tab=settings' ) ); ?>">
                        <?php echo esc_html(__( 'Settings', 'easy-stripe-payments' )); ?>
                    </a> 
                </p>
            </div>  
    
    <?php } ?>
    
</div>

