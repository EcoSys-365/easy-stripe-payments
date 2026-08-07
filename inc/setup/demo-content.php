<?php

defined( 'ABSPATH' ) || exit;

/*
 * This file is loaded during plugin activation.
 *
 * Available variables from espd_plugin_activate():
 *
 * $table_forms
 * $table_payments
 * $wpdb
 */

/*
 * --------------------------------------------------------------------------
 * Demo Campaign Checkout
 * --------------------------------------------------------------------------
 *
 * Check this specific demo instead of checking whether the entire table
 * is empty. This allows multiple different demo checkouts to be installed.
 */
$campaign_demo_exists = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT id
         FROM {$table_forms}
         WHERE form_name = %s
         AND mode = %s
         LIMIT 1",
        'Demo Campaign',
        'Campaign'
    )
);

if ( ! $campaign_demo_exists ) {

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->insert(
        $table_forms,
        array(
            'form_name'                => 'Demo Campaign',
            'fix_amount'               => '-',
            'currency'                 => 'USD',
            'description'              =>
                'Join our volunteer campaign to support communities in need across rural Africa. This initiative focuses on providing access to clean water, education, and basic healthcare for families and children. Whether you are helping to build schools, teach local students or assist in medical outreach, your time and effort will create real, lasting impact.',
            'success_url'              => '',
            'cancel_url'               => '',
            'stripe_metadata_campaign' => '',
            'stripe_metadata_project'  => '',
            'stripe_metadata_product'  => '',
            'amount_type'              =>
                'select_and_variable_amount',
            'price_list'               =>
                '1000,2000,3000,4000,5000',
            'campaign_image'           =>
                ESPAD_PLUGIN_URL .
                'assets/images/volunteer.png',
            'payment_button'           => 'Donate',
            'mode'                     => 'Campaign',
            'campaign_current_amount'  => '74500',
            'campaign_goal_amount'     => '100000',
            'color'                    => '#0d8889',
            'choosed_fields'           =>
                'name_email_address_telephone',
            'lang'                     => 'en',
            'payment_layout'           => 'tabs',
            'checkout_metadata_1'      => null,
            'checkout_metadata_2'      => null,
            'checkout_metadata_3'      => null,
        ),
        array(
            '%s', // form_name
            '%s', // fix_amount
            '%s', // currency
            '%s', // description
            '%s', // success_url
            '%s', // cancel_url
            '%s', // stripe_metadata_campaign
            '%s', // stripe_metadata_project
            '%s', // stripe_metadata_product
            '%s', // amount_type
            '%s', // price_list
            '%s', // campaign_image
            '%s', // payment_button
            '%s', // mode
            '%s', // campaign_current_amount
            '%s', // campaign_goal_amount
            '%s', // color
            '%s', // choosed_fields
            '%s', // lang
            '%s', // payment_layout
            '%s', // checkout_metadata_1
            '%s', // checkout_metadata_2
            '%s', // checkout_metadata_3
        )
    );
}

/*
 * --------------------------------------------------------------------------
 * Multi-Step Demo Checkout
 * --------------------------------------------------------------------------
 */
$multistep_demo_name = 'Multi-Step Demo Checkout';

$multistep_demo_exists = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT id
         FROM {$table_forms}
         WHERE form_name = %s
         AND mode = %s
         LIMIT 1",
        $multistep_demo_name,
        'Multistep'
    )
);

