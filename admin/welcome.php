<?php defined( 'ABSPATH' ) || exit; ?>

<!-- Ladeanimation -->
<div id="espad-loading-overlay">
  <div class="loader"></div>
</div>

<?php 

espad_check_first_visit(); 

// Stripe data is not loaded automatically on every page visit to avoid
// unnecessary API requests and slow loading times in the admin dashboard.
// The Stripe API sections are only loaded after the user explicitly clicks
// the "Load Stripe Data" button. This is handled through a secure
// URL parameter check combined with a WordPress nonce verification.
$load_stripe_data = false;

if (
    isset( $_GET['espad_load_stripe_data'], $_GET['espad_load_stripe_data_nonce'] )
    && $_GET['espad_load_stripe_data'] === '1'
    && wp_verify_nonce(
        sanitize_text_field( wp_unslash( $_GET['espad_load_stripe_data_nonce'] ) ),
        'espad_load_stripe_data_action'
    )
) {
    $load_stripe_data = true;
}

?>

<div id="content">

    <?php 

        $tooltip_text_stripe = __( "Stripe is not a traditional bank account, but a Payment Gateway.\nThis means: Funds from customer payments are not stored permanently in a Stripe balance account.\nInstead, available amounts are automatically transferred to your linked bank account through what are called payouts.", 'easy-stripe-payments' );

        /* *************************** */
        /* STRIPE CONNECT - CONNECTION */
        /* *************************** */
        if ( defined('ESPAD_STRIPE_CONNECTED_ACCOUNT_ACCESS') && ESPAD_STRIPE_CONNECTED_ACCOUNT_ACCESS ) {
            
            require ESPAD_PLUGIN_PATH . 'admin/sections/welcome_headline.php'; 
            
            if ( $load_stripe_data ) {

                require ESPAD_PLUGIN_PATH . 'admin/sections/stripe_connect_api.php';

                require ESPAD_PLUGIN_PATH . 'admin/sections/stripe_api_2.php';       
                
            }

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
            
            if ( $load_stripe_data ) {
                
                require ESPAD_PLUGIN_PATH . 'admin/sections/stripe_api.php';

                require ESPAD_PLUGIN_PATH . 'admin/sections/stripe_api_2.php';

            }
        
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
    
    <?php
    
    $load_stripe_data_url = wp_nonce_url(
        admin_url( 'admin.php?page=espd_main&tab=welcome&espad_load_stripe_data=1' ),
        'espad_load_stripe_data_action',
        'espad_load_stripe_data_nonce'
    );
    
    ?>

    <a href="<?php echo esc_url( $load_stripe_data_url ); ?>" class="button button-primary">
        <?php echo esc_html__( 'Load Stripe Data', 'easy-stripe-payments' ); ?>
    </a>    
    
</div>

