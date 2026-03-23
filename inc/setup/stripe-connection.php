<?php defined( 'ABSPATH' ) || exit; ?>

<div style="background: #f2f2f2; border-left: 5px solid #ccc; padding: 20px; margin: 20px 0; border-radius: 8px; font-family: Arial, sans-serif;">
    
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
        
        <div style="flex: 1; min-width: 250px;">
            
            <h2 style="margin-top: 0;">⚠️ <?php echo esc_html(__('Plugin not connected to Stripe', 'easy-stripe-payments' )); ?></h2>
            
            <p style="font-size: 15px; color: #333;">
                <?php echo esc_html(__('To accept payments with Stripe, you need to connect your Stripe account.', 'easy-stripe-payments' )); ?>
            </p>
            
            <p style="font-size: 15px; color: #333;">
                <?php echo esc_html__(
                    'If you need more information or assistance with connecting your account with Stripe and also setting up webhooks to integrate online payments with your CRM, feel free to contact us at:',
                    'easy-stripe-payments'
                ); ?>
                <a href="mailto:support@payments-and-donations.com">
                    <?php echo esc_html__('support@payments-and-donations.com', 'easy-stripe-payments'); ?>
                </a>
            </p>           
             
            <p style="font-size: 15px; color: #333;">
                <i>
                    <?php echo esc_html__(
                        'This plugin is powered by',
                        'easy-stripe-payments'
                    ); ?>
                    <a href="https://www.ecosys365.com" target="_blank" style="text-decoration: underline;">
                        <?php echo esc_html__('EcoSys 365 Solutions LLC', 'easy-stripe-payments'); ?>
                    </a>
                </i>
            </p>              

            <a href="<?php echo esc_url(admin_url('admin.php?page=espd_main&tab=settings')); ?>" class="espd-connect-button">
                🔌 <?php echo esc_html(__('Get started with Stripe', 'easy-stripe-payments' )); ?>
            </a>

        </div>
 
        <div style="flex-shrink: 0;">
            <?php 
                echo wp_kses(
                    espad_plugin_image('assets/images/powered_by_stripe.png?v=8', 'No Connection', ['class' => 'no-connection', 'width' => 260]),
                    ESPAD_ALLOWED_IMG_TAGS
                );                
            ?>            
        </div>
        
    </div>
    
</div>