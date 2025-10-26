<?php

defined( 'ABSPATH' ) || exit;

// Access the global WordPress database object
global $wpdb;

// Define the table name for ESPAD forms
$table = $wpdb->prefix . 'espad_forms';
 
// Check if a form ID is provided via request or shortcode
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only fetching form ID via REQUEST
if ( isset($_REQUEST['form_id']) || isset($shortcode_form_id) ) {    
  
    // Determine the selected form ID
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only fetching form ID via REQUEST
    $selected_form_id = isset($_REQUEST['form_id']) ? intval($_REQUEST['form_id']) : intval($shortcode_form_id);
    
    // Fetch the form data from the database using a prepared statement
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Intentionally used custom table
    $forms = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $selected_form_id) );
     
} else {
    
    // No form selected
    $selected_form_id = '';
    $forms = false;
    
}
  
// Fetch all available forms to populate dropdowns or form selectors
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Intentionally used custom table
$all_form_titles = $wpdb->get_results("SELECT id, form_name FROM {$table}");

// Retrieve and decrypt the stored Stripe public key
$stored_public_key = get_option( 'espd_stripe_public_key', '' );
$stripe_public_key = $stored_public_key ? espd_decrypt( $stored_public_key ) : '';

// Show a notice if the Stripe public key is not configured
if ( $stripe_public_key == "" ) {
    
    echo '<div class="notice notice-info is-dismissible"><p>' . wp_kses_post( sprintf(
        // translators: %s is a link to the plugin settings page.
        __('Please enter your <b>Stripe Public Key</b> &amp; <b>Stripe Secret Key</b> in the <a href="%s">Settings</a> section.', 'easy-stripe-payments'),
        esc_url( admin_url('admin.php?page=espd_main&tab=settings') )
    ) ) . '</p></div>';
    
}

// If form data is available, extract and prepare all needed variables
if ( $forms ) {

    foreach ( $forms as $form ) {

        $amount_type = esc_html($form->amount_type);
        $price_list  = esc_html($form->price_list);
        $prices = explode(',', $price_list);

        $payment_button  = esc_html($form->payment_button);

        // Use default label if payment button text is not set
        if ( $payment_button == "" ) {
            $payment_button = __( 'Pay', 'easy-stripe-payments' );
        }

        // Sanitize and extract all remaining form fields
        $success_url              = esc_html($form->success_url);
        $cancel_url               = esc_html($form->cancel_url);
        $stripe_metadata_campaign = esc_html($form->stripe_metadata_campaign);
        $stripe_metadata_project  = esc_html($form->stripe_metadata_project);
        $stripe_metadata_product  = esc_html($form->stripe_metadata_product);
        $currency                 = esc_html($form->currency);
        $color                    = esc_html($form->color);
        $choosed_fields           = esc_html($form->choosed_fields);
        $fix_amount               = esc_html($form->fix_amount);
        $mode                     = esc_html($form->mode);
        $campaign_image           = esc_html($form->campaign_image);
        $description              = esc_html($form->description);
        $campaign_current_amount  = esc_html($form->campaign_current_amount);
        $campaign_goal_amount     = esc_html($form->campaign_goal_amount);
        $lang                     = esc_html($form->lang);
        $payment_layout           = esc_html($form->payment_layout);

        // Store metadata and form ID in Options Table for later access
        update_option( 'espad_stripe_metadata_campaign', $stripe_metadata_campaign );
        update_option( 'espad_stripe_metadata_project', $stripe_metadata_project );
        update_option( 'espad_stripe_metadata_product', $stripe_metadata_product );
        
        update_option( 'espad_currency', $currency );
        update_option( 'espad_checkout_form_id', $selected_form_id );

        // Fallback color if none is defined
        $color = $color ?: '#0d8889';
        
    }

} else {

    // Set default values when no form is loaded
    $amount_type    = false;
    $payment_button = __( 'Pay', 'easy-stripe-payments' );
    $color          = "#0d8889";
    $lang           = 'en';
    $mode           = 'Standard';
    $payment_layout = 'auto';

    // Empty defaults for optional fields
    $success_url = $cancel_url = $currency = $choosed_fields = $fix_amount = $campaign_image = $description = $campaign_current_amount = $campaign_goal_amount = "";

    $stripe_metadata_campaign = $stripe_metadata_project = $stripe_metadata_product = "";

    // Clear related Options variables    
    delete_option( 'espad_stripe_metadata_campaign' );
    delete_option( 'espad_stripe_metadata_project' );
    delete_option( 'espad_stripe_metadata_product' );
    delete_option( 'espad_currency' );
    delete_option( 'espad_checkout_form_id' );
    
}
