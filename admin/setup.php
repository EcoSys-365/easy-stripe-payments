<?php defined( 'ABSPATH' ) || exit; ?>

<?php espd_setup_steps_infobox(); ?>

<?php espd_domain_is_not_registered(); ?>

<h2><?php echo esc_html(__( 'Setup', 'easy-stripe-payments' )); ?> &#9881;</h2>
<p>
        <?php echo esc_html(__( '1. Provide Stripe Public Key &amp; Stripe Private Key', 'easy-stripe-payments' )); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=espd_main&tab=settings' ) ); ?>" target="_blank">
            <?php echo esc_html(__( 'Settings', 'easy-stripe-payments' )); ?>
        </a>
   
</p>
<p>
        <?php echo esc_html(__( '2. Create a Payment Form ( Checkout Form or Campaign Form )', 'easy-stripe-payments' )); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=espd_main&tab=forms' ) ); ?>" target="_blank">
            <?php echo esc_html(__( 'Payment Forms', 'easy-stripe-payments' )); ?>
        </a>
   
</p>
<p>
        <?php echo esc_html(__( '2.1 Testing purposes: Choose your form in the "Preview" section', 'easy-stripe-payments' )); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=espd_main&tab=preview' ) ); ?>" target="_blank">
            <?php echo esc_html(__( 'Preview', 'easy-stripe-payments' )); ?>
        </a>
   
</p>
<p>
        <?php echo esc_html(__( '3. Create a Stripe Product for recurring Payments', 'easy-stripe-payments' )); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=espd_main&tab=recurring' ) ); ?>" target="_blank">
            <?php echo esc_html(__( 'Recurring Payments', 'easy-stripe-payments' )); ?>
        </a>
   
</p>
<p>
        <?php echo esc_html(__( '3.1 Testing purposes: Click the button to test your product. You will be redirected to Stripe’s secure hosted Checkout page', 'easy-stripe-payments' )); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=espd_main&tab=recurring' ) ); ?>" target="_blank">
            <?php echo esc_html(__( 'Recurring Payments', 'easy-stripe-payments' )); ?>
        </a>
   
</p>
<p>
        <?php echo esc_html(__( '4. Add the shortcodes to the appropriate page', 'easy-stripe-payments' )); ?>
</p>
<p>
        <?php echo esc_html(__( '5. Register your domain and get priority support &amp; access to Multiple Stripe Checkouts, Multiple Subscription Payments &amp; Stripe Metadata', 'easy-stripe-payments' ));   
            echo ' <a href=" ' . esc_url(ESPAD_REGISTER_LINK) . '" target="_blank" rel="noopener">' . esc_html(__( 'Become a Premium Member', 'easy-stripe-payments' )) . '</a>';    
        ?>
</p> 

