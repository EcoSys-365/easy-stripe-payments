<?php

defined('ABSPATH') || exit; // Direct access is prohibited!

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * uninstall.php
 *
 * Handles the cleanup process when the Easy Stripe Payments plugin is uninstalled.
 * 
 * This script removes all plugin-related options stored in the WordPress options table,
 * including Stripe API keys, plugin settings, email configurations & membership status.
 * 
 * It also deletes dynamically generated option entries related to subscription buttons.
 * 
 * Additionally, it drops all custom database tables created by the plugin, such as those
 * for forms and payments, ensuring a complete removal of plugin data from the database.
 * 
 * This thorough cleanup helps to avoid orphaned data and keeps the WordPress database clean
 * after the plugin is uninstalled.
 */
$option_keys = [
	'espd_stripe_secret_key',
	'espd_stripe_public_key',
    'espd_membership_status',
    'espd_membership_last_check',
    'espd_welcome_page_visited',
    'espd_email_notification',
    'espd_email_subject',
    'espd_email_sender_mail',
    'espd_email_mail_content',
    'espad_stripe_metadata_campaign',
    'espad_stripe_metadata_project',
    'espad_stripe_metadata_product',
    'espad_currency',
    'espad_checkout_form_id',
];
  
$table_names = [
    'espad_forms',
	'espad_payments',
];

function espd_uninstall_cleanup_site( $option_keys, $table_names ) {
    
	global $wpdb;

	// Optionen loeschen
	foreach ( $option_keys as $option ) {
		delete_option( $option );
	}
    
    // Dynamisch generierte Felder loeschen
	$prefix = 'espd_subscription_btn_id_';
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( $prefix ) . '%' ) );    

	// Tabellen loeschen
	foreach ( $table_names as $table ) {
		$table_name = $wpdb->prefix . $table;
        /* phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange */
		$wpdb->query( "DROP TABLE IF EXISTS `$table_name`" );
	}
    
}
 
if ( is_multisite() ) {
    
	$site_ids = get_sites( [ 'fields' => 'ids' ] );

	foreach ( $site_ids as $site_id ) {
		switch_to_blog( $site_id );
		espd_uninstall_cleanup_site( $option_keys, $table_names );
		restore_current_blog();
	}
    
} else {
    
	// Einzel-Site-Bereinigung
	espd_uninstall_cleanup_site( $option_keys, $table_names );
    
}
