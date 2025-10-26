<?php defined( 'ABSPATH' ) || exit; ?>

<!-- Ladeanimation -->
<div id="espad-loading-overlay">
  <div class="loader"></div>
</div>

<?php espd_domain_is_not_registered(); ?>
 
<?php 
 
// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- GET parameters used for admin UI tabs only, no sensitive action performed.
if ( isset($_GET['membership_is_false']) && $_GET['membership_is_false'] === 'true' ) : 
 
    echo "<div id='membership-is-false'></div>";

endif; 

if ( defined('ESPAD_STRIPE_ACCESS') && ESPAD_STRIPE_ACCESS ) {

    $active_payment_methods = active_stripe_payment_methods(true); 

} else {

    ?>

    <div class="notice notice-info is-dismissible">
        <p>
            <?php echo esc_html(__( 'Please enter your Stripe Public Key and Stripe Secret Key', 'easy-stripe-payments' )); ?>
            <a href="<?php echo esc_url( admin_url('admin.php?page=espd_main&tab=settings') ); ?>">
                <?php echo esc_html(__( 'Settings', 'easy-stripe-payments' )); ?>
            </a> 
        </p>
    </div>  

    <?php

}

?>

<?php 
    $tooltip_text_products = __( "Prices cannot be changed once created — this ensures consistency in billing history and records.\nIf a price needs to change, a new price must be created and assigned to the product.\nYou can manually delete &amp; change a product through the Stripe Dashboard.", "easy-stripe-payments" );

    $tooltip_text_price_is_fix = __( "Stripe does not allow updating the price of an active product via the API.\nYou can manually delete &amp; change a product through the Stripe Dashboard.", "easy-stripe-payments" );
?>

<?php

$membership_status = get_current_membership_status();

global $wpdb;

$prefix = 'espd_subscription_btn_id_';

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$db_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s", $prefix . '%' ) );
 
?>
 
<?php if ( defined('ESPAD_STRIPE_ACCESS') && ESPAD_STRIPE_ACCESS ) : ?>

    <h2>
        <?php echo esc_html(__( 'Stripe Recurring Payments', 'easy-stripe-payments' )); ?> &#128260;
        <span 
              class = "espad-info-box-icon has-tooltip"
              data-tooltip = "<?php echo esc_html($tooltip_text_products); ?>"
              data-offset-top = "-80">
            <?php 
                echo wp_kses(
                    espad_plugin_image('assets/images/info_icon.png', 'Info', ['class' => 'info-icon', 'width' => 24]),
                    ESPAD_ALLOWED_IMG_TAGS
                );                
            ?>            
        </span>    
    </h2>

<?php endif; ?>

<?php if ( !( defined('ESPAD_STRIPE_ACCESS') && ESPAD_STRIPE_ACCESS ) ) espad_plugin_is_not_connected_to_stripe(); ?>

