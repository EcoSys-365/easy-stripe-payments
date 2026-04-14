<?php
defined('ABSPATH') || exit; // Direct access is prohibited!
 
/**
 * payment-process.php
 *
 * Handles Stripe payment processing (Return URL Handler)
 *
 * This script handles the Stripe redirect after a payment attempt.
 * It retrieves and validates the PaymentIntent from the URL, checks the payment status,
 * and processes the result accordingly.
 *
 * Key responsibilities:
 * - Validate the incoming PaymentIntent ID from the Stripe redirect
 * - Determine payment mode (one-time or recurring) via success_url parameters
 * - Retrieve PaymentIntent, PaymentMethod, Customer, and Invoice data from Stripe
 * - Extract and normalize billing and address information (with fallbacks for subscriptions)
 * - Update the Stripe Customer with the latest billing details
 * - Store the payment and customer data in the WordPress database
 * - Update campaign totals if applicable
 * - Trigger confirmation emails
 * - Redirect the user to the configured success URL
 *
 * Note:
 * Nonce verification is intentionally skipped because this request originates
 * from a trusted Stripe redirect. Payment status validation is performed instead.
 */

// Safe, this GET param is from Stripe redirect. Stripe Payment Intent validation is in: admin/sections/preview/main-form.php
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Instead of nonce verification, we are checking the status of each payment because this is a redirect from Stripe
if ( isset($_GET['payment_intent']) && preg_match('/^pi_[a-zA-Z0-9]+$/', sanitize_text_field( wp_unslash($_GET['payment_intent']) )) ) {

    // Load the Stripe manager class if not already loaded
    class_exists( 'ESPAD\Stripe\StripeESPADManager' ) || espad_stripe_manager_init();

    // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Instead of nonce verification, we are checking the status of each payment because this is a redirect from Stripe
    $payment_intent_id        = sanitize_text_field( wp_unslash($_GET['payment_intent']) );
    $espad_form_id            = sanitize_text_field( wp_unslash($_GET['espad_form_id'] ?? '' ) );
    $success_url              = sanitize_text_field( wp_unslash($_GET['success_url'] ?? '' ) );
    $cancel_url               = sanitize_text_field( wp_unslash($_GET['cancel_url'] ?? '' ) );
    $stripe_metadata_campaign = sanitize_text_field( wp_unslash($_GET['stripe_metadata_campaign'] ?? '' ) );
    $stripe_metadata_project  = sanitize_text_field( wp_unslash($_GET['stripe_metadata_project'] ?? '' ) );
    $stripe_metadata_product  = sanitize_text_field( wp_unslash($_GET['stripe_metadata_product'] ?? '' ) );

    // subscription_payment aus success_url auslesen
    $params = [];
    $query_string = parse_url($success_url, PHP_URL_QUERY);
 
    if ( is_string($query_string) && $query_string !== '' ) {
        parse_str($query_string, $params);
    }

    $subscription_payment = $params['subscription_payment'] ?? null;    
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    $payment_mode = ( $subscription_payment === "1" ) ? 'recurring' : 'one-time';

    try {

        // PaymentIntent laden
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
        $payment_method_id = $payment_intent->payment_method ?? '';

        if ( !$payment_method_id ) {
            echo esc_html( __( 'No payment method selected.', 'easy-stripe-payments' ) );
            return;
        }

        // IDs
        $customer_id = $payment_intent->customer ?? '';
        $invoice_id  = $payment_intent->invoice ?? '';

        // Payment Method laden
        $payment_method = \Stripe\PaymentMethod::retrieve($payment_method_id);
        $payment_method_type = $payment_method->type ?? '(unknown)';

        // Optional Stripe-Objekte laden
        $customer = null;
        $invoice  = null;

        if ( !empty($customer_id) ) {
            $customer = \Stripe\Customer::retrieve($customer_id);
        }

        if ( !empty($invoice_id) ) {
            $invoice = \Stripe\Invoice::retrieve($invoice_id);
        }

        // Basis: Billing Details aus PaymentMethod
        $billing = $payment_method->billing_details ?? null;

        $name    = $billing->name ?? '';
        $email   = $billing->email ?? '';
        $phone   = $billing->phone ?? '';
        $address = $billing->address ?? null;

        // Fallbacks für recurring / subscriptions
        if ( $payment_mode === 'recurring' ) {

            if ( empty($name) && !empty($invoice) && !empty($invoice->customer_name) ) {
                $name = $invoice->customer_name;
            } elseif ( empty($name) && !empty($customer) && !empty($customer->name) ) {
                $name = $customer->name;
            }

            if ( empty($email) && !empty($invoice) && !empty($invoice->customer_email) ) {
                $email = $invoice->customer_email;
            } elseif ( empty($email) && !empty($customer) && !empty($customer->email) ) {
                $email = $customer->email;
            }

            if ( empty($phone) && !empty($customer) && !empty($customer->phone) ) {
                $phone = $customer->phone;
            }

            if ( empty($address) || empty($address->line1) ) {
                if ( !empty($invoice) && !empty($invoice->customer_address) ) {
                    $address = $invoice->customer_address;
                } elseif ( !empty($customer) && !empty($customer->address) ) {
                    $address = $customer->address;
                } elseif ( !empty($payment_intent->shipping) && !empty($payment_intent->shipping->address) ) {
                    $address = $payment_intent->shipping->address;
                }
            }
        }

        // Finale sichere Address-Felder
        $address_line1 = $address->line1 ?? '';
        $address_line2 = $address->line2 ?? '';
        $postal_code   = $address->postal_code ?? '';
        $city          = $address->city ?? '';
        $country       = $address->country ?? '';

        // Stripe Customer aktualisieren
        if ( !empty($customer_id) ) {
            
            \Stripe\Customer::update($customer_id, [
                'name'    => $name,
                'email'   => $email,
                'phone'   => $phone,
                'address' => [
                    'line1'       => $address_line1,
                    'line2'       => $address_line2,
                    'postal_code' => $postal_code,
                    'city'        => $city,
                    'country'     => $country,
                ],
            ]);
            
        }

        $amount   = $payment_intent->amount_received / 100;
        $currency = strtoupper($payment_intent->currency);

        $address_str = trim($address_line1 . ' ' . $postal_code . ' ' . $city . ' ' . $country);

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

        // Neue Zahlung speichern
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally used for a custom table
        $wpdb->insert(
            "{$wpdb->prefix}espad_payments",
            [
                'stripe_payment_id'   => $payment_intent_id,
                'email'               => $email,
                'name'                => $name,
                'phone'               => $phone,
                'address_line'        => $address_line1,
                'address_line_2'      => $address_line2,
                'postal_code'         => $postal_code,
                'city'                => $city,
                'country'             => $country,
                'amount'              => $amount,
                'currency'            => $currency,
                'mode'                => $payment_mode,
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

        // Formular / Kampagne aktualisieren
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

            $current_amount = isset($row->campaign_current_amount) && is_numeric($row->campaign_current_amount)
                ? (int) $row->campaign_current_amount
                : 0;

            $new_total = $current_amount + (int) $amount;

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

        echo wp_kses_post( '<p>Stripe API Error: ' . esc_html($e->getMessage()) . '</p>' );

    }

} else {

    echo esc_html( __( 'No payment method selected.', 'easy-stripe-payments' ) );
    
    exit;
    
}

