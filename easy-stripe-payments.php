<?php
/**
 * Plugin Name: Easy Stripe Payments
 * Description: A user-friendly WordPress plugin for accepting <strong>one-time and recurring Stripe payments</strong>. Perfect for businesses, freelancers and Non-Profit organizations. Secure, fast and fully PCI-compliant.
 * Version: 1.0.0
 * Author: EcoSys365
 * Author URI: https://www.ecosys365.com
 * Plugin URI: https://www.payments-and-donations.com
 * Text Domain: easy-stripe-payments
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html 
 * Requires at least: 5.5
 * Tested up to: 6.8
 * Requires PHP: 7.4 
 */
defined( 'ABSPATH' ) || exit;  

// Composer Autoload
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}
  
// Define the base site URL if it hasn't been defined already.
defined( 'ESPAD_SITE_URL' )    || define( 'ESPAD_SITE_URL', get_site_url() );
// Define the current domain (host) if not already defined.
defined( 'ESPAD_DOMAIN' )      || define( 'ESPAD_DOMAIN', isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '' );
// Define the plugin's public URL path (used to enqueue scripts/styles).
defined( 'ESPAD_PLUGIN_URL' )  || define( 'ESPAD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
// Define the plugin's absolute filesystem path (used to include PHP files).
defined( 'ESPAD_PLUGIN_PATH' ) || define( 'ESPAD_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
// Define the Domain for Emails etc.
defined( 'ESPAD_SITE_DOMAIN' ) || define( 'ESPAD_SITE_DOMAIN', preg_replace( '/^www\./', '', wp_parse_url( get_site_url(), PHP_URL_HOST ) ) );
// Define the current URL if not already defined
defined( 'ESPAD_CURRENT_URL' ) || define( 'ESPAD_CURRENT_URL', ( function() {
    $scheme = is_ssl() ? 'https' : 'http';
    $uri    = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
    return $scheme . '://' . ESPAD_DOMAIN . $uri;
} )() );
 
// Include the plugin's helper functions.
require_once ESPAD_PLUGIN_PATH . 'inc/functions.php';
 
// Hook into the 'init' action to run custom initialization logic early.
add_action( 'init', function () {
    
    // Start a session if none is currently active.
    if ( session_status() === PHP_SESSION_NONE ) session_start();
    
    // Load the Stripe ESPAD Manager class if not already loaded
    class_exists( 'ESPAD\Stripe\StripeESPADManager' ) || espad_stripe_manager_init(); 
      
    // Register JS Redirect Script
    wp_register_script(
        'espad-redirect-script',
        ESPAD_PLUGIN_URL . 'assets/js/redirect.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );     
     
    // Membership status check
    espad_membership_check();
     
});

/**
 * Register a custom REST API endpoint for handling Stripe Checkout ( Recurring Payment ) success redirects.
 * This endpoint can be used to retrieve the payment_intent ID from the URL
 * and optionally fetch additional payment details from Stripe via the SDK.
 *
 * Route: /wp-json/easy-stripe-payments/v1/success
 * Method: GET
 */ 
add_action('rest_api_init', function () {
     
    register_rest_route('easy-stripe-payments/v1', '/success/', [
        'methods'  => 'GET',
        'callback' => 'easy_stripe_payments_stripe_success_callback',
        'permission_callback' => '__return_true', // Allows frontend users to access this endpoint
    ]);    
     
});
 
/**
 * Handles the Stripe success callback for Recurring Payments.
 * 
 * This function is triggered when Stripe redirects back after a successful payment.
 * It loads the recurring payment processing logic to handle the subscription setup.
 *
 * @param WP_REST_Request $request The incoming REST API request from Stripe.
 */
function easy_stripe_payments_stripe_success_callback($request) {
    
    require_once ESPAD_PLUGIN_PATH . 'recurring-payment-process.php';
    
}

/**
 * Checks the membership status once per day by comparing the last check date
 * stored in the database with the current date. If not checked today, updates
 * the date and triggers the premium membership verification.
 *
 * @return void
 */
function espad_membership_check() {
    
    $last_check = get_option('espd_membership_last_check'); 
    
    $today = gmdate('dmY');

    if ( $last_check !== $today ) {
        
        espad_premium_membership_check();
        
    }
    
}   

/**
 * Enqueues necessary styles and scripts for the Stripe Checkout page.
 * This includes scoped Bootstrap styles, custom checkout styles, and Stripe JS.
 */
function espd_preview_add_scripts() {
    
    // Enqueue CSS styles
    wp_enqueue_style('espd-bootstrap-scoped', ESPAD_PLUGIN_URL . 'assets/css/bootstrap-scoped.css', [], '1.0.1');
    wp_enqueue_style('espd-checkout-css', ESPAD_PLUGIN_URL . 'inc/stripeCheckout/checkout.css', [], '1.0.7');

    // Register Stripe.js as an external script
    wp_register_script(
        'stripe-js',
        'https://js.stripe.com/v3/',
        [],
        '3', // Version
        true // Load in footer
    );
 
    // Register and enqueue your custom checkout script, dependent on Stripe
    wp_enqueue_script(
        'espd-checkout-js',
        ESPAD_PLUGIN_URL . 'inc/stripeCheckout/checkout.js',
        ['stripe-js'],
        '1.0.144',
        true // Load in footer
    );
      
}

/**
 * Enqueues styles and scripts required for rendering the frontend payment form.
 *
 * This function is typically called within a shortcode context to load
 * all necessary assets for the payment form UI, including jQuery,
 * custom JS, and styles.
 *
 * @return void
 */
function espd_add_payment_shortcode_scripts() {
    
    // Enqueue frontend payment form CSS with versioning
    wp_enqueue_style(
        'espd-frontend-payment-style',
        ESPAD_PLUGIN_URL . 'assets/css/frontend-payment-form.css',
        array(),
        '1.0.91'
    );
   
    // Enqueue built-in jQuery
    wp_enqueue_script('jquery');

    // Enqueue frontend payment form JS with versioning and footer placement
    wp_enqueue_script(
        'espd-frontend-script',
        ESPAD_PLUGIN_URL . 'assets/js/frontend-payment-form.js',
        array('jquery'),
        '1.0.12',
        true
    );
    
}

/**
 * Enqueues DataTables scripts and styles for use in the payment overview.
 *
 * This function loads the jQuery DataTables plugin from a CDN,
 * allowing for sortable and searchable tables in the admin or frontend.
 *
 * @return void
 */
function espd_payments_add_scripts() {
    
    // Enqueue the DataTables JavaScript library, dependent on jQuery.
    wp_enqueue_script(
        'datatables-js',
        ESPAD_PLUGIN_URL . 'assets/js/jquery.dataTables.min.js',
        array('jquery'),
        '1.13.11',
        true
    );    
      
    // Enqueue the DataTables CSS 
    wp_enqueue_style(
        'datatables-css',
        ESPAD_PLUGIN_URL . 'assets/css/jquery.dataTables.min.css',
        array(),
        '1.13.11'
    );    
       
}    

/**
 * Enqueues the WordPress media uploader and a custom uploader script.
 *
 * This function loads the native WordPress media uploader functionality
 * and attaches a custom JavaScript file to handle media uploads in the plugin.
 *
 * @return void
 */
function espd_add_media_uploader_scripts() {
    
    // Enqueue WordPress's built-in media uploader scripts and styles.
    wp_enqueue_media();

    // Enqueue the plugin's custom JavaScript for media upload handling.
    wp_enqueue_script(
        'espd-media-uploader',
        ESPAD_PLUGIN_URL . 'assets/js/media-uploader.js',
        ['jquery'],
        '1.0.8', // Version for cache busting
        true     // Load in footer
    );
    
}


/**
 * Conditionally enqueues admin scripts based on the current page and tab in the plugin settings.
 *
 * This anonymous function hooked into 'admin_init' adds specific scripts/styles
 * to the <head> section of the admin panel, depending on which plugin tab is active.
 */  
add_action('admin_init', function() {
     
    // Load scripts for the "Preview" tab.
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameters used for admin UI tabs only, no sensitive action performed.
    if ( isset($_GET['page'], $_GET['tab']) && $_GET['page'] === 'espd_main' && $_GET['tab'] === 'preview' ) { 
        add_action('admin_head', 'espd_preview_add_scripts', 99);
        add_action('admin_head', 'espd_add_payment_shortcode_scripts', 99);
    }

    // Load charting script for the "Welcome" tab.
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameters used for admin UI tabs only, no sensitive action performed.
    if ( isset($_GET['page'], $_GET['tab']) && $_GET['page'] === 'espd_main' && $_GET['tab'] === 'welcome' ) { 
        
        // Register and load Chart.js
        wp_enqueue_script(
            'espd-chartjs',
            ESPAD_PLUGIN_URL . 'assets/js/chart.js',
            array(),
            '1.0.0', // Version 
            false
        );
        
    }
  
    // Load DataTables for the "Payments" tab.
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameters used for admin UI tabs only, no sensitive action performed.
    if ( isset($_GET['page'], $_GET['tab']) && $_GET['page'] === 'espd_main' && $_GET['tab'] === 'payments' ) { 
        
        add_action('admin_head', 'espd_payments_add_scripts', 99);
        
    } 

    // Load media uploader for "Recurring" and "Forms" tabs.
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameters used for admin UI tabs only, no sensitive action performed.
    if ( isset($_GET['page'], $_GET['tab']) && $_GET['page'] === 'espd_main' && ( $_GET['tab'] === 'recurring' || $_GET['tab'] === 'forms' ) ) {
        
        add_action('admin_head', 'espd_add_media_uploader_scripts', 99); 
        
    }  
    
});

/**
 * Redirects to the default "welcome" tab if no tab is specified in the plugin admin page URL.
 *
 * This ensures a consistent user experience by automatically selecting the default
 * tab (e.g., "welcome") when the plugin settings page is accessed without a `tab` parameter.
 *
 * Hooked into 'admin_init'.
 *
 * @return void
 */
add_action('admin_init', 'espd_redirect_to_default_tab');

function espd_redirect_to_default_tab() {
    
    // Check if we're on the plugin's main admin page and no tab is selected.
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameters used for admin UI tabs only, no sensitive action performed.
    if ( isset($_GET['page']) && $_GET['page'] === 'espd_main' && !isset($_GET['tab']) ) {
        
        // Redirect to the default tab "welcome".
        wp_redirect(admin_url('admin.php?page=espd_main&tab=welcome'));
        
        exit;
        
    }
    
}

/**
 * Enqueues frontend assets only when specific ESPAD shortcodes are used in the post content.
 *
 * This function hooks into 'wp_enqueue_scripts' and conditionally loads JavaScript and styles
 * needed for the [espad_payment_form] and [espad_product_btn] shortcodes.
 *
 * @return void
 */
add_action('wp_enqueue_scripts', 'espad_enqueue_frontend_assets');

function espad_enqueue_frontend_assets() {
    
    if ( ! is_singular() ) {
        return;
    }

    $post = get_queried_object();

    if ( ! ($post instanceof WP_Post) ) {
        return;
    }

    $has_payment_form = has_shortcode($post->post_content, 'espad_payment_form');
    $has_product_btn  = has_shortcode($post->post_content, 'espad_product_btn');

    if ( $has_payment_form || $has_product_btn ) {
        
        // Load payment form styles and scripts
        espd_add_payment_shortcode_scripts();        
        
        // SweetAlert
        wp_enqueue_script('sweetalert');
        
    }

    if ( $has_payment_form ) {
        
        // Load Stripe.js, scoped Bootstrap, and checkout flow scripts
        espd_preview_add_scripts();
        
    }
    
}


/**
 * Registers the [espad_payment_form] shortcode and its callback.
 *
 * This shortcode is used to render a payment form based on a given form ID.
 * It ensures required scripts are loaded and includes form rendering logic from external files.
 */
add_shortcode('espad_payment_form', 'espad_render_payment_form');

/**
 * Callback function for the [espad_payment_form] shortcode.
 *
 * Loads necessary frontend scripts and renders a dynamic payment form based on the provided `id` attribute.
 *
 * @param array $atts Shortcode attributes, expects 'id' as the form identifier.
 * @return string The rendered HTML output of the payment form.
 */
function espad_render_payment_form($atts) {
    
    // Define default shortcode attributes and merge with provided ones.
    $atts = shortcode_atts([
        'id' => null,
    ], $atts, 'espad_payment_form');

    // Sanitize and validate the provided form ID.
    $form_id = intval($atts['id']);
    
    if ( !$form_id ) return '<p>' . esc_html__('Invalid payment form ID.', 'easy-stripe-payments') . '</p>';
    
    // Store form ID for use in included template files.
    $shortcode_form_id = $form_id;

    // Define a mode variable (e.g., for preview vs live display styling).
    $mode = '';

    // Start output buffering to capture HTML content.
    ob_start(); 
    
    require_once ESPAD_PLUGIN_PATH . 'frontend/main-form.php';
    
    // Return the captured output as a string to be displayed via shortcode.
    return ob_get_clean();
    
}

/**
 * Registers the [espad_product_btn] shortcode and its callback.
 *
 * This shortcode is used to render a payment subscription button on a given Product ID.
 * It ensures required scripts are loaded and includes button rendering logic from external files.
 */
add_shortcode('espad_product_btn', 'espad_render_payment_button');

/**
 * Callback function for the [espad_product_btn] shortcode.
 *
 * Loads necessary frontend scripts and renders a dynamic payment subscription button based on the provided `id` attribute.
 *
 * @param array $atts Shortcode attributes, expects 'id' as the button identifier.
 * @return string The rendered HTML output of the payment button.
 */
function espad_render_payment_button($atts) {
    
    // Enqueue assets only once and correctly within the WordPress load lifecycle.
    add_action('wp_enqueue_scripts', function() {
        
        // SweetAlert
        wp_enqueue_script('sweetalert');
        
    });     
    
    // Define default shortcode attributes and merge with provided ones.
    $atts = shortcode_atts([
        'id' => null,
    ], $atts, 'espad_product_btn');

    // Sanitize and validate the provided form ID.
    $btn_id = sanitize_text_field($atts['id']);
    
    if ( !$btn_id ) return '<p>' . esc_html__('Invalid payment product ID.', 'easy-stripe-payments') . '</p>';

    // Store form ID for use in included template files.
    $shortcode_btn_id = $btn_id;

    // Define a mode variable (e.g., for preview vs live display styling).
    $mode = '';

    // Start output buffering to capture HTML content.
    ob_start(); 
    
    require_once ESPAD_PLUGIN_PATH . 'frontend/subscription-btn.php';
    
    // Return the captured output as a string to be displayed via shortcode.
    return ob_get_clean();
    
}

/**
 * Registers the plugin activation hook.
 * Runs database table creation and initial option setup on plugin activation.
 */
register_activation_hook( __FILE__, 'espd_plugin_activate' );

/**
 * Plugin activation callback.
 *
 * Creates necessary custom database tables using dbDelta and initializes default options.
 *
 * @return void
 */
function espd_plugin_activate() {
    
    global $wpdb;

    // Get charset and collation for database tables, ensuring compatibility.
    $charset_collate = $wpdb->get_charset_collate();

    // Define table names with WordPress prefix.
    $table_forms = $wpdb->prefix . 'espad_forms';
    $table_payments = $wpdb->prefix . 'espad_payments';

    // SQL statement to create the required tables.
    // Note: Multiple CREATE TABLE statements separated by semicolon.
    $sql = "
    CREATE TABLE $table_forms (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        form_name VARCHAR(255) NOT NULL,
        fix_amount VARCHAR(4) NOT NULL,
        currency VARCHAR(3) NOT NULL,
        description TEXT,
        success_url TEXT,
        cancel_url TEXT,
        stripe_metadata_campaign TEXT,
        stripe_metadata_project TEXT,
        stripe_metadata_product TEXT,
        amount_type VARCHAR(200) NOT NULL,
        price_list VARCHAR(255) NOT NULL,
        campaign_image VARCHAR(255) NOT NULL,
        payment_button VARCHAR(255) NOT NULL,
        mode VARCHAR(100) NOT NULL, 
        campaign_current_amount VARCHAR(6) NOT NULL,
        campaign_goal_amount VARCHAR(6) NOT NULL,
        color VARCHAR(7) NOT NULL,
        choosed_fields VARCHAR(100) NOT NULL, 
        lang VARCHAR(2) NOT NULL,
        payment_layout VARCHAR(10) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;

    CREATE TABLE $table_payments (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        stripe_payment_id VARCHAR(255) NOT NULL UNIQUE,
        name VARCHAR(255),
        email VARCHAR(255),
        phone VARCHAR(50),
        address_line VARCHAR(255),
        address_line_2 VARCHAR(255),
        postal_code VARCHAR(150),
        city VARCHAR(200),
        country VARCHAR(200),
        amount VARCHAR(200),
        currency VARCHAR(3),
        mode VARCHAR(150),
        payment_method_type VARCHAR(150),
        payment_form_id VARCHAR(100),
        success_url TEXT,
        cancel_url TEXT,
        metadata_campaign TEXT,
        metadata_project TEXT,
        metadata_product TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;
    ";

    // Load upgrade functions for dbDelta.
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    // Execute SQL queries to create or update tables.
    dbDelta( $sql );
    
    // File: includes/setup/demo-content.php
    require_once ESPAD_PLUGIN_PATH . 'inc/setup/demo-content.php';    

    // Store the current date in a custom encrypted option.
    $current_date = gmdate('jmY');
    
    update_option( 'espd_membership_last_check', espd_encrypt( $current_date ) );

    // Initialize email-related options if they don't exist.
    if ( get_option('espd_membership_status') === false ) {
        update_option( 'espd_membership_status', espd_encrypt('0') );
    }
    
    if ( get_option('espd_email_notification') === false ) {
        add_option('espd_email_notification', 0);
    }

    if ( get_option('espd_email_subject') === false ) {
        add_option('espd_email_subject', '');
    }

    if ( get_option('espd_email_sender_mail') === false ) {
        add_option('espd_email_sender_mail', '');
    }

    if ( get_option('espd_email_mail_content') === false ) {
        add_option('espd_email_mail_content', '');
    }
    
}

/**
 * Register the plugin's admin menu and submenu pages.
 *
 * Adds a top-level menu and several submenus under it, each linked to a callback function
 * that renders the corresponding admin page.
 *
 * @return void
 */
add_action( 'admin_menu', 'espd_register_admin_menu' );

function espd_register_admin_menu() { 
     
    // Add the main menu page in the WordPress admin sidebar
    add_menu_page(
        __( 'Stripe Payments', 'easy-stripe-payments' ),  // Page title (shown in <title>)
        __( 'Stripe Payments', 'easy-stripe-payments' ),  // Menu title (shown in sidebar)
        'manage_options',                                 // Capability required to access
        'espd_main',                                      // Menu slug / page identifier
        'espd_render_admin_page',                         // Callback function to display page content
        'dashicons-money-alt',                            // Dashicon icon for the menu
        6                                                 // Position in the menu order
    );
      
    // Add submenu page for Overview (same slug as main menu, so it replaces main page link)
    add_submenu_page(
        'espd_main', 
        __( 'Overview &#10024;', 'easy-stripe-payments' ), 
        __( 'Overview &#10024;', 'easy-stripe-payments' ), 
        'manage_options',
        'espd_main', 
        'espd_render_admin_page' 
    ); 
 
    // Add submenu page for Payments tab
    add_submenu_page(
        'espd_main', 
        __( 'Payments &#128176;', 'easy-stripe-payments' ), 
        __( 'Payments &#128176;', 'easy-stripe-payments' ), 
        'manage_options',
        'espd_main&tab=payments',  
        'espd_render_payments_page'
    );     
    
    // Add submenu page for Settings tab
    add_submenu_page(
        'espd_main', 
        __( 'Settings &#128295;', 'easy-stripe-payments' ), 
        __( 'Settings &#128295;', 'easy-stripe-payments' ), 
        'manage_options',
        'espd_main&tab=settings', 
        'espd_render_settings_page' 
    ); 
    
    // Add submenu page for Premium tab
    add_submenu_page(
        'espd_main', 
        __( 'Premium &#9733;', 'easy-stripe-payments' ), 
        __( 'Premium &#9733;', 'easy-stripe-payments' ), 
        'manage_options',
        'espd_main&tab=premium', 
        'espd_render_premium_page' 
    );     
    
    // Add submenu page for Help & FAQ tab
    add_submenu_page(
        'espd_main', 
        __( 'Help &amp; FAQ &#10068;', 'easy-stripe-payments' ), 
        __( 'Help &amp; FAQ &#10068;', 'easy-stripe-payments' ), 
        'manage_options',
        'espd_main&tab=help',
        'espd_render_help_page' 
    );    
    
}

// Register for Frontend
function espd_register_scripts() {
    
    wp_register_script(
        'sweetalert',
        ESPAD_PLUGIN_URL . 'assets/js/sweetalert.js',
        array(),
        '1.0.0',
        false
    );
    
    wp_enqueue_script(
        'espd-frontend-enqueue',
        ESPAD_PLUGIN_URL . 'assets/js/espd-frontend-enqueue.js',
        array('sweetalert'), // SweetAlert dependency
        '1.0.21', 
        true // Load script in footer
    );      
              
}    
   
add_action('wp_enqueue_scripts', 'espd_register_scripts');

/**
 * Enqueue admin scripts for the Stripe Payments plugin admin pages.
 *
 * Loads jQuery (WordPress bundled) and the custom admin JavaScript files
 * only on the plugin's admin pages identified by 'espd_main' in the URL.
 *
 * @param string $hook The current admin page hook suffix.
 * @return void
 */
add_action('admin_enqueue_scripts', function($hook) {
           
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe to read GET for admin page detection only, no sensitive action performed.
    if ( isset($_GET['page']) && $_GET['page'] === 'espd_main' ) { 
 
        wp_enqueue_script(
            'espd-backend-enqueue',
            ESPAD_PLUGIN_URL . 'assets/js/espd-backend-enqueue.js',
            array('sweetalert'), // Dependencies
            '1.0.60',               
            true // Load script in footer
        );         
   
        wp_enqueue_script(
            'espd-backend-jquery-enqueue',
            ESPAD_PLUGIN_URL . 'assets/js/espd-backend-jquery-enqueue.js',
            array('datatables-js'), // Dependencies 
            '1.0.7',                 
            true // Load script in footer
        );         
      
        wp_enqueue_script(
            'espd-backend-chart',
            ESPAD_PLUGIN_URL . 'assets/js/espd-backend-chart.js',
            array('espd-chartjs'), // Dependencies 
            '1.0.5',                 
            true // Load script in footer
        );       

        // Enqueue ESPAD admin CSS
        wp_enqueue_style(
            'espad-admin-style',
            ESPAD_PLUGIN_URL . 'assets/css/espad.css',
            array(),
            '1.0.200'
        );        

        // Enqueue WordPress built-in jQuery script
        wp_enqueue_script('jquery');

        // Enqueue custom admin script with versioning, dependent on jQuery, loaded in footer
        wp_enqueue_script(
            'espd-admin-script',
            ESPAD_PLUGIN_URL . 'assets/js/admin-script.js',
            ['jquery'], 
            '1.0.78',
            true // Load script in footer
        );

        // Enqueue custom admin script with versioning, dependent on jQuery, loaded in footer
        wp_enqueue_script(
            'espd-admin', 
            ESPAD_PLUGIN_URL . 'assets/js/espd-admin.js',  
            ['jquery'], 
            '1.0.199',  
            true
        );         

        // SweetAlert JS
        wp_enqueue_script(
            'sweetalert',
            ESPAD_PLUGIN_URL . 'assets/js/sweetalert.js',
            array(),
            '1.0.0',
            false
        ); 

        // Pass dynamic data from PHP to JavaScript using wp_localize_script
        wp_localize_script('espd-admin', 'espd_ajax', [
            'ajax_url'                   => admin_url('admin-ajax.php'),
            'nonce'                      => wp_create_nonce('espd_form_nonce'),
            'recurringModalTitle'        => __('Stripe Subscription Product &amp; Button', 'easy-stripe-payments'),
            'standardCheckoutModalTitle' => __('Stripe Standard Checkout', 'easy-stripe-payments'),
            'campaignCheckoutModalTitle' => __('Stripe Campaign Checkout', 'easy-stripe-payments')
        ]);

        // Enqueue jQuery UI dialog styles and scripts for modal/dialog UI components
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-dialog');
        
    }
      
});

// Handles the AJAX request to save form data
add_action('wp_ajax_espd_save_form', function() {
  
    // Verify the AJAX nonce for security
    check_ajax_referer('espd_form_nonce', 'nonce'); 

    // Parse the serialized form data from the POST request
    $form = []; 
       
    // Check if data exists in POST
    if ( isset( $_POST['data'] ) ) {
 
        // Unslash and parse the serialized string safely
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Raw data required for parse_str(); sanitization applied after parsing.
        $raw_data = wp_unslash( $_POST['data'] );
 
        // Ensure the data is a string before parsing
        if ( is_string( $raw_data ) ) {
            parse_str( $raw_data, $form );
 
            // Recursively sanitize all fields
            $form = array_map( 'sanitize_text_field', $form );
        }
    }    
 
    global $wpdb;
    
    $table = $wpdb->prefix . 'espad_forms';

    // Set default fix_amount to '-' if empty
    $fix_amount = !empty($form['fix_amount']) ? sanitize_text_field($form['fix_amount']) : '-';
    $price_list = !empty($form['price_list']) ? sanitize_text_field($form['price_list']) : '';
    
    if ( $form['mode'] == 'Campaign') {
        // Sanitize campaign-related fields when mode is 'Campaign'
        $campaign_image          = sanitize_text_field($form['campaign_image']);
        $campaign_current_amount = sanitize_text_field($form['campaign_current_amount']);
        $campaign_goal_amount    = sanitize_text_field($form['campaign_goal_amount']);
    } else {
        // Clear campaign fields if mode is not 'Campaign'
        $campaign_image = $campaign_current_amount = $campaign_goal_amount = '';
    }
    
    // Check if form ID is set => update existing form
    if ( isset($form['form_id']) && !empty($form['form_id']) ) {

        $form_id = intval($form['form_id']);  
         
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally used for a custom table update with sanitized data.
        $result = $wpdb->update($table, [
            'form_name'                => sanitize_text_field($form['form_name']),
            'fix_amount'               => $fix_amount,
            'currency'                 => sanitize_text_field($form['currency']),
            'description'              => sanitize_textarea_field($form['description']),
            'success_url'              => esc_url_raw($form['success_url']),
            'cancel_url'               => esc_url_raw($form['cancel_url']),
            'stripe_metadata_campaign' => sanitize_text_field($form['stripe_metadata_campaign']),
            'stripe_metadata_project'  => sanitize_text_field($form['stripe_metadata_project']),
            'stripe_metadata_product'  => sanitize_text_field($form['stripe_metadata_product']),
            'created_at'               => current_time('mysql'),  
            'amount_type'              => sanitize_text_field($form['amount_type']),
            'price_list'               => $price_list,
            'payment_button'           => sanitize_text_field($form['espad_payment_button']),
            'mode'                     => sanitize_text_field($form['mode']),
            'campaign_image'           => $campaign_image,
            'campaign_current_amount'  => $campaign_current_amount,
            'campaign_goal_amount'     => $campaign_goal_amount,
            'color'                    => sanitize_text_field($form['color']),
            'choosed_fields'           => sanitize_text_field($form['show_fields']),
            'lang'                     => sanitize_text_field($form['form_language']),
            'payment_layout'           => sanitize_text_field($form['payment_layout']),
        ], ['id' => $form_id]);
        
        // Return success or error JSON response based on update result
        if ( $result !== false ) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Could not update form.');
        }
        
    } else {
        // Insert new form if no ID is provided
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally used for a custom table update with sanitized data.
        $result = $wpdb->insert($table, [
            'form_name'                => sanitize_text_field($form['form_name']),
            'fix_amount'               => sanitize_text_field($fix_amount),
            'currency'                 => sanitize_text_field($form['currency']),
            'description'              => sanitize_textarea_field($form['description']),
            'success_url'              => esc_url_raw($form['success_url']),
            'cancel_url'               => esc_url_raw($form['cancel_url']),
            'stripe_metadata_campaign' => sanitize_text_field($form['stripe_metadata_campaign']),
            'stripe_metadata_project'  => sanitize_text_field($form['stripe_metadata_project']),
            'stripe_metadata_product'  => sanitize_text_field($form['stripe_metadata_product']),
            'created_at'               => current_time('mysql'),
            'amount_type'              => sanitize_text_field($form['amount_type']),
            'price_list'               => $price_list,
            'payment_button'           => sanitize_text_field($form['espad_payment_button']),
            'mode'                     => sanitize_text_field($form['mode']),
            'campaign_image'           => $campaign_image,
            'campaign_current_amount'  => $campaign_current_amount,
            'campaign_goal_amount'     => $campaign_goal_amount,  
            'color'                    => sanitize_text_field($form['color']),
            'choosed_fields'           => sanitize_text_field($form['show_fields']),
            'lang'                     => sanitize_text_field($form['form_language']),
            'payment_layout'           => sanitize_text_field($form['payment_layout']),
        ]);

        // Return success or error JSON response based on insert result
        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Could not save form.');
        }
        
    }
    
}); 

/**
 * Handles AJAX requests to delete a payment record from the custom payments table.
 *
 * This function validates the AJAX nonce for security, sanitizes and validates
 * the received payment ID, and performs a secure database deletion using $wpdb->delete().
 * Returns a JSON response indicating success or failure.
 *
 */
add_action( 'wp_ajax_espd_delete_payment', 'espd_delete_payment_callback' );
 
function espd_delete_payment_callback() {
     
    // Verify the AJAX nonce to ensure request validity
    check_ajax_referer( 'espd_form_nonce', 'nonce' );

    global $wpdb;
    $table = $wpdb->prefix . 'espad_payments';

    // Validate and sanitize the payment ID
    if ( isset( $_POST['payment_id'] ) ) {
        
        $payment_id = absint( wp_unslash( $_POST['payment_id'] ) );

        if ( $payment_id > 0 ) {
            // Attempt to delete the payment entry from the database
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally used for a custom table delete with sanitized data.
            $deleted = $wpdb->delete( $table, array( 'id' => $payment_id ), array( '%d' ) );

            if ( $deleted ) {
                wp_send_json_success( array( 'message' => 'Payment deleted successfully.' ) );
            } else {
                wp_send_json_error( array( 'message' => 'Could not delete payment.' ) );
            }
        } else {
            wp_send_json_error( array( 'message' => 'Invalid payment ID.' ) );
        }
    } else {
        wp_send_json_error( array( 'message' => 'Missing payment ID.' ) );
    }

    wp_die(); // End AJAX callbacks
    
}
 
/**
 * Handles AJAX requests to delete a form record from the custom forms table.
 *
 * This function verifies the AJAX nonce for security, sanitizes and validates
 * the incoming form ID, and performs a secure deletion from the database using
 * $wpdb->delete(). Returns a JSON response indicating success or failure.
 *
 */
add_action( 'wp_ajax_espd_delete_form', 'espd_delete_form_callback' );
 
function espd_delete_form_callback() {
    
    // Verify the AJAX nonce to protect against unauthorized requests
    check_ajax_referer( 'espd_form_nonce', 'nonce' );

    global $wpdb;
    $table = $wpdb->prefix . 'espad_forms';

    // Validate and sanitize the form ID before using it
    if ( isset( $_POST['form_id'] ) ) {
        
        $form_id = absint( wp_unslash( $_POST['form_id'] ) );

        if ( $form_id > 0 ) {
            // Delete the form entry from the custom table
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally used for a custom table delete with sanitized data.
            $deleted = $wpdb->delete( $table, array( 'id' => $form_id ), array( '%d' ) );

            if ( $deleted ) {
                wp_send_json_success( array( 'message' => 'Form deleted successfully.' ) );
            } else {
                wp_send_json_error( array( 'message' => 'Could not delete form.' ) );
            }
        } else {
            wp_send_json_error( array( 'message' => 'Invalid form ID.' ) );
        }
    } else {
        wp_send_json_error( array( 'message' => 'Missing form ID.' ) );
    }

    wp_die(); // End AJAX callbacks
    
}

/**
 * AJAX callback to retrieve a payment form's data by ID.
 *
 * - Verifies the request using a nonce.
 * - Checks user capabilities.
 * - Fetches the form from the database.
 * - Returns form data as JSON on success or error message on failure.
 */
add_action('wp_ajax_espd_get_form_data', 'espd_get_form_data');

function espd_get_form_data() {
    
    // Verify nonce to prevent CSRF attacks
    check_ajax_referer('espd_form_nonce', 'nonce');

    // Check if the current user has permission to manage options
    if ( !current_user_can('manage_options') ) {
        wp_send_json_error('No permission');
    }
 
    global $wpdb;
    $table = $wpdb->prefix . 'espad_forms';

    // Sanitize form ID from GET parameter
    $form_id = intval( wp_unslash( $_GET['form_id'] ?? 0 ) );
     
    // Retrieve form data from the database
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Intentionally for custom table 
    $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $form_id));

    // If form found, send data as JSON
    if ( $form ) {
        wp_send_json_success([
            'id'                       => $form->id,
            'form_name'                => $form->form_name,
            'fix_amount'               => $form->fix_amount,
            'currency'                 => $form->currency,
            'description'              => $form->description,
            'success_url'              => $form->success_url,
            'cancel_url'               => $form->cancel_url,
            'amount_type'              => $form->amount_type,
            'price_list'               => $form->price_list,
            'stripe_metadata_campaign' => $form->stripe_metadata_campaign,
            'stripe_metadata_project'  => $form->stripe_metadata_project,
            'stripe_metadata_product'  => $form->stripe_metadata_product,
            'payment_button'           => $form->payment_button,
            'campaign_image'           => $form->campaign_image,
            'campaign_current_amount'  => $form->campaign_current_amount,
            'campaign_goal_amount'     => $form->campaign_goal_amount,
            'color'                    => $form->color,
            'choosed_fields'           => $form->choosed_fields,
            'lang'                     => $form->lang,
            'payment_layout'           => $form->payment_layout,
        ]);
    } else {
        wp_send_json_error('Error: Form not found');
    }
    
}

// Handles the AJAX request to update a recurring Stripe product
add_action('wp_ajax_espd_update_recurring_product', function() {

    // Verify nonce to protect against CSRF
    check_ajax_referer('espd_form_nonce', 'nonce'); 

    // Check user capability
    if ( !current_user_can('manage_options') ) {
        wp_send_json_error('No permission');
    }
 
    // Parse the form data
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Raw data required for parse_str(); sanitization applied after parsing.
    isset($_POST['data']) && (parse_str(wp_unslash($_POST['data']), $form) && $form = array_map('sanitize_text_field', $form));

    // Continue only if product ID is provided
    if ( isset($form['product_id']) && !empty($form['product_id']) ) {

        // Load the Stripe manager class if not already loaded
        class_exists( 'ESPAD\Stripe\StripeESPADManager' ) || espad_stripe_manager_init();                

        // Get values from form
        $recurring_product_id    = sanitize_text_field($form['product_id']);
        $new_product_name        = sanitize_text_field($form['choosed_product_name']);
        $new_product_description = sanitize_textarea_field($form['choosed_product_description']);
        $new_product_image       = esc_url_raw($form['choosed_product_images']);

        // Button styling options
        $button_title      = isset($form['button_title'])      ? sanitize_text_field($form['button_title'])      : 'Subscribe';
        $button_size       = isset($form['button_size'])       ? sanitize_text_field($form['button_size'])       : '';
        $button_color      = isset($form['button_color'])      ? sanitize_hex_color($form['button_color'])       : '';
        $button_font_color = isset($form['button_font_color']) ? sanitize_hex_color($form['button_font_color'])  : '';
        $button_language   = isset($form['form_language'])     ? sanitize_text_field($form['form_language'])     : 'en';

        // Update the Stripe product
        try {
            $product = \Stripe\Product::update(
                $recurring_product_id,
                [
                    'name'        => $new_product_name,
                    'description' => $new_product_description,
                    'images'      => [$new_product_image],
                ]
            );
        } catch ( Exception $e ) {
            wp_send_json_error('Stripe error: ' . $e->getMessage());
        }

        // If the product was updated, store button config and respond
        if ( $product ) {
            
            $option_name = 'espd_subscription_btn_id_' . $recurring_product_id;

            $button_settings = [
                'button_title'      => $button_title,
                'button_size'       => $button_size,
                'button_color'      => $button_color,
                'button_font_color' => $button_font_color,
                'button_language'   => $button_language
            ];

            update_option($option_name, json_encode($button_settings));               

            wp_send_json_success([
                'product_name'        => $product->name,
                'product_description' => $product->description,
                'product_images'      => $product->images,
            ]);
            
        } else {
            
            wp_send_json_error('Error: Product not updated');
            
        }
        
    } else {
        
        wp_send_json_error('Missing product ID');
        
    }
    
});

// Handles the AJAX request to delete a recurring Stripe product
add_action('wp_ajax_espd_delete_recurring_payment', function() {

    // Verify nonce for security
    check_ajax_referer('espd_form_nonce', 'nonce');

    // Only allow admins or users with manage_options capability
    if ( !current_user_can('manage_options') ) {
        wp_send_json_error('No permission');
    }

    // Get and sanitize the product ID
    $product_id = isset($_POST['product_id']) ? sanitize_text_field( wp_unslash($_POST['product_id']) ) : '';

    if ( $product_id ) {
        
        $option_name = 'espd_subscription_btn_id_' . $product_id;

        // Delete the option
        if ( delete_option($option_name) ) {
            
            // Load the Stripe manager class if not already loaded
            class_exists( 'ESPAD\Stripe\StripeESPADManager' ) || espad_stripe_manager_init(); 

            try {
                
                // Deactivate the product by setting it inactive on Stripe
                $product = \Stripe\Product::update($product_id, [
                    'active' => false,
                ]);

                // If deactivation was successful
                if ( $product && !$product->active ) {
                    
                    wp_send_json_success('Product has been deactivated');
                    
                } else {
                    
                    wp_send_json_error('Unable to deactivate the product');
                    
                }

            } catch (\Exception $e) {
                
                // Return Stripe error if something goes wrong
                wp_send_json_error('Stripe error: ' . $e->getMessage());
                
            }            
            
        } else {
            
            wp_send_json_error("Option '$option_name' could not be deleted or did not exist.");
            
        }        

    } else {
        
        wp_send_json_error('Missing product ID');
        
    }
});

// Handles the AJAX request to get a recurring Stripe product
add_action('wp_ajax_espd_get_product_data', 'espd_get_product_data');

function espd_get_product_data() {

    // Verify nonce for security
    check_ajax_referer('espd_form_nonce', 'nonce');

    // Only allow users with admin privileges
    if ( !current_user_can('manage_options') ) {
        wp_send_json_error('No permission');
    }

    // Load the Stripe manager class if not already loaded
    class_exists( 'ESPAD\Stripe\StripeESPADManager' ) || espad_stripe_manager_init(); 

    // Get and sanitize the product ID from the request
    $product_id = isset($_GET['product_id']) ? sanitize_text_field( wp_unslash($_GET['product_id']) ) : '';

    // Retrieve product data from Stripe
    try {
        
        $product = \Stripe\Product::retrieve($product_id);
        
    } catch (\Exception $e) {
        
        wp_send_json_error('Stripe error: ' . $e->getMessage());
        
    }

    if ( $product ) {

        // Load custom button settings from WordPress options
        $option_name = 'espd_subscription_btn_id_' . $product_id;
        $button_data_raw = get_option($option_name);
        $button_data = $button_data_raw ? json_decode($button_data_raw, true) : [];

        // Return product and button settings data
        wp_send_json_success([
            'product_name'        => $product->name,
            'product_description' => $product->description,
            'product_images'      => $product->images,
            'button_settings'     => $button_data
        ]);

    } else {
        
        wp_send_json_error('Product not found');
        
    }    
    
}

// Loads the welcome page for the admin interface.
function espd_render_welcome_page() {
    
    require_once ESPAD_PLUGIN_PATH . 'admin/welcome.php';

}

// Loads the settings page for the admin interface.
function espd_render_settings_page() {
    
    require_once ESPAD_PLUGIN_PATH . 'admin/settings.php';

}

// Loads the payments page for the admin interface.
function espd_render_payments_page() {
    
    require_once ESPAD_PLUGIN_PATH . 'admin/payments.php';

}

// Loads the premium features page for the admin interface.
function espd_render_premium_page() {
    
    require_once ESPAD_PLUGIN_PATH . 'admin/premium.php';

}

// Loads the help/documentation page for the admin interface.
function espd_render_help_page() {
    
    require_once ESPAD_PLUGIN_PATH . 'admin/help.php';

}

/**
 * Renders the specified admin subpage if it is within the list of allowed pages.
 *
 * Includes the corresponding PHP file from the admin directory.
 *
 * @param string $page The slug of the admin page to render.
 */
function espd_render_admin_page() {
      
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tabs are only for display, no sensitive action.
    $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash($_GET['tab']) ) : 'welcome';
    
    require_once ESPAD_PLUGIN_PATH . 'admin/sections/nav-tabs.php';
    
}
 