<div id="espd-form-recurring-modal" class="recurring_form_modal_box" style="display:none;">

    <form id="espd-update-recurring-product" enctype="multipart/form-data">
        
        <?php wp_nonce_field('espad_recurring_form_action', 'espad_recurring_form_nonce'); ?>

        <input type="hidden" id="product_id" name="product_id" value="">

        <div id="espd-admin-notice-wrapper"></div>

        <div class="form-container">

                <h3 class="orange"><?php echo esc_html(__( 'Stripe Product', 'easy-stripe-payments' )); ?> &#128230;</h3>
            
                <div class="form-row">
                    
                    <div class="form-group">
                        <label class="orange" for="choosed_product_name"><?php echo esc_html(__( 'Product Name', 'easy-stripe-payments' )); ?></label>
                        <input type="text" id="choosed_product_name" name="choosed_product_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="orange" for="choosed_product_description"><?php echo esc_html(__( 'Description', 'easy-stripe-payments' )); ?></label>
                        <textarea name="choosed_product_description" id="choosed_product_description" rows="3" class="large-text" required></textarea>                        
                    </div>
                    
                </div>
            
                <div class="form-row">
                    
                    <div class="form-group">
                        <label class="orange" for="choosed_product_images"><?php echo esc_html(__( 'Image', 'easy-stripe-payments' )); ?></label>
                        <input name="choosed_product_images" id="choosed_product_images" type="url" class="product_image_input regular-text" required>
                        <input type="button" class="button product_image_button" value="<?php echo esc_html(__( 'Select image', 'easy-stripe-payments' )); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="orange" for="choosed_product_default_price"><?php echo esc_html(__( 'Language', 'easy-stripe-payments' )); ?></label>
                        <?php require ESPAD_PLUGIN_PATH . 'admin/sections/form-languages.php'; ?>   
                    </div>
                    
                    <div class="form-group">
                        <label class="orange" for="choosed_product_default_price"><?php echo esc_html(__( 'Price', 'easy-stripe-payments' )); ?></label>
                        <input 
                               type="text" 
                               id="choosed_product_default_price" 
                               name="choosed_product_default_price" 
                               class="form-control has-tooltip" 
                               style="cursor: not-allowed;"
                               data-tooltip="<?php echo esc_html($tooltip_text_price_is_fix); ?>"
                               data-offset-top = "-56"
                               disabled>
                    </div>
                    
                </div>   
            
                <h3 class="blue" style="margin-top: 50px;"><?php echo esc_html(__( 'Subscription Button Preview', 'easy-stripe-payments' )); ?> &#128270;</h3>
            
                <a href="#" onclick="return false;" target="_blank" class="button button-primary subscription_btn"><?php echo esc_html(__( 'Subscribe', 'easy-stripe-payments' )); ?></a>
            
                <div class="form-row">
                    
                    <div class="form-group">
                        <label class="blue" for="button_title"><?php echo esc_html(__( 'Button Title', 'easy-stripe-payments' )); ?></label>
                        <input type="text" id="button_title" name="button_title" class="form-control" value="<?php echo esc_html(__( 'Subscribe', 'easy-stripe-payments' )); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="blue" for="button_size"><?php echo esc_html(__( 'Button Size', 'easy-stripe-payments' )); ?></label>
                        <select name="button_size" id="button_size" style="height: 47px;border: 1px solid #ccc;">
                          <option value="small" selected><?php echo esc_html(__( 'Small', 'easy-stripe-payments' )); ?></option>
                          <option value="medium"><?php echo esc_html(__( 'Medium', 'easy-stripe-payments' )); ?></option>
                          <option value="large"><?php echo esc_html(__( 'Large', 'easy-stripe-payments' )); ?></option>
                          <option value="x-large"><?php echo esc_html(__( 'X-Large', 'easy-stripe-payments' )); ?></option>
                          <option value="xx-large"><?php echo esc_html(__( 'XX-Large', 'easy-stripe-payments' )); ?></option>
                        </select>                        
                    </div>

                    <div class="form-group">
                        <label class="blue" for="button_color">
                            <?php echo esc_html(__( 'Button Color', 'easy-stripe-payments' )); ?></label>
                        <input type="color" style="height: 47px;width: 100%;" id="button_color" name="button_color" value="#0d8889">   
                    </div> 
                    
                    <div class="form-group">
                        <label class="blue" for="button_font_color">
                            <?php echo esc_html(__( 'Font Color', 'easy-stripe-payments' )); ?></label>
                        <input type="color" style="height: 47px;width: 100%;" id="button_font_color" name="button_font_color" value="#ffffff">   
                    </div>                     
                    
                </div>            
            
        </div>

        <p class="submit">
            <button type="submit" class="button button-primary"><?php echo esc_html(__( 'Update', 'easy-stripe-payments' )); ?></button>
        </p>

    </form>

</div>

<?php
  
// Save product
if (
    isset($_POST['espd_submit']) &&
    isset($_POST['espad_stripe_product_form_nonce']) &&
    wp_verify_nonce(
        sanitize_text_field( wp_unslash( $_POST['espad_stripe_product_form_nonce'] ) ),
        'espad_stripe_product_form_action'
    )
) {      
  
    $name           = isset($_POST['product_name']) ? sanitize_text_field(wp_unslash($_POST['product_name'])) : '';
    $desc           = isset($_POST['product_desc']) ? sanitize_textarea_field(wp_unslash($_POST['product_desc'])) : '';
    $image          = isset($_POST['product_image']) ? esc_url_raw(wp_unslash($_POST['product_image'])) : '';
    $amount         = isset($_POST['product_amount']) ? intval(wp_unslash($_POST['product_amount'])) * 100 : 0; // Amount in Cent
    $currency       = isset($_POST['currency']) ? sanitize_text_field(wp_unslash($_POST['currency'])) : 'USD';
    $lang           = isset($_POST['form_language']) ? sanitize_text_field(wp_unslash($_POST['form_language'])) : 'en';
    $parts          = isset($_POST['product_interval']) ? explode(':', sanitize_text_field(wp_unslash($_POST['product_interval']))) : [];
    $interval       = $parts[0] ?? '';
    $interval_count = isset($parts[1]) ? intval($parts[1]) : 1;
 
    try {
        
        $product = \Stripe\Product::create([
            'name' => $name,
            'description' => $desc,
            'images' => [$image],
        ]);

        $price = \Stripe\Price::create([
            'product' => $product->id,
            'unit_amount' => $amount,
            'currency' => $currency,
            'recurring' => [
                'interval' => $interval,           // "month" oder "year"
                'interval_count' => (int)$interval_count // z. B. 3 = alle 3 Monate
            ]                
        ]);
        
        if ( $product ) {
            
            $post_product_id = $product->id;
            
            $option_name = 'espd_subscription_btn_id_' . $post_product_id;

            $button_settings = [
                'button_title'      => '',
                'button_size'       => '',
                'button_color'      => '',
                'button_font_color' => '',
                'button_language'   => $lang
            ];

            update_option($option_name, json_encode($button_settings));               
            
        }        

        echo '<div class="notice notice-success"><p>'; echo esc_html(__( 'Product created: ', 'easy-stripe-payments' )); echo esc_html($product->name) . '</p></div>';

    } catch (Exception $e) {
        
        echo '<div class="notice notice-error"><p>Error: ' . esc_html($e->getMessage()) . '</p></div>';
        
    }
    
}

