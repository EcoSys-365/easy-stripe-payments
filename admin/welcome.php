<?php defined( 'ABSPATH' ) || exit; ?>

<!-- Ladeanimation -->
<div id="espad-loading-overlay">
  <div class="loader"></div>
</div>

<?php espad_check_first_visit(); ?>

<?php 

    $tooltip_text_stripe = __( "Stripe is not a traditional bank account, but a Payment Gateway.\nThis means: Funds from customer payments are not stored permanently in a Stripe balance account.\nInstead, available amounts are automatically transferred to your linked bank account through what are called payouts.", 'easy-stripe-payments' );

?>

<div id="content">

    <?php if ( defined('ESPAD_STRIPE_ACCESS') && ESPAD_STRIPE_ACCESS ) { ?>
    
        <h2 class="welcome_headline">
            <?php echo esc_html(__( 'Stripe overview', 'easy-stripe-payments' )); ?> &#10024; 
            <span 
                  class = "espad-info-box-icon has-tooltip"
                  data-tooltip = "<?php echo esc_html( $tooltip_text_stripe ); ?>"
                  data-offset-top = "-80">
                <?php 
                    echo wp_kses(
                        espad_plugin_image('assets/images/info_icon.png', 'Info Icon', ['class' => 'info-icon', 'width' => 24]),
                        ESPAD_ALLOWED_IMG_TAGS
                    );                
                ?>
            </span>
        </h2>     
    
        <?php

            printf('<canvas id="payoutChart" width="600" height="300"></canvas>');    

            require ESPAD_PLUGIN_PATH . 'admin/sections/stripe_api.php';

        ?>    
    
        <div class="espad-wrapper">
          <div class="espad-column">
            <?php if ( isset($espad_amount) ) { ?>
              <div class="balance-box">
                <div class="balance-title"><?php echo esc_html(__( 'Available balance', 'easy-stripe-payments' )); ?></div>
                <div class="balance-amount"><?php echo esc_html($espad_amount . $espad_currency); ?></div>
              </div>
            <?php } ?>

            <?php if ( isset($amount_pending) ) { ?>
              <div class="balance-box">
                <div class="balance-title"><?php echo esc_html(__( 'Pending balance', 'easy-stripe-payments' )); ?></div>
                <div class="balance-amount"><?php echo esc_html($amount_pending . $currency_pending); ?></div>
              </div>
            <?php } ?>

            <div class="balance-box">
              <div class="balance-title"><?php echo esc_html(__( 'Stripe subscriptions', 'easy-stripe-payments' )); ?></div>
              <div class="balance-amount"><?php echo esc_html($subscription_count); ?></div>
            </div>

            <div class="balance-box">
              <div class="balance-title"><?php echo esc_html(__( 'Total Stripe products', 'easy-stripe-payments' )); ?></div>
              <div class="balance-amount"><?php echo esc_html($totalProducts); ?></div>
            </div>
          </div>

          <div class="espad-column">
            <div class="balance-box">
              <div class="balance-title"><?php echo esc_html(__( 'Active Stripe products', 'easy-stripe-payments' )); ?></div>
              <div class="balance-amount"><?php echo esc_html($activeProducts); ?></div>
            </div>

            <div class="balance-box">
              <div class="balance-title"><?php echo esc_html(__( 'Inactive Stripe products', 'easy-stripe-payments' )); ?></div>
              <div class="balance-amount"><?php echo esc_html($inactiveProducts); ?></div>
            </div>

            <div class="balance-box">
              <div class="balance-title"><?php echo esc_html(__( 'Failed Payments', 'easy-stripe-payments' )); ?></div>
              <div class="balance-amount"><?php echo esc_html($failedCount); ?></div>
            </div>

            <div class="balance-box">
              <div class="balance-title"><?php echo esc_html(__( 'Total number of Stripe customers', 'easy-stripe-payments' )); ?></div>
              <div class="balance-amount"><?php echo esc_html($totalCustomers); ?></div>
            </div>
          </div>
        </div>
    
    <?php } else { ?>
    
        <?php espad_plugin_is_not_connected_to_stripe(); ?>
    
        <div class="notice notice-info is-dismissible">
            <p>
                <?php echo esc_html(__( 'Please enter your Stripe Public Key and Stripe Secret Key', 'easy-stripe-payments' )); ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=espd_main&tab=settings' ) ); ?>">
                    <?php echo esc_html(__( 'Settings', 'easy-stripe-payments' )); ?>
                </a> 
            </p>
        </div>  
    
    <?php } ?>
    
</div>

