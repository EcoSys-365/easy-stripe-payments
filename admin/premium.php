<?php 

defined( 'ABSPATH' ) || exit; 

$membership_status = get_current_membership_status();
 
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameters used for admin UI tabs only, no sensitive action performed.
if ( isset($_GET['check_premium_status_now']) && $_GET['check_premium_status_now'] === 'true' ) {
    
    $is_premium = espad_premium_membership_check(true);
    
    if ( $is_premium === true ) {
        
        require_once ESPAD_PLUGIN_PATH . 'admin/sections/premium/active.php';
        
    } else {
        
        require_once ESPAD_PLUGIN_PATH . 'admin/sections/premium/inactive.php';
        
    }    
    
} else if ( $membership_status != "1" ) {

    espd_premium_lightbox(); 
    
} else if ( $membership_status == "1" ) {

    require_once ESPAD_PLUGIN_PATH . 'admin/sections/premium/active.php'; 
    
}

?>

<?php espd_domain_is_not_registered(); ?>

<?php

$premium_member_text = __( 'As a Premium &#x1F48E; Member, you\'ll get <b>Priority Support</b> and access to <b class="blue">Multiple Stripe Checkouts</b>, <b>Multiple Subscription Payments</b> &amp; <b class="blue">Stripe Metadata</b>.<br /><br />Membership is quick &amp; easy — register your website for a full year in minutes.', 'easy-stripe-payments' );

?>

<h2><?php echo esc_html(__( 'Premium Membership', 'easy-stripe-payments' )); ?> &#x1F48E;</h2>

<p><?php echo wp_kses_post($premium_member_text); ?></p>

<br />

<a href="<?php echo esc_url( add_query_arg( 'check_premium_status_now', 'true', ESPAD_CURRENT_URL ) ); ?>" id="check-premium-status" class="wp-premium-button">
    <?php echo esc_html(__( 'Check Premium Status', 'easy-stripe-payments' )); ?>
</a>

<a href="<?php echo esc_url( ESPAD_REGISTER_LINK ); ?>" class="wp-premium-button" target="_blank">
    <?php echo esc_html(__( 'Become a Premium Member', 'easy-stripe-payments' ) ); ?> <span style="color: gold;">&#9733;</span>
</a>
