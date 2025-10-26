<?php

defined( 'ABSPATH' ) || exit; 

/**
 * Generates and displays Stripe subscription buttons based on dynamic pricing and saved settings.
 *
 * Retrieves active recurring Stripe payment methods and product prices, processes associated 
 * subscription button settings stored in WordPress options and renders styled subscription 
 * buttons with appropriate localization and design attributes.
 *
 * @package CustomStripeIntegration
 */
$active_recurring_payment_methods = active_stripe_payment_methods(false);

$prices = \Stripe\Price::all(['product' => $shortcode_btn_id]);

if ( !empty($prices->data) ) {
    
    $price = $prices->data[0]; 
    
    $product = \Stripe\Product::retrieve($price->product);

    // Check status of the product
    if ( $product->active ) {
        
        
    } else {
        
        return;
    }
    
} else {
     
    return;
    
}

foreach ($prices->data as $preis) {

    $unit_amount = number_format($preis->unit_amount / 100, 0); // Stripe speichert Cent
    $unit_currency = strtoupper($preis->currency);  

}

$option_name = 'espd_subscription_btn_id_' . $shortcode_btn_id;
$json_string = get_option( $option_name );
$button_settings = [];

if ( $json_string ) {
    
    $decoded = json_decode( $json_string, true );
    
    if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
        
        $button_settings = $decoded;
        
    }
    
}

$button_title       = esc_attr( $button_settings['button_title']      ?? '' );
$button_size        = esc_attr( $button_settings['button_size']       ?? 'medium' );
$button_color       = esc_attr( $button_settings['button_color']      ?? '' );
$button_font_color  = esc_attr( $button_settings['button_font_color'] ?? '' );
$button_language    = esc_attr( $button_settings['button_language']   ?? 'en' );

foreach ($prices->data as $price) {

    $stripe_product = create_stripe_product_and_session($price->id, $price->product, $unit_currency, $button_language, $active_recurring_payment_methods);

    $new_button_title      = !empty($button_title) ? sanitize_text_field($button_title) : 'Subscribe';
    $new_button_color      = !empty($button_color) ? sanitize_hex_color($button_color) : '#0d8889';
    $new_button_font_color = !empty($button_font_color) ? sanitize_hex_color($button_font_color) : '#ffffff';

    $style = sprintf(
        'background-color: %s; color: %s;',
        esc_attr($new_button_color),
        esc_attr($new_button_font_color)
    );
 
    echo '<a 
            href="' . esc_url($stripe_product['checkout_url']) . '" 
            target="_blank" class="button button-primary espad_subscription_btn espad-btn-' . esc_html($button_size) . '" 
            style="' . esc_attr($style) . '">';
        echo esc_html($new_button_title);
    echo '</a>';                                    

}
