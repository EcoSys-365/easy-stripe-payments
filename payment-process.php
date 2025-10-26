<?php
defined('ABSPATH') || exit; // Direct access is prohibited!

/**
 * payment-process.php
 *  
 * Handles Stripe payment processing
 * 
 * This file manages the retrieval and verification of Stripe PaymentIntents via
 * the Stripe API. It processes incoming requests with a 'payment_intent' parameter,
 * verifies payment status, handles errors, and extracts relevant metadata related
 * to campaigns, projects, and products associated with the payment.
 *
 * It also ensures the Stripe manager class is loaded and initializes it if necessary.
 *
 * This script is a crucial part of the payment flow, providing server-side validation
 * and handling after payment submission through Stripe.
 */
        
// Safe, this GET param is from Stripe redirect. Token validation is in: admin/sections/preview/main-form.php
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Instead of nonce verification, we are using token validation because this is a redirect from Stripe
if ( isset($_GET['payment_intent']) && preg_match('/^pi_[a-zA-Z0-9]+$/', sanitize_text_field( wp_unslash($_GET['payment_intent']) )) ) {    
     
    // Load the Stripe manager class if not already loaded
    class_exists( 'ESPAD\Stripe\StripeESPADManager' ) || espad_stripe_manager_init(); 
    
    // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Instead of nonce verification, we are using token validation because this is a redirect from Stripe
    $payment_intent_id        = sanitize_text_field( wp_unslash($_GET['payment_intent']) );
    $espad_form_id            = sanitize_text_field( wp_unslash($_GET['espad_form_id'] ?? '' ) );
    $success_url              = sanitize_text_field( wp_unslash($_GET['success_url'] ?? '' ) );
    $cancel_url               = sanitize_text_field( wp_unslash($_GET['cancel_url'] ?? '' ) );
    $stripe_metadata_campaign = sanitize_text_field( wp_unslash($_GET['stripe_metadata_campaign'] ?? '' ) );
    $stripe_metadata_project  = sanitize_text_field( wp_unslash($_GET['stripe_metadata_project'] ?? '' ) );
    $stripe_metadata_product  = sanitize_text_field( wp_unslash($_GET['stripe_metadata_product'] ?? '' ) );
    // phpcs:enable WordPress.Security.NonceVerification.Recommended -- Instead of nonce verification, we are using token validation because this is a redirect from Stripe
 
    try {
        
        // PaymentIntent 
        $payment_intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);

        if ( $payment_intent->status !== 'succeeded' ) {
             
            require_once ESPAD_PLUGIN_PATH . 'frontend/sections/payment-pending.php';
            
            add_action('wp_enqueue_scripts', function() {
                wp_enqueue_script(
                    'espd-payment-reload',
                    ESPAD_PLUGIN_URL . 'assets/js/payment-reload.js',
                    [],
                    '1.0.10',
                    true
                );
            });
            
            return;
             
        }

        // payment_method ID 
        $payment_method_id = $payment_intent->payment_method;

        if ( !$payment_method_id ) {
            
            echo esc_html(__( 'No payment method selected.', 'easy-stripe-payments' ));
            
            return;
            
        }

        // Payment Method 
        $payment_method = \Stripe\PaymentMethod::retrieve($payment_method_id);
        
        $payment_method_type = $payment_method->type ?? '(unknown)';
        
        // Billing Details 
        $billing = $payment_method->billing_details;
        $name    = $billing->name ?? '';
        $email   = $billing->email ?? '';
        $phone   = $billing->phone ?? '';
        $address = $billing->address;

        $amount   = $payment_intent->amount_received / 100;
        $currency = strtoupper($payment_intent->currency);
        
        $address_str = trim($address->line1 . ' ' . $address->postal_code . ' ' . $address->city . ' ' . $address->country);
  
        // /////////////////////////
        // Save into WordPress-DB
        // /////////////////////////
        global $wpdb;

        // Check, if this PaymentIntent was already saved
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally used for a custom table 
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}espad_payments WHERE stripe_payment_id = %s",
            $payment_intent_id
        ));

        if ( $exists > 0 ) {
            
            require_once ESPAD_PLUGIN_PATH . 'frontend/sections/payment-successful-reload.php';
            
            return;
            
        } else {
            
            require_once ESPAD_PLUGIN_PATH . 'frontend/sections/payment-successful.php';      
            
        }

        // New Payment. Save into Database
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally used for a custom table
        $wpdb->insert(
            "{$wpdb->prefix}espad_payments",
            [
                'stripe_payment_id'   => $payment_intent_id,
                'email'               => $email,
                'name'                => $name,
                'phone'               => $phone,
                'address_line'        => $address->line1,
                'address_line_2'      => $address->line2,
                'postal_code'         => $address->postal_code,
                'city'                => $address->city,
                'country'             => $address->country,
                'amount'              => $amount,
                'currency'            => $currency,
                'mode'                => 'one-time', 
                'payment_method_type' => $payment_method_type,
                'payment_form_id'     => $espad_form_id,
                'success_url'         => $success_url,
                'cancel_url'          => $cancel_url,
                'metadata_campaign'   => $stripe_metadata_campaign,
                'metadata_project'    => $stripe_metadata_project,
                'metadata_product'    => $stripe_metadata_product,
                'created_at'          => current_time('mysql')
            ]
        );
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally used for a custom table
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT campaign_goal_amount, campaign_current_amount 
                 FROM {$wpdb->prefix}espad_forms
                 WHERE id = %d AND mode = %s",
                $espad_form_id,
                'Campaign'
            )
        );
        
        if ( $row && isset($row->campaign_goal_amount) && is_numeric($row->campaign_goal_amount) ) {
            
            $amount = (int) round($amount);
            
            // Get the current amount
            $current_amount = isset($row->campaign_current_amount) && is_numeric($row->campaign_current_amount)
                ? (int) $row->campaign_current_amount
                : 0;

            // Calculate new total
            $new_total = $current_amount + (int) $amount;

            // Update in the database
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally used for a custom table
            $wpdb->update(
                "{$wpdb->prefix}espad_forms",
                [ 'campaign_current_amount' => $new_total ],
                [ 'id' => $espad_form_id ],
                [ '%d' ],
                [ '%d' ]
            );
            
        }   
        
        require_once ESPAD_PLUGIN_PATH . 'inc/paymentProcess/send-mail.php'; 
        
        // Redirect to success_url
        espad_redirect_to_url($success_url);      

    } catch (\Stripe\Exception\ApiErrorException $e) {
        
        echo esc_html( "<p>Stripe API Error: " . esc_html($e->getMessage()) . "</p>" );
        
    }
    
} else {
    
    echo esc_html(__( 'No payment method selected.', 'easy-stripe-payments' ));
    
    exit;

}