?>

<?php 

if ( defined('ESPAD_STRIPE_ACCESS') && ESPAD_STRIPE_ACCESS ) { ?>

    <?php if ( $db_count >= 1 && $membership_status != "1" ) { ?>

        <a href="<?php echo esc_url( add_query_arg( 'membership_is_false', 'true', ESPAD_CURRENT_URL ) ); ?>">
            <?php echo esc_html(__( 'Add new Stripe product', 'easy-stripe-payments' )); ?> &#9660;
        </a><br />

    <?php } else { ?>

        <a href="" class="add_new_stripe_product">
            <?php echo esc_html(__( 'Add new Stripe product', 'easy-stripe-payments' )); ?> &#9660;
        </a><br />

    <?php } 

} else { 

    return;
    
} 
 
?>

<div class="new_stripe_product_box" style="display: none;">

    <form method="post" enctype="multipart/form-data">
        
        <?php wp_nonce_field('espad_stripe_product_form_action', 'espad_stripe_product_form_nonce'); ?>

        <table class="form-table">

            <tr><th><label for="product_name"><?php echo esc_html(__( 'Product name', 'easy-stripe-payments' )); ?></label></th>
            <td><input name="product_name" id="product_name" type="text" class="regular-text" required></td></tr>

            <tr><th><label for="product_desc"><?php echo esc_html(__( 'Description', 'easy-stripe-payments' )); ?></label></th>
            <td><textarea name="product_desc" id="product_desc" rows="3" class="large-text" required></textarea></td></tr>

            <tr><th><label for="product_image"><?php echo esc_html(__( 'Image', 'easy-stripe-payments' )); ?></label></th>
            <td>
                <input name="product_image" class="product_image_input" type="url" class="regular-text" required>
                <input type="button" class="button product_image_button" value="<?php echo esc_html(__( 'Select image', 'easy-stripe-payments' )); ?>">
            </td></tr>
            <tr>
                <th><label for="product_amount"><?php echo esc_html(__( 'Price', 'easy-stripe-payments' )); ?></label></th>
                <td><input name="product_amount" id="product_amount" type="number" min="5" required class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="product_currency"><?php echo esc_html(__( 'Currency', 'easy-stripe-payments' )); ?></label></th>
                <td><?php require ESPAD_PLUGIN_PATH . 'admin/sections/currency.php'; ?></td>
            </tr> 
            <tr>
                <th><label for="form_language"><?php echo esc_html(__( 'Language', 'easy-stripe-payments' )); ?></label></th>
                <td><?php require ESPAD_PLUGIN_PATH . 'admin/sections/form-languages.php'; ?></td>
            </tr>             
            <tr>
                <th><label for="product_interval"><?php echo esc_html(__( 'Payment interval', 'easy-stripe-payments' )); ?></label></th>
                <td>
                    <select name="product_interval" id="product_interval" required>
                        <option value="month:1"><?php echo esc_html(__( 'Monthly', 'easy-stripe-payments' )); ?></option>
                        <option value="month:3"><?php echo esc_html(__( 'Every 3 months', 'easy-stripe-payments' )); ?></option>
                        <option value="month:6"><?php echo esc_html(__( 'Every 6 months', 'easy-stripe-payments' )); ?></option>
                        <option value="year:1"><?php echo esc_html(__( 'Annually', 'easy-stripe-payments' )); ?></option>
                    </select>
                </td>
            </tr>

        </table>

        <p><input type="submit" name="espd_submit" class="button button-primary" value="<?php echo esc_html(__( 'Create product', 'easy-stripe-payments' )); ?>"></p>
        
    </form>

</div>

<?php

try {
    
    // Alle "aktiven" Produkte abrufen (optional Limit setzen)
    $products = \Stripe\Product::all([
        'limit' => 100,
        'active' => true,
    ]);    

} catch (\Stripe\Exception\ApiErrorException $e) {
    
    echo esc_html( "Error: " . $e->getMessage() );

}

?>

