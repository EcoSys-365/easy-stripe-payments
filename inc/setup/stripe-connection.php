<?php defined( 'ABSPATH' ) || exit; ?>

<div style="background: #fff8e1; border-left: 5px solid #ffb300; padding: 20px; margin: 20px 0; border-radius: 8px; font-family: Arial, sans-serif;">
    
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
        
        <div style="flex: 1; min-width: 250px;">
            
            <h2 style="margin-top: 0; color: #d17b00;">⚠️ <?php echo esc_html(__('Plugin not connected to Stripe', 'easy-stripe-payments' )); ?></h2>
            
            <p style="font-size: 15px; color: #333;">
                <?php echo esc_html(__('To accept payments with Stripe, you need to connect your Stripe account.', 'easy-stripe-payments' )); ?>
            </p>
            
            <p style="font-size: 15px; color: #333;">
                You can easily start with the <a href="https://docs.stripe.com/sandboxes" target="_blank" style="text-decoration: underline;">Stripe Sandbox</a> to test payments and the checkout process.
            </p>
            
            <p style="font-size: 15px; color: #333;">
                If you need more information or assistance with Stripe Webhooks to integrate Online Payments with your CRM,<br />feel free to contact us at: <a href="mailto:support@payments-and-donations.com">support@payments-and-donations.com</a>
            </p>
            
            <p style="font-size: 15px; color: #333;">
                <i>This plugin is powered by <a href="https://www.ecosys365.com" target="_blank" style="text-decoration: underline;">EcoSys365</a></i>
            </p>            

            <a href="<?php echo esc_url(admin_url('admin.php?page=espd_main&tab=settings')); ?>" class="espd-connect-button">
                🔌 <?php echo esc_html(__('Connect to Stripe now', 'easy-stripe-payments' )); ?>
            </a>

        </div>
 
        <div style="flex-shrink: 0;">
            <?php 
                echo wp_kses(
                    espad_plugin_image('assets/images/powered_by_stripe.png?v=7', 'No Connection', ['class' => 'no-connection', 'width' => 260]),
                    ESPAD_ALLOWED_IMG_TAGS
                );                
            ?>            
        </div>
        
    </div>
    
</div>