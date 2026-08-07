<?php defined( 'ABSPATH' ) || exit; ?>
 
<div
     id="espad-payment-successful-reload" 
     data-name="<?php echo esc_attr($name); ?>"
     data-email="<?php echo esc_attr($email); ?>"
     data-phone="<?php echo esc_attr($phone); ?>"
     data-address-street="<?php echo esc_attr($address_str); ?>"
     data-amount="<?php echo esc_attr($amount); ?>"
     data-currency="<?php echo esc_attr($currency); ?>"
     data-payment-method="<?php echo esc_attr($payment_method_type); ?>">
</div>  
     