if ( ! $multistep_demo_exists ) {

    /*
     * Build installation-independent plugin image URLs.
     *
     * These resolve automatically to the current WordPress installation
     * and therefore never contain a hard-coded localhost domain.
     */
    $demo_image_base_url =
        ESPAD_PLUGIN_URL .
        'assets/images/multi-step-checkout-demo/';

    $non_profit_team_image =
        $demo_image_base_url .
        'non-profit-team.jpg';

    $eco_support_tshirt_image =
        $demo_image_base_url .
        'eco-support-t-shirt.jpg';

    $eco_essentials_image =
        $demo_image_base_url .
        'eco-essentials-gift-set.jpg';

    /*
     * Multi-Step Checkout flow.
     *
     * imageId is set to 0 because these files are plugin assets and not
     * WordPress Media Library attachments.
     */
    $multistep_demo_flow = array(
        'app' => array(
            'name' =>
                'Easy Stripe Payments & Donations for WordPress',

            'company' => array(
                'name'    => 'EcoSys 365 Solutions LLC',
                'website' => 'https://www.ecosys365.com',
            ),

            'plugin' => array(
                'version'    => '1.4.0',
                'repository' =>
                    'https://wordpress.org/plugins/easy-stripe-payments',
                'membership' =>
                    'https://www.payments-and-donations.com',
                'liveDemos'  =>
                    'https://demo.ecosys365.com',
                'github'     =>
                    'https://github.com/EcoSys-365/easy-stripe-payments',
            ),

            'type'      => 'multistep-checkout',
            'framework' => 'react',
            'builder'   => 'react-based',
            'version'   => '1.0.0',
        ),

        'settings' => array(
            'checkoutName' =>
                $multistep_demo_name,

            'stepCount' => 4,
            'language'  => 'en',

            'continueButtonLabel' => 'Continue',
            'backButtonLabel'     => 'Back',

            'layout'           => 'standard',
            'showCheckoutName' => false,
            'currency'         => 'USD',

            'productRequiredMessage' =>
                'Please select at least one product before continuing',

            'requiredFieldMessage' =>
                'Please complete all required fields before continuing.',

            'progressBar' => 'steps',
            'color'       => '#0d8889',

            'paymentButtonLabel' =>
                'Pay',

            'securePaymentLabel' =>
                '100% Secure Payment via Stripe',
        ),

        'steps' => array(
            /*
             * Step 1
             */
            array(
                'id'    => 'step_1',
                'title' => 'Support Our Cause',

                'fields' => array(
                    array(
                        'id'    =>
                            'image_field_demo_non_profit_team',

                        'type'  => 'image_field',
                        'label' => 'Image',
                        'image' => '',

                        'settings' => array(
                            'imageId'  => 0,
                            'imageUrl' =>
                                $non_profit_team_image,
                            'imageAlt' =>
                                'Nonprofit team supporting a community project',
                            'required' => false,
                        ),
                    ),

                    array(
                        'id'    =>
                            'text_field_demo_support_message',

                        'type'  => 'text_field',
                        'label' => 'Text & HTML',
                        'image' => '',

                        'settings' => array(
                            'html' =>
                                '<p style="text-align:center;font-size:19px !important;">Help our organization by donating today! Donations go to making a difference for our cause.</p>',

                            'required' => false,
                        ),
                    ),
                ),
            ),

            /*
             * Step 2
             */
            array(
                'id'    => 'step_2',
                'title' => "Who's Giving Today?",

                'fields' => array(
                    array(
                        'id'    => 'fullname_demo',
                        'type'  => 'fullname',
                        'label' => 'Full Name',

                        'settings' => array(
                            'required' => false,
                        ),
                    ),

                    array(
                        'id'    => 'email_demo',
                        'type'  => 'email',
                        'label' => 'E-Mail',

                        'settings' => array(
                            'required' => false,
                        ),
                    ),

                    array(
                        'id'    => 'street_no_demo',
                        'type'  => 'street_no',
                        'label' => 'Street & No.',
                        'image' => '',

                        'settings' => array(
                            'required' => false,
                        ),
                    ),

                    array(
                        'id'    => 'postal_code_demo',
                        'type'  => 'postal_code',
                        'label' => 'Postal code',
                        'image' => '',

                        'settings' => array(
                            'required' => false,
                        ),
                    ),

                    array(
                        'id'    => 'city_demo',
                        'type'  => 'city',
                        'label' => 'City',
                        'image' => '',

                        'settings' => array(
                            'required' => false,
                        ),
                    ),

                    array(
                        'id'    => 'country_demo',
                        'type'  => 'country',
                        'label' => 'Country',
                        'image' => '',

                        'settings' => array(
                            'required' => false,
                        ),
                    ),

                    array(
                        'id'    => 'phone_number_demo',
                        'type'  => 'phone_number',
                        'label' => 'Phone Number',
                        'image' => '',

                        'settings' => array(
                            'required' => false,
                        ),
                    ),

                    array(
                        'id'    => 'textarea_field_demo',
                        'type'  => 'textarea_field',
                        'label' => 'Message',
                        'image' => '',

                        'settings' => array(
                            'required'    => false,
                            'placeholder' => 'Message',
                            'rows'        => 3,
                        ),
                    ),
                ),
            ),

            /*
             * Step 3
             */
            array(
                'id'    => 'step_3',
                'title' => 'Products & Donations',

                'fields' => array(
                    array(
                        'id'    =>
                            'product_demo_eco_essentials',

                        'type'  => 'product',
                        'label' =>
                            '💚 Eco Essentials Gift Set',

                        'image' => '',

                        'settings' => array(
                            'required' => false,
                            'productId' => 2,
                            'imageId'   => 0,

                            'imageUrl' =>
                                $eco_essentials_image,

                            'imageAlt' =>
                                'Eco Essentials Gift Set',

                            'price'       => '39.99',
                            'maxQuantity' => 3,

                            'description' =>
                                'Support our mission with this practical gift set designed for everyday use.',
                        ),
                    ),

                    array(
                        'id'    =>
                            'product_demo_eco_support_tshirt',

                        'type'  => 'product',
                        'label' =>
                            'Eco Support T-Shirt',

                        'image' => '',

                        'settings' => array(
                            'required' => false,
                            'productId' => 1,
                            'imageId'   => 0,

                            'imageUrl' =>
                                $eco_support_tshirt_image,

                            'imageAlt' =>
                                'Eco Support T-Shirt',

                            'price'       => '24.99',
                            'maxQuantity' => 10,

                            'description' =>
                                'Show your support with this premium eco-friendly T-shirt.',
                        ),
                    ),

                    array(
                        'id'    => 'donation_demo',
                        'type'  => 'donation',
                        'label' =>
                            'Choose your Donation Amount',
                        'image' => '',

                        'settings' => array(
                            'required'          => false,
                            'amounts'           =>
                                array( 50, 100, 200 ),
                            'allowCustomAmount' => true,

                            'customAmountLabel' =>
                                'Custom amount',

                            'customAmountPlaceholder' =>
                                'Enter amount',

                            'minimumAmount' => 5,
                        ),
                    ),

                    array(
                        'id'    => 'checkbox_demo_terms',
                        'type'  => 'checkbox',
                        'label' => 'Checkbox',
                        'image' => '',

                        'settings' => array(
                            'required' => true,

                            'checkboxHtml' =>
                                'I agree to the <a href="#" target="_blank" rel="noopener noreferrer">Terms & Conditions</a>',
                        ),
                    ),
                ),
            ),

            /*
             * Step 4
             */
            array(
                'id'    => 'step_4',
                'title' => 'Payment Details',

                'fields' => array(
                    array(
                        'id'    =>
                            'stripe_payments_demo',

                        'type'  => 'stripe_payments',
                        'label' =>
                            '💳 ⚡ Stripe Payments',

                        'image' => '',

                        'settings' => array(
                            'required'      => false,
                            'paymentFormId' => '',
                        ),
                    ),
                ),
            ),
        ),
    );

    /*
     * wp_json_encode() correctly escapes HTML, Unicode emoji and URLs.
     * Do not manually escape this JSON before inserting it.
     */
    $multistep_demo_json =
        wp_json_encode(
            $multistep_demo_flow,
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE
        );

    if ( is_string( $multistep_demo_json ) ) {

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->insert(
            $table_forms,
            array(
                'form_name'                =>
                    $multistep_demo_name,

                'fix_amount'               => '',
                'currency'                 => 'USD',
                'description'              => '',
                'success_url'              => '',
                'cancel_url'               => '',
                'stripe_metadata_campaign' => '',
                'stripe_metadata_project'  => '',
                'stripe_metadata_product'  => '',
                'amount_type'              => '',
                'price_list'               => '',
                'campaign_image'           => '',
                'payment_button'           => '',
                'mode'                     => 'Multistep',
                'campaign_current_amount'  => '',
                'campaign_goal_amount'     => '',
                'color'                    => '',
                'choosed_fields'           => '',
                'lang'                     => 'en',
                'payment_layout'           => '',
                'checkout_metadata_1'      =>
                    $multistep_demo_json,
                'checkout_metadata_2'      => null,
                'checkout_metadata_3'      => null,
            ),
            array(
                '%s', // form_name
                '%s', // fix_amount
                '%s', // currency
                '%s', // description
                '%s', // success_url
                '%s', // cancel_url
                '%s', // stripe_metadata_campaign
                '%s', // stripe_metadata_project
                '%s', // stripe_metadata_product
                '%s', // amount_type
                '%s', // price_list
                '%s', // campaign_image
                '%s', // payment_button
                '%s', // mode
                '%s', // campaign_current_amount
                '%s', // campaign_goal_amount
                '%s', // color
                '%s', // choosed_fields
                '%s', // lang
                '%s', // payment_layout
                '%s', // checkout_metadata_1
                '%s', // checkout_metadata_2
                '%s', // checkout_metadata_3
            )
        );
    }
}