<div class="wrap_box">

    <table id="espad-table" class="widefat striped" style="margin-top:20px;">
        <thead>
            <tr>
                <th><?php echo esc_html(__( 'Product Name', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Product ID', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Price', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Image', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Shortcode', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Subscription Button', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Edit', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Delete', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'View', 'easy-stripe-payments' )); ?></th>                
            </tr>
        </thead>
        <tbody>
            <?php if ( !empty($products->data) ): ?>
                <?php foreach ($products as $produkt): ?>
            
                    <?php $prices = \Stripe\Price::all(['product' => $produkt->id]); ?>
            
                    <?php 
                        foreach ($prices->data as $preis) {

                            $unit_amount = number_format($preis->unit_amount / 100, 0); // Stripe speichert Cent
                            $unit_currency = strtoupper($preis->currency);  

                        }
               
                        $option_name = 'espd_subscription_btn_id_' . $produkt->id;
                        $json_string = get_option( $option_name );
                        $button_settings = [];

                        if ( $json_string ) {
                            $decoded = json_decode( $json_string, true );
                            if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
                                $button_settings = $decoded;
                            }
                        }

                        $button_title       = esc_attr( $button_settings['button_title']      ?? '' );
                        $button_color       = esc_attr( $button_settings['button_color']      ?? '' );
                        $button_font_color  = esc_attr( $button_settings['button_font_color'] ?? '' );
                        $button_language    = esc_attr( $button_settings['button_language']   ?? 'en' );
            
                    ?>
            
                    <tr 
                        data-form-name="<?php echo esc_html($produkt->name); ?>" 
                        data-form-produkt-id="<?php echo esc_html($produkt->id); ?>"
                        data-button-title="<?php echo esc_html( $button_title ); ?>"
                        data-button-color="<?php echo esc_html( $button_color ); ?>"
                        data-button-font-color="<?php echo esc_html( $button_font_color ); ?>">
                        
                        <td><?php echo esc_html($produkt->name); ?></td>
                        <td><?php echo esc_html($produkt->id); ?></td>
                        <td><?php echo esc_html( $unit_amount . " " . $unit_currency ); ?></td>
                        <td>
                            <?php
 
                                // Image-URL
                                if ( !empty($produkt->images )) {
                                     
                                    echo wp_kses(
                                        espad_plugin_image(esc_url( $produkt->images[0] ), 'Stripe Product', ['class' => 'stripe-product', 'width' => 100]),
                                        ESPAD_ALLOWED_IMG_TAGS
                                    );                                     

                                } else {

                                    echo "<br />" . esc_html(__("No image choosed", "easy-stripe-payments"));

                                }     

                            ?>                        
                        </td>
                        <td>
                            <code id="shortcode-<?php echo esc_html($produkt->id); ?>">[espad_product_btn id="<?php echo esc_html($produkt->id); ?>"]</code>
                            <button class="button copy-button" data-target="shortcode-<?php echo esc_html($produkt->id); ?>"><?php echo esc_html(__( 'Copy', 'easy-stripe-payments' )); ?></button>
                        </td> 
                        <td>
                            <?php

                                foreach ($prices->data as $price) {
                                    
                                    $stripe_product = create_stripe_product_and_session($price->id, $price->product, $unit_currency, $button_language, $active_payment_methods);
                                    
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
                                            target="_blank" class="button button-primary subscription_btn_preview" 
                                            style="' . esc_attr($style) . '">';
                                        echo esc_html($new_button_title);
                                    echo '</a>';                                    
                                    
                                }

                            ?>
                            
                        </td>
                        <td>
                            <button 
                                    class="button edit-recurring-button" 
                                    data-id="<?php echo esc_html($produkt->id); ?>"
                                    data-product-price="<?php echo esc_html( $unit_amount . " " . $unit_currency ); ?>">
                                &#9998; <?php echo esc_html(__( 'Edit', 'easy-stripe-payments' )); ?>
                            </button> 
                        </td>
                        <td>
                            <button 
                                    class="button delete-button-recurring-payment" 
                                    data-id="<?php echo esc_html($produkt->id); ?>" 
                                    style="color: #b32d2e;">
                                &#128465; <?php echo esc_html(__( 'Delete', 'easy-stripe-payments' )); ?>
                            </button>                        
                        </td>                        
                        <td>
                            <a href="<?php echo esc_url( $stripe_product['checkout_url'] ); ?>" 
                               class="button preview-button" 
                               target="_blank" 
                               rel="noopener noreferrer">
                               &#128270; <?php echo esc_html(__( 'View', 'easy-stripe-payments' )); ?>
                            </a>
                        </td>                        
                    </tr>
                <?php endforeach; ?>
            
            <?php else: ?>
            
                <tr><td colspan="4"><?php echo esc_html(__( 'No products added yet.', 'easy-stripe-payments' )); ?></td></tr>
            
            <?php endif; ?>
        </tbody>
    </table>
</div>