add_action('admin_footer', function () {
    
    // Get the current tab from the URL, defaulting to 'welcome' if not set
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tabs are only for display, no sensitive action.
    $current_tab = isset($_GET['tab']) ? sanitize_text_field( wp_unslash($_GET['tab'])) : 'welcome';
    
    // Enqueue custom admin footer script 
    wp_enqueue_script(
        'espad-admin-footer', 
        ESPAD_PLUGIN_URL . 'assets/js/admin-footer.js',  
        ['jquery'], 
        '1.0.11',  
        true
    ); 
      
    // Get powered-by.php 
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Pages are only for display, no sensitive action.
    if ( isset($_GET['page']) && $_GET['page'] === 'espd_main' ) {
         
        require_once ESPAD_PLUGIN_PATH . 'admin/sections/powered-by.php';
        
    }    
    
});

/**
 * Conditionally enqueues the SweetAlert script on the front page 
 * if the URL contains the parameter ?espad_stripe_status=success 
 * and the [espad_payment_form] shortcode is NOT present in the post content.
 *
 * This prevents loading the script twice if it's already loaded by the shortcode.
 */
add_action('wp_enqueue_scripts', 'espd_maybe_enqueue_sweetalert');

function espd_maybe_enqueue_sweetalert() {
    
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe to read GET for UI purposes only, no sensitive action or data modification occurs.
    $status = isset($_GET['espad_stripe_status']) ? sanitize_text_field( wp_unslash($_GET['espad_stripe_status']) ) : '';
    
    if ( is_front_page() && ( $status === 'success' || $status === 'failed' ) ) {    
        
        global $post;
        
        if ( isset($post->post_content) && !has_shortcode($post->post_content, 'espad_payment_form') ) {
            
            wp_enqueue_script('sweetalert');
            
        }
    }
    
}
 
