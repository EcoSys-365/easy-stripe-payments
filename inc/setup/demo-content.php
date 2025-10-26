<?php

defined( 'ABSPATH' ) || exit; 

/**
 * Inserts a predefined demo campaign form into the database if no forms exist.
 *
 * This is used during plugin activation to provide users with an example form
 * pre-configured with default values such as donation amounts, layout, and content.
 * The demo campaign helps illustrate how a campaign form works and can be modified
 * or removed by the site administrator after activation.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 */
 
/* phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared */
$form_exists = $wpdb->get_var( "SELECT COUNT(*) FROM $table_forms" );

if ( $form_exists == 0 ) {
   
    /* phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared */
    $wpdb->insert(
        $table_forms,
        [
            'form_name'               => 'Demo Campaign',
            'fix_amount'              => '-',
            'currency'                => 'USD',
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
            'description'             => 'Join our volunteer campaign to support communities in need across rural Africa. This initiative focuses on providing access to clean water, education, and basic healthcare for families and children. Whether you are helping to build schools, teach local students or assist in medical outreach, your time and effort will create real, lasting impact.',
            'success_url'             => '',
            'cancel_url'              => '',
            'stripe_metadata_campaign'=> '',
            'stripe_metadata_project' => '',
            'stripe_metadata_product' => '',
            'amount_type'             => 'select_and_variable_amount',
            'price_list'              => '1000,2000,3000,4000,5000',
            'campaign_image'          => ESPAD_PLUGIN_URL . 'assets/images/volunteer.png',
            'payment_button'          => 'Donate',
            'mode'                    => 'Campaign',
            'campaign_current_amount' => '74500',
            'campaign_goal_amount'    => '100000',
            'color'                   => '#0d8889',
            'choosed_fields'          => 'name_email_address_telephone',
            'lang'                    => 'en',
            'payment_layout'          => 'tabs',
        ]
    );

}