// Hook into the WordPress footer to display a success message 
// if the custom URL parameter 'espad_stripe_status=success' is present.
// This is typically used after a successful Stripe subscription redirect.
add_action( 'wp_footer', 'espad_show_stripe_success_message' );

function espad_show_stripe_success_message() {
    
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe to read GET for UI purposes only, no sensitive action or data modification occurs.
    $status = isset($_GET['espad_stripe_status']) ? sanitize_text_field( wp_unslash($_GET['espad_stripe_status']) ) : '';
    
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe to read GET for UI purposes only, no sensitive action or data modification occurs.
    if ( is_front_page() && empty( $_GET['payment_intent'] ) ) {

        if ( $status === 'success' ) {
            
            require_once ESPAD_PLUGIN_PATH . 'frontend/sections/recurring-payment-success.php';
            
        } elseif ( $status === 'failed' ) {
            
            require_once ESPAD_PLUGIN_PATH . 'frontend/sections/recurring-payment-failed.php';
            
        }
  
    }
    
}

// Add custom action links (Settings and Setup) to the plugin row on the Plugins page
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'espd_plugin_action_links' );

function espd_plugin_action_links( $links ) {
     
    $settings_link = '<a href="admin.php?page=espd_main&tab=settings">Settings</a>';
    $setup_link    = '<a href="admin.php?page=espd_main&tab=setup">Setup</a>';    
 
    array_unshift( $links, $setup_link, $settings_link );
    
    return $links;
    
}

/**
 * Register a REST API endpoint for creating a Stripe Checkout session.
 * 
 * Note: This endpoint is intended to be used by the frontend, where the
 * Stripe Checkout form is embedded and visible to all users. Therefore,
 * the permission_callback is set to __return_true to allow public access.
 * 
 * POST requests should be used to securely pass the necessary payment data.
 *
 * Usage:
 * - Frontend code can send a POST request with necessary parameters (amount,
 *   product, currency, etc.) and create the Stripe paymentIntent to initiate Stripe Checkout. 
 */ 
add_action('rest_api_init', function() {
    
    register_rest_route('espad-stripe/v1', '/create/', [
        'methods' => 'POST',
        'callback' => 'espad_create_checkout',
        'permission_callback' => '__return_true', 
    ]);
    
});
  
function espad_create_checkout(WP_REST_Request $request) {
     
    // Load StripeESPADManager if not already loaded
    if ( !class_exists('\ESPAD\Stripe\StripeESPADManager') ) {
        require_once  ESPAD_PLUGIN_PATH . 'inc/StripeESPADManager.php';
    }
   
    // Get singleton instance and retrieve Stripe client
    $stripe = \ESPAD\Stripe\StripeESPADManager::get_instance()->get_stripe_client();    
    
    $metadata = [];
    
    $metadata['campaign']         = get_option('espad_stripe_metadata_campaign') ?? null;
    $metadata['project']          = get_option('espad_stripe_metadata_project') ?? null;
    $metadata['product']          = get_option('espad_stripe_metadata_product') ?? null;
    $metadata['checkout_form_id'] = get_option('espad_checkout_form_id') ?? null;
     
    $currency = get_option('espad_currency');

    // "0", null, or an empty string is treated as invalid.
    if ( empty($currency) || $currency === '0' ) {
        $currency = 'USD';
    }  
      
    function calculateEspadAmount(array $items): int {

        $total = 0;

        foreach($items as $item) {

          $total += $item->amount;

        }

        return $total;

    }       
         
    try { 
          
        // 1. Retrieve JSON from POST body
        $jsonStr = file_get_contents( 'php://input' );

        // 2. Validate presence of data
        if ( empty( $jsonStr ) ) {
            wp_die( 'Error: No data received.' );
        }

        // 3. Decode JSON safely
        $jsonObj = json_decode( $jsonStr );

        // 4. Check for JSON errors
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            wp_die( esc_html( 'Error: Invalid JSON format: ' . json_last_error_msg() ) );
        }

        // 5. Validate items array
        if ( ! isset( $jsonObj->items ) || ! is_array( $jsonObj->items ) ) {
            wp_die( 'Error: Missing or invalid "items" array in JSON data.' );
        }

        if ( empty( $jsonObj->items ) ) {
            wp_die( 'Error: The "items" array is empty.' );
        }

        // 6. Sanitize decoded data
        foreach ( $jsonObj->items as &$item ) {
            if ( is_array( $item ) ) {
                $item = array_map( 'sanitize_text_field', $item );
            } elseif ( is_object( $item ) ) {
                foreach ( $item as $key => $value ) {
                    $item->$key = sanitize_text_field( $value );
                }
            } else {
                $item = sanitize_text_field( $item );
            }
        }
        unset( $item );

        // 7. Sanitize and validate currency
        if ( isset( $currency ) ) {
            $currency = strtoupper( sanitize_text_field( $currency ) );

            // Optional: ISO 4217 format check
            if ( ! preg_match( '/^[A-Z]{3}$/', $currency ) ) {
                wp_die( 'Error: Invalid currency code.' );
            }
        } else {
            wp_die( 'Error: Missing currency field.' );
        }

        // 8. Prepare parameters for the payment gateway
        $params = [
            'amount' => absint( calculateEspadAmount( $jsonObj->items ) ),
            'currency' => $currency,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ];        
               
        // 9. Add metadata only if it contains something.
        if ( !empty($metadata) ) {
            $params['metadata'] = $metadata;
        }
  
        // 10. Create Stripe PaymentIntent
        $paymentIntent = $stripe->paymentIntents->create($params);    

        $output = [
            'clientSecret' => $paymentIntent->client_secret,
            // [DEV]: For demo purposes only, you should avoid exposing the PaymentIntent ID in the client-side code.
            //'dpmCheckerLink' => "https://dashboard.stripe.com/settings/payment_methods/review?transaction_id={$paymentIntent->id}",
        ];
  
        return rest_ensure_response($output);

    } catch (Error $e) {

        http_response_code(500);

        echo json_encode(['error' => $e->getMessage()]);

    } 
     
}
