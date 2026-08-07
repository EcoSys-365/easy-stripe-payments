<?php
/**
 * Plugin Name: Easy Stripe Payments
 * Description: A user-friendly WordPress plugin for accepting <strong>one-time and recurring Stripe payments</strong>. Perfect for businesses, freelancers and Non-Profit organizations. Secure, fast and fully PCI-compliant.
 * Version: 1.4.0
 * Author: EcoSys365
 * Author URI: https://www.ecosys365.com
 * Plugin URI: https://www.payments-and-donations.com
 * Text Domain: easy-stripe-payments
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html 
 * Requires at least: 5.5
 * Tested up to: 7.0
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
// Define DB version
define( 'ESPAD_DB_VERSION', '1.3.3' );
 
// Include the plugin's helper functions.
require_once ESPAD_PLUGIN_PATH . 'inc/functions.php';

/*
 * Handle Stripe payment return requests after the customer
 * is redirected back from Stripe.
 */
require_once ESPAD_PLUGIN_PATH . 'inc/handle-payment-return.php'; 
 
// Hook into the 'init' action to run custom initialization logic early.
add_action( 'init', function () {
    
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
    wp_enqueue_style('espd-bootstrap-scoped', ESPAD_PLUGIN_URL . 'assets/css/bootstrap-scoped.css', [], '1.0.4');
    wp_enqueue_style('espd-checkout-css', ESPAD_PLUGIN_URL . 'inc/stripeCheckout/checkout.css', [], '1.0.10');

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
        '1.0.242',
        true // Load in footer
    );
           
}

/**
 * Enqueues Stripe.js without loading the legacy checkout.js file.
 *
 * Used by the dynamic React-based Multi-Step Checkout.
 */
function espad_enqueue_stripe_base_assets() {

    wp_enqueue_script(
        'stripe-js',
        'https://js.stripe.com/v3/',
        array(),
        '3',
        true
    );

}

/**
 * Enqueues all assets required by an existing ESPAD Payment Form.
 */
function espad_enqueue_existing_checkout_assets() {

    wp_enqueue_style(
        'espd-bootstrap-scoped',
        ESPAD_PLUGIN_URL . 'assets/css/bootstrap-scoped.css',
        array(),
        '1.0.4'
    );

    wp_enqueue_style(
        'espd-checkout-css',
        ESPAD_PLUGIN_URL . 'inc/stripeCheckout/checkout.css',
        array(),
        '1.0.10'
    );

    wp_enqueue_script(
        'stripe-js',
        'https://js.stripe.com/v3/',
        array(),
        '3',
        true
    );

    wp_enqueue_script(
        'espd-checkout-js',
        ESPAD_PLUGIN_URL . 'inc/stripeCheckout/checkout.js',
        array('stripe-js'),
        '1.0.242',
        true
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
        '1.2.46'  
    ); 
       
    // Enqueue built-in jQuery
    wp_enqueue_script('jquery');

    // Enqueue frontend payment form JS with versioning and footer placement
    wp_enqueue_script(
        'espd-frontend-script',
        ESPAD_PLUGIN_URL . 'assets/js/frontend-payment-form.js',
        array('jquery'),
        '1.0.15',
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
    if (
        isset( $_GET['page'], $_GET['tab'] ) &&
        sanitize_key( wp_unslash( $_GET['page'] ) ) === 'espd_main' &&
        sanitize_key( wp_unslash( $_GET['tab'] ) ) === 'preview'
    ) {
        global $wpdb; 

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $preview_form_id = isset( $_GET['form_id'] )
            ? absint( $_GET['form_id'] )
            : 0;

        $preview_form_mode = '';

        if ( $preview_form_id ) {
            $table = $wpdb->prefix . 'espad_forms';

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $preview_form_mode = (string) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT mode
                     FROM {$table}
                     WHERE id = %d
                     LIMIT 1",
                    $preview_form_id
                )
            );
        }

        if ( $preview_form_mode !== 'Multistep' ) {
            add_action(
                'admin_head',
                'espd_preview_add_scripts',
                99
            );

            add_action(
                'admin_head',
                'espd_add_payment_shortcode_scripts',
                99
            );
        }
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
       
    // Do not render the payment form inside excerpts, archive, search & singular.
    if ( doing_filter('get_the_excerpt') || doing_filter('the_excerpt') || is_archive() || is_search() || ! is_singular() ) {
        return '';
    }    
    
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
 * Creates or updates the required database tables using dbDelta,
 * inserts demo content, and initializes default plugin options.
 * Also stores the current database version for future upgrades.
 *
 * @return void
 */
function espd_plugin_activate() {
    
    espd_plugin_create_db_tables();
    
    global $wpdb;

    // Define table names with WordPress prefix.
    $table_forms    = $wpdb->prefix . 'espad_forms';
    $table_payments = $wpdb->prefix . 'espad_payments';

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
    
    update_option( 'espd_plugin_db_version', ESPAD_DB_VERSION );
    
}

/**
 * Creates or updates the plugin's custom database tables.
 *
 * Uses WordPress dbDelta to create the required tables or modify them
 * if the structure has changed (e.g. new database columns added).
 *
 * @return void
 */
function espd_plugin_create_db_tables() {

    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    // Tabellen-Namen
    $table_forms    = $wpdb->prefix . 'espad_forms';
    $table_payments = $wpdb->prefix . 'espad_payments';

    // SQL
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
        checkout_metadata_1 TEXT,
        checkout_metadata_2 TEXT,
        checkout_metadata_3 TEXT,
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

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
    dbDelta( $sql );
    
}
 
/**
 * Checks whether the database schema needs to be updated.
 *
 * Compares the stored database version with the current plugin DB version.
 * If the stored version is older, the database tables are updated using dbDelta
 * and the version is updated accordingly.
 *
 * @return void
 */
function espd_plugin_maybe_update_db() {

    // Get the stored DB version or fallback to default (1.0.0)
    $installed_version = get_option( 'espd_plugin_db_version', '1.0.0' );
 
    if ( version_compare( $installed_version, ESPAD_DB_VERSION, '<' ) ) {
        
        espd_plugin_create_db_tables(); // execute dbDelta
        
        update_option( 'espd_plugin_db_version', ESPAD_DB_VERSION );
    
    }

}

add_action( 'plugins_loaded', 'espd_plugin_maybe_update_db' );

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
        'espd_render_admin_page'
    ); 
    
    // Add submenu page for Payments tab
    add_submenu_page(
        'espd_main', 
        __( 'Payment Forms 💳', 'easy-stripe-payments' ), 
        __( 'Payment Forms 💳', 'easy-stripe-payments' ), 
        'manage_options',
        'espd_main&tab=forms',  
        'espd_render_admin_page'
    );     
    
    // Add submenu page for Checkout Builder tab
    add_submenu_page(
        'espd_main',
        __( 'Checkout Builder &#129513;', 'easy-stripe-payments' ),
        __( 'Checkout Builder &#129513;', 'easy-stripe-payments' ),
        'manage_options',
        'espd_main&tab=checkout-builder-overview',
        'espd_render_admin_page'
    );     
    
    // Add submenu page for Settings tab
    add_submenu_page(
        'espd_main', 
        __( 'Settings &#128295;', 'easy-stripe-payments' ), 
        __( 'Settings &#128295;', 'easy-stripe-payments' ), 
        'manage_options',
        'espd_main&tab=settings', 
        'espd_render_admin_page' 
    ); 
    
    // Add submenu page for Premium tab
    add_submenu_page(
        'espd_main', 
        __( 'Premium &#9733;', 'easy-stripe-payments' ), 
        __( 'Premium &#9733;', 'easy-stripe-payments' ), 
        'manage_options',
        'espd_main&tab=premium', 
        'espd_render_admin_page' 
    );     
    
    // Add submenu page for Help & FAQ tab
    add_submenu_page(
        'espd_main', 
        __( 'Help &amp; FAQ &#10068;', 'easy-stripe-payments' ), 
        __( 'Help &amp; FAQ &#10068;', 'easy-stripe-payments' ), 
        'manage_options',
        'espd_main&tab=help',
        'espd_render_admin_page' 
    );    
    
}

/**
 * Set the active submenu item based on the current plugin tab.
 *
 * @param string $submenu_file Current submenu slug.
 * @return string Active submenu slug.
 */
add_filter( 'submenu_file', 'espd_set_active_submenu' );

function espd_set_active_submenu( $submenu_file ) {

    if ( empty( $_GET['page'] ) || $_GET['page'] !== 'espd_main' ) {
        return $submenu_file;
    }
 
    $tab = sanitize_key( wp_unslash( $_GET['tab'] ?? 'welcome' ) );    

    switch ( $tab ) {

        case 'payments':
            return 'espd_main&tab=payments';

        case 'forms':
            return 'espd_main&tab=forms';
            
        case 'checkout-builder-overview':
        case 'checkout-builder':
            return 'espd_main&tab=checkout-builder-overview';            

        case 'settings':
            return 'espd_main&tab=settings';

        case 'premium':
            return 'espd_main&tab=premium';

        case 'help':
            return 'espd_main&tab=help';

        case 'welcome':
            return 'espd_main'; 
            
        default:
            return '';
            
    }
    
}

/**
 * Ensure the plugin's top-level admin menu remains highlighted.
 *
 * @param string $parent_file Current parent menu slug.
 * @return string Parent menu slug.
 */
add_filter( 'parent_file', 'espd_set_parent_menu' );

function espd_set_parent_menu( $parent_file ) {

    if (
        isset( $_GET['page'] ) &&
        $_GET['page'] === 'espd_main'
    ) {
        return 'espd_main';
    }

    return $parent_file;
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
        '1.0.23',  
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

        // Load the React-based Multi-Step Checkout Builder assets only on the dedicated admin tab
        if ( isset($_GET['tab']) && $_GET['tab'] === 'checkout-builder' ) {

            $asset_file = ESPAD_PLUGIN_PATH . 'build/index.asset.php';
            $js_file    = ESPAD_PLUGIN_PATH . 'build/index.js';
            $css_file   = ESPAD_PLUGIN_PATH . 'build/style-index.css';
  
            if (
                file_exists( $asset_file ) &&
                file_exists( $js_file )
            ) {
                $asset = include $asset_file;

                /*
                 * Use the physical file modification time as the version.
                 * This automatically changes after every successful build
                 * and prevents the browser from loading an outdated bundle.
                 */
                $js_version = filemtime( $js_file );

                wp_enqueue_script(
                    'espad-checkout-builder',
                    ESPAD_PLUGIN_URL . 'build/index.js',
                    isset( $asset['dependencies'] )
                        ? $asset['dependencies']
                        : array(),
                    $js_version,
                    true
                );
                
                // Enqueue WordPress's built-in media uploader
                wp_enqueue_media();

                /*
                 * Load builder configuration and existing checkout data
                 * for the React-based Multi-Step Checkout Builder.
                 *
                 * Provides:
                 * - Available builder languages and currencies
                 * - Builder mode (new or edit)
                 * - Checkout ID for edit operations
                 * - Existing checkout flow data when editing
                 * - REST API endpoint for saving checkout flows
                 * - WordPress REST API nonce for authenticated requests
                 * - Premium membership status for feature availability
                 *
                 * The data is exposed through the global
                 * window.espadBuilderData object before the React app loads.
                */
                $languages  = require ESPAD_PLUGIN_PATH . 'inc/data/languages.php';
                $currencies = require ESPAD_PLUGIN_PATH . 'inc/data/currencies.php';
                
                $builder_action = isset( $_GET['builder_action'] )
                    ? sanitize_key( wp_unslash( $_GET['builder_action'] ) )
                    : 'new';

                $checkout_id = isset( $_GET['checkout_id'] )
                    ? absint( $_GET['checkout_id'] )
                    : 0;

                $existing_flow   = null;
                $checkout_exists = false;
                
                global $wpdb; 

                if ( $builder_action === 'edit' && $checkout_id > 0 ) {

                    $table = $wpdb->prefix . 'espad_forms';

                    $row = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT checkout_metadata_1
                             FROM {$table}
                             WHERE id = %d
                             AND mode = %s
                             LIMIT 1",
                            $checkout_id,
                            'Multistep'
                        )
                    ); 

                    if ( $row && ! empty( $row->checkout_metadata_1 ) ) {
                        $decoded_flow = json_decode( $row->checkout_metadata_1, true );

                        if ( is_array( $decoded_flow ) ) {
                            $existing_flow = $decoded_flow;
                            $checkout_exists = true;
                        }
                    }
                }
                
                $payment_forms = $wpdb->get_results(
                    "SELECT id, form_name, mode
                     FROM {$wpdb->prefix}espad_forms
                     WHERE mode <> 'Multistep'
                     ORDER BY created_at DESC",
                    ARRAY_A
                );                
                 
                wp_add_inline_script(
                    'espad-checkout-builder',
                    'window.espadBuilderData = ' . wp_json_encode([
                        'restUrl'   => esc_url_raw(
                            rest_url('espad-stripe/v1/save-multistep-checkout')
                        ),
                        'nonce'            => wp_create_nonce('wp_rest'),
                        'languages'        => $languages,
                        'currencies'       => $currencies,
                        'builderAction'    => $builder_action,
                        'checkoutId'       => $checkout_id,
                        'existingFlow'     => $existing_flow, 
                        'checkoutExists'   => $checkout_exists,
                        'paymentForms'     => $payment_forms,
                        'membershipStatus' => get_current_membership_status(),
                        'isPremium'        => get_current_membership_status() === '1',                        
                    ]) . ';',
                    'before'
                );                
                
                wp_enqueue_style(
                    'espad-checkout-builder',
                    ESPAD_PLUGIN_URL . 'build/style-index.css',
                    array(),
                    filemtime( $css_file )
                );      
                
            } 
            
        }
        
        // SweetAlert JS
        wp_enqueue_script(
            'sweetalert',
            ESPAD_PLUGIN_URL . 'assets/js/sweetalert.js',
            array(),
            '1.0.1',
            false
        );         
 
        wp_enqueue_script(
            'espd-backend-enqueue',
            ESPAD_PLUGIN_URL . 'assets/js/espd-backend-enqueue.js',
            array('sweetalert'), // Dependencies
            '1.0.79',                
            true // Load script in footer
        );         
         
        // DataTables register/enqueue first
        wp_enqueue_script(
            'datatables-js',
            ESPAD_PLUGIN_URL . 'assets/js/jquery.dataTables.min.js',
            array('jquery'),
            '1.13.11',
            true
        );

        // Script with Dependency
        wp_enqueue_script(
            'espd-backend-jquery-enqueue',
            ESPAD_PLUGIN_URL . 'assets/js/espd-backend-jquery-enqueue.js',
            array('datatables-js'),
            '1.0.9',
            true // Load script in footer
        );               

        // Enqueue ESPAD admin CSS
        wp_enqueue_style(
            'espad-admin-style',
            ESPAD_PLUGIN_URL . 'assets/css/espad.css',
            array(),
            '1.0.325'
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
            '1.0.238',  
            true
        );           

        // Pass dynamic data from PHP to JavaScript using wp_localize_script
        wp_localize_script('espd-admin', 'espd_ajax', [
            'ajax_url'                       => admin_url('admin-ajax.php'),
            'nonce'                          => wp_create_nonce('espd_form_nonce'),
            'recurringModalTitle'            => __('Stripe Subscription Product &amp; Button', 'easy-stripe-payments'),
            'standardCheckoutModalTitle'     => __('Stripe Standard Checkout', 'easy-stripe-payments'),
            'campaignCheckoutModalTitle'     => __('Stripe Campaign Checkout', 'easy-stripe-payments'),
            'subscriptionCheckoutModalTitle' => __('Stripe Subscription Checkout', 'easy-stripe-payments'),
            'advancedCheckoutModalTitle'     => __('Stripe Advanced Checkout', 'easy-stripe-payments')
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
        ]);

        // Return success or error JSON response based on insert result
        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Could not save form.');
        }
        
    }
    
}); 
 
// Handles the AJAX request to save form data for Subscription Checkout
add_action('wp_ajax_espd_save_new_subscription_form', function() {
  
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

    $subscription_price_id = sanitize_text_field($form['subscription_price_id']);

    // Check if Stripe Connect is active
    $stripe_access_token = \get_option( 'espad_stripe_connect_access_token', '' );

    // Stripe Connect with Fee
    if ( ! empty( $stripe_access_token ) ) {
        
        if ( !class_exists('\ESPAD\Stripe\StripeESPADManager') ) {
            require_once ESPAD_PLUGIN_PATH . 'inc/StripeESPADManager.php';
        }
         
        $stripe = \ESPAD\Stripe\StripeESPADManager::get_instance()->get_stripe_client();    
         
        // Retrieve price from Stripe
        $price = $stripe->prices->retrieve($subscription_price_id);

        // Amount (Cent)
        $amount = $price->unit_amount;
        
        $amount_formatted = $amount / 100;        
  
        // Currency
        $subscription_currency = $price->currency;    

        $checkout_metadata_1 = json_encode([
            'subscription_checkout_price_id' => $subscription_price_id,
            'subscription_checkout_amount'   => $amount_formatted,
            'subscription_checkout_currency' => $subscription_currency,
        ]);           
         
    } else {
        
        wp_send_json_error('Please use Stripe Connect when using Subscription Checkout.');    
         
    }
    
    // Check if form ID is set => update existing form
    if ( isset($form['form_id']) && !empty($form['form_id']) ) {

        $form_id = intval($form['form_id']);  
         
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally used for a custom table update with sanitized data.
        $result = $wpdb->update($table, [
            'form_name'                => sanitize_text_field($form['form_name']),
            'checkout_metadata_1'      => $checkout_metadata_1,
            'currency'                 => sanitize_text_field($form['currency']),
            'description'              => sanitize_textarea_field($form['description']),
            'success_url'              => esc_url_raw($form['success_url']),
            'cancel_url'               => esc_url_raw($form['cancel_url']),
            'stripe_metadata_campaign' => sanitize_text_field($form['stripe_metadata_campaign']),
            'stripe_metadata_project'  => sanitize_text_field($form['stripe_metadata_project']),
            'stripe_metadata_product'  => sanitize_text_field($form['stripe_metadata_product']),
            'created_at'               => current_time('mysql'),  
            'payment_button'           => sanitize_text_field($form['espad_payment_button']),
            'mode'                     => sanitize_text_field($form['mode']),
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
            'checkout_metadata_1'      => $checkout_metadata_1,
            'currency'                 => sanitize_text_field($form['currency']),
            'description'              => sanitize_textarea_field($form['description']),
            'success_url'              => esc_url_raw($form['success_url']),
            'cancel_url'               => esc_url_raw($form['cancel_url']),
            'stripe_metadata_campaign' => sanitize_text_field($form['stripe_metadata_campaign']),
            'stripe_metadata_project'  => sanitize_text_field($form['stripe_metadata_project']),
            'stripe_metadata_product'  => sanitize_text_field($form['stripe_metadata_product']),
            'created_at'               => current_time('mysql'),
            'payment_button'           => sanitize_text_field($form['espad_payment_button']),
            'mode'                     => sanitize_text_field($form['mode']), 
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
 
// Handles the AJAX request to save form data for Advanced Checkout
add_action('wp_ajax_espd_save_new_advanced_form', function() {
  
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

    $subscription_price_id = sanitize_text_field($form['subscription_price_id']);

    // Check if Stripe Connect is active
    $stripe_access_token = \get_option( 'espad_stripe_connect_access_token', '' );

    // Stripe Connect with Fee
    if ( ! empty( $stripe_access_token ) ) {
        
        if ( !class_exists('\ESPAD\Stripe\StripeESPADManager') ) {
            require_once ESPAD_PLUGIN_PATH . 'inc/StripeESPADManager.php';
        }
         
        $stripe = \ESPAD\Stripe\StripeESPADManager::get_instance()->get_stripe_client();    
         
        // Retrieve price from Stripe
        $price = $stripe->prices->retrieve($subscription_price_id);

        // Amount (Cent)
        $amount = $price->unit_amount;
        
        $amount_formatted = $amount / 100;        
  
        // Currency
        $subscription_currency = $price->currency;    

        $checkout_metadata_1 = json_encode([
            'subscription_checkout_price_id'          => $subscription_price_id,
            'subscription_checkout_amount'            => $amount_formatted,
            'subscription_checkout_currency'          => $subscription_currency,
            'subscription_button_label'               => $form['subscription_button_label'],
            'advanced_subscription_payment_button'    => $form['espad_advanced_subscription_payment_button'],
            'advanced_checkout_one_time_button_label' => $form['one_time_button_label'],
        ]);           
         
    } else {
        
        wp_send_json_error('Please use Stripe Connect when using Advanced Checkout.');    
         
    }
    
    // Check if form ID is set => update existing form
    if ( isset($form['form_id']) && !empty($form['form_id']) ) {

        $form_id = intval($form['form_id']);  
         
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentionally used for a custom table update with sanitized data.
        $result = $wpdb->update($table, [
            'form_name'                => sanitize_text_field($form['form_name']),
            'fix_amount'               => $fix_amount,
            'checkout_metadata_1'      => $checkout_metadata_1,
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
            'fix_amount'               => $fix_amount,
            'checkout_metadata_1'      => $checkout_metadata_1,
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
        
        $checkout_metadata_1 = json_decode( $form->checkout_metadata_1 ?? '', true );
        
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
            'checkout_metadata_1'      => $checkout_metadata_1,
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
        '1.0.13',  
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

// Add custom action link (Settings) to the plugin row on the Plugins page
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'espd_plugin_action_links' );

function espd_plugin_action_links( $links ) {
     
    $settings_link = '<a href="admin.php?page=espd_main&tab=settings">Settings</a>';    
 
    array_unshift( $links, $settings_link );
    
    return $links;
    
}

/**
 * Create Stripe PaymentIntent for a given checkout form via REST API.
 *
 * This endpoint processes incoming checkout requests, retrieves form-specific
 * configuration (such as currency and metadata) from the database, and prepares
 * a Stripe PaymentIntent.
 *
 * Metadata is dynamically generated based on stored form settings. If no
 * metadata is defined, the form ID is used as a fallback.
 *
 * Currency is loaded per form, validated, and falls back to a default value
 * if missing or invalid.
 *
 * All incoming request data is validated and sanitized before processing.
 *
 * @param \WP_REST_Request $request The REST API request object containing the form ID and JSON payload.
 *
 * @return \WP_REST_Response|array Returns a REST response containing the Stripe client secret on success.
 *                                 Returns an error response on failure.
 */
add_action('rest_api_init', function() {
    
    register_rest_route('espad-stripe/v1', '/create/(?P<form_id>\d+)', [
        'methods' => 'POST',
        'callback' => 'espad_create_checkout',
        'permission_callback' => '__return_true', 
    ]);
    
});

/**
 * Sum all item amounts.
 */
function calculateEspadAmount(array $items): int {
    
    $total = 0;

    foreach ( $items as $item ) {
        $total += isset($item->amount) ? (int) $item->amount : 0;
    }

    return $total;

}

/** 
 * Calculate platform fee in smallest currency unit.
 * Default: 1.8%
 */
function espad_calculate_platform_fee(int $amount, float $percent = 1.8): int {
    
    if ( $amount <= 0 ) {
        return 0;
    }

    $fee = (int) round($amount * ($percent / 100));

    if ( $fee >= $amount ) {
        $fee = max(0, $amount - 1);
    }

    return $fee;
    
}
  
function espad_create_checkout(WP_REST_Request $request) {  
    
    global $wpdb;

    $form_id = absint( $request->get_url_params()['form_id'] ?? 0 );

    $table_name = $wpdb->prefix . 'espad_forms';

    /**
     * Retrieves optional metadata values (campaign, project, product) from the
     * espad_forms table based on the given form ID. Only non-empty values are
     * included in the final metadata array.
     */    
    $form_data = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT stripe_metadata_campaign, stripe_metadata_project, stripe_metadata_product
             FROM $table_name
             WHERE id = %d",
            $form_id
        ),
        ARRAY_A
    );
 
    $metadata = array_filter([
        'campaign'         => $form_data['stripe_metadata_campaign'] ?? null,
        'project'          => $form_data['stripe_metadata_project'] ?? null,
        'product'          => $form_data['stripe_metadata_product'] ?? null,
        'checkout_form_id' => (string) $form_id,
    ], fn($value) => !in_array($value, [null, '', false], true));

    $metadata = array_map('strval', $metadata);
     
    $currency = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT currency FROM $table_name WHERE id = %d",
            $form_id
        )
    );    

    // "0", null, or an empty string is treated as invalid.
    if ( empty($currency) || $currency === '0' ) {
        $currency = 'USD';
    } 
    
    // Load StripeESPADManager if not already loaded
    if ( !class_exists('\ESPAD\Stripe\StripeESPADManager') ) {
        require_once  ESPAD_PLUGIN_PATH . 'inc/StripeESPADManager.php';
    }
   
    // Get singleton instance and retrieve Stripe client
    $stripe = \ESPAD\Stripe\StripeESPADManager::get_instance()->get_stripe_client();         
         
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
        $amount = absint( calculateEspadAmount( $jsonObj->items ) );

        $customer = isset( $jsonObj->customer ) && is_object( $jsonObj->customer )
            ? $jsonObj->customer
            : null;

        $customer_email = '';
        $shipping_name  = '';

        $shipping_address = [];

        if ( $customer ) {
            $customer_email = isset( $customer->email )
                ? sanitize_email( $customer->email )
                : '';

            $shipping_name = isset( $customer->name )
                ? sanitize_text_field( $customer->name )
                : '';

            if ( isset( $customer->address ) && is_object( $customer->address ) ) {

                if ( ! empty( $customer->address->line1 ) ) {
                    $shipping_address['line1'] = sanitize_text_field( $customer->address->line1 );
                }

                if ( ! empty( $customer->address->city ) ) {
                    $shipping_address['city'] = sanitize_text_field( $customer->address->city );
                }

                if ( ! empty( $customer->address->state ) ) {
                    $shipping_address['state'] = sanitize_text_field( $customer->address->state );
                }

                if ( ! empty( $customer->address->postal_code ) ) {
                    $shipping_address['postal_code'] = sanitize_text_field( $customer->address->postal_code );
                }

                if ( ! empty( $customer->address->country ) ) {
                    $shipping_address['country'] = strtoupper(
                        sanitize_text_field( $customer->address->country )
                    );
                }
            }
        }

        $params = [
            'amount'   => $amount,
            'currency' => $currency,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ];

        if ( ! empty( $customer_email ) ) {
            $params['receipt_email'] = $customer_email;
        }

        if (
            ! empty( $shipping_name ) &&
            ! empty( $shipping_address['line1'] ) &&
            ! empty( $shipping_address['city'] ) &&
            ! empty( $shipping_address['postal_code'] ) &&
            ! empty( $shipping_address['country'] )
        ) {
            $params['shipping'] = [
                'name'    => $shipping_name,
                'address' => $shipping_address,
            ];
        }

        // Check if Stripe Connect is active
        $stripe_access_token = \get_option( 'espad_stripe_connect_access_token', '' );

        if ( ! empty( $stripe_access_token ) ) {

            // Default 1.8% platform fee
            $application_fee_amount = espad_calculate_platform_fee( $amount );

            if ( $application_fee_amount > 0 ) {
                $params['application_fee_amount'] = $application_fee_amount;
            }

        }          
               
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
 
/**
 * Create a Stripe Subscription for a given checkout form via REST API.
 *
 * Registers a REST endpoint that initializes a Stripe subscription based on
 * form-specific configuration stored in the database.
 *
 * The function retrieves metadata associated with the form, extracts the
 * subscription price ID, and creates a Stripe customer and subscription.
 * The form ID is always attached as metadata for traceability.
 *
 * Supports Stripe Connect with an optional application fee. If enabled,
 * a platform fee is applied to the subscription.
 *
 * The response includes the client secret required to complete the payment
 * or setup intent on the frontend.
 *
 * All inputs are validated, and appropriate error responses are returned
 * if required data is missing or invalid.
 *
 * @param \WP_REST_Request $request The REST API request containing the form ID.
 *
 * @return void Outputs a JSON response with subscription ID and client secret
 *              or an error message on failure.
 */
add_action('rest_api_init', function () {
    register_rest_route('espad-stripe/v1', '/create-subscription/(?P<form_id>\d+)', [
        'methods'             => 'POST',
        'callback'            => 'espad_create_subscription',
        'permission_callback' => '__return_true',
    ]);
});

function espad_create_subscription(WP_REST_Request $request) {
    
    global $wpdb;
    
    $form_id = absint( $request->get_url_params()['form_id'] ?? 0 );

    if ( ! $form_id ) {
        return new WP_REST_Response([ 'error' => 'Missing form_id.' ], 400);
    } 
    
    $table_name = $wpdb->prefix . 'espad_forms';

    // Retrieve checkout_metadata_1 based on the form ID
    $checkout_metadata = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT checkout_metadata_1 FROM $table_name WHERE id = %d",
            $form_id
        )
    ); 
    
    if ( empty( $checkout_metadata ) ) {
        return new WP_REST_Response([
            'error' => 'No checkout metadata found for this form.',
        ], 400);
    }
      
    $data = json_decode($checkout_metadata, true);

    if ( empty($data['subscription_checkout_price_id']) ) {
        return new WP_REST_Response([
            'error' => 'No price ID found in metadata.',
        ], 400);
    } 

    $price_id = $data['subscription_checkout_price_id'];
     
    if ( !class_exists('\ESPAD\Stripe\StripeESPADManager') ) {
        require_once ESPAD_PLUGIN_PATH . 'inc/StripeESPADManager.php';
    }

    $stripe = \ESPAD\Stripe\StripeESPADManager::get_instance()->get_stripe_client();
 
    try {
 
        // Create a new customer and save the checkout_form_id as metadata in Stripe
        $customer = $stripe->customers->create([
            'metadata' => [
                'checkout_form_id' => (string) $form_id,
            ],
        ]);        
        
        // Check if Stripe Connect is active
        $stripe_access_token = \get_option( 'espad_stripe_connect_access_token', '' );
        
        // Stripe Connect with Fee
        if ( ! empty( $stripe_access_token ) ) {

            $stripe_connected_account_encrypted = \get_option( 'espad_stripe_account_id', '' );
            
            $stripe_access_token_decrypted = espd_decrypt($stripe_access_token);
              
            // Default 1.8% platform fee
            $subscription = $stripe->subscriptions->create([
                'customer' => $customer->id,
                'items' => [[
                    'price' => $price_id,
                ]],
                'application_fee_percent' => 1.8,
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => [
                    'save_default_payment_method' => 'on_subscription',
                ],
                'metadata' => [
                    'checkout_form_id' => (string) $form_id,
                ],                
                'expand' => [
                    'latest_invoice.confirmation_secret',
                    'pending_setup_intent',
                ],
            ]);            
               
        } else {

            $subscription = $stripe->subscriptions->create([
                'customer' => $customer->id,
                'items' => [[
                    'price' => $price_id,
                ]],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => [
                    'save_default_payment_method' => 'on_subscription',
                ],
                'metadata' => [
                    'checkout_form_id' => (string) $form_id,
                ],                 
                'expand' => [
                    'latest_invoice.confirmation_secret',
                    'pending_setup_intent',
                ],
            ]);            
            
        }          

        $clientSecret = $subscription->latest_invoice->confirmation_secret->client_secret ?? null;

        if ( !$clientSecret ) {
            
            $pendingSetupIntent = $subscription->pending_setup_intent ?? null;

            if ($pendingSetupIntent && is_object($pendingSetupIntent) && !empty($pendingSetupIntent->client_secret)) {
                wp_send_json([
                    'subscriptionId' => $subscription->id,
                    'clientSecret'   => $pendingSetupIntent->client_secret,
                    'intentType'     => 'setup_intent',
                ]);
            }

            throw new Exception('Kein client_secret gefunden.');
            
        }

        wp_send_json([
            'subscriptionId' => $subscription->id,
            'clientSecret'   => $clientSecret,
            'intentType'     => 'payment_intent',
        ]);

    } catch (\Throwable $e) {
        wp_send_json([
            'error' => $e->getMessage(),
        ], 400);
    }
    
}

/*
 * Register a custom WordPress REST API endpoint
 * used by the React-based Multi-Step Checkout Builder.
 *
 * This endpoint receives the complete checkout flow JSON state
 * from the React application and stores it inside the local
 * WordPress database table wp_espad_forms.
 *
 * Saved data includes:
 * - Checkout name
 * - Selected language and currency
 * - Builder flow structure
 * - Multi-Step configuration JSON
 *
 * The complete React flow state is stored in the
 * checkout_metadata_1 database column so the
 * checkout builder can later restore and re-edit
 * the exact saved configuration.
 *
 * Access is restricted to WordPress administrators.
 *
 */
add_action('rest_api_init', function () {
    register_rest_route('espad-stripe/v1', '/save-multistep-checkout', [
        'methods'  => 'POST',
        'callback' => 'espad_save_multistep_checkout',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
    ]);
});

function espad_save_multistep_checkout(WP_REST_Request $request) {
    
    global $wpdb;

    $flow = $request->get_param('flow');

    if (empty($flow) || !is_array($flow)) {
        return new WP_Error(
            'invalid_flow',
            'Invalid checkout flow data.',
            ['status' => 400]
        );
    }

    $settings = $flow['settings'] ?? [];
 
    $form_name = sanitize_text_field($settings['checkoutName'] ?? 'Multi-Step Checkout');
    $currency  = strtoupper( sanitize_text_field($settings['currency'] ?? 'USD') );
    $lang      = sanitize_text_field($settings['language'] ?? 'en');

    $checkout_id = absint($request->get_param('checkoutId'));
    $builder_action = sanitize_key($request->get_param('builderAction') ?: 'new');

    $table = $wpdb->prefix . 'espad_forms';

    $flow_json = wp_json_encode($flow);

    /*
     * Update existing Multi-Step Checkout
     */
    if ($builder_action === 'edit' && $checkout_id > 0) {

        $result = $wpdb->update(
            $table,
            [
                'form_name'           => $form_name,
                'currency'            => $currency,
                'lang'                => $lang,
                'checkout_metadata_1' => $flow_json,
            ],
            [
                'id'   => $checkout_id,
                'mode' => 'Multistep',
            ],
            [
                '%s',
                '%s',
                '%s',
                '%s',
            ],
            [
                '%d',
                '%s',
            ]
        );

        if ($result === false) {
            return new WP_Error(
                'db_error',
                'Checkout could not be updated.',
                ['status' => 500]
            );
        }

        return [
            'success' => true,
            'id'      => $checkout_id,
            'action'  => 'updated',
            'message' => 'Multi-Step checkout updated successfully.',
        ];
    }

    /*
     * Insert new Multi-Step Checkout
     */
    $result = $wpdb->insert(
        $table,
        [
            'form_name'                => $form_name,
            'fix_amount'               => '',
            'currency'                 => $currency,
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
            'lang'                     => $lang,
            'payment_layout'           => '',
            'checkout_metadata_1'      => $flow_json,
        ],
        [
            '%s', '%s', '%s', '%s', '%s', '%s',
            '%s', '%s', '%s', '%s', '%s', '%s',
            '%s', '%s', '%s', '%s', '%s', '%s',
            '%s', '%s', '%s',
        ]
    );

    if (!$result) {
        return new WP_Error(
            'db_error',
            'Checkout could not be saved.',
            ['status' => 500]
        );
    }

    return [
        'success' => true,
        'id'      => $wpdb->insert_id,
        'action'  => 'created',
        'message' => 'Multi-Step checkout saved successfully.',
    ];
    
}

/**
 * Checks whether the current request is the ESPAD admin Preview tab.
 *
 * @return bool
 */
function espad_is_admin_preview(): bool {

    if ( ! is_admin() ) {
        return false;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page = isset( $_GET['page'] )
        ? sanitize_key( wp_unslash( $_GET['page'] ) )
        : '';

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $tab = isset( $_GET['tab'] )
        ? sanitize_key( wp_unslash( $_GET['tab'] ) )
        : '';

    return (
        $page === 'espd_main' &&
        $tab === 'preview'
    );
}
 
/**
 * Registers the [espad_multistep_checkout] shortcode and renders
 * the React-based Multi-Step Checkout frontend application.
 *
 * This shortcode:
 * - Loads the saved checkout flow JSON from the database
 * - Enqueues the React frontend assets
 * - Renders the React mount container with serialized flow data
 * - Pre-renders the existing Stripe Checkout form in PHP
 *   so React can later move and display it dynamically
 *   inside the "stripe_payments" component step
 *
 * Usage:
 * [espad_multistep_checkout id="123"]
 */
add_shortcode('espad_multistep_checkout', 'espad_render_multistep_checkout');

function espad_render_multistep_checkout($atts) {

    global $wpdb;

    $is_admin_preview = espad_is_admin_preview();

    if (
        doing_filter( 'get_the_excerpt' ) ||
        doing_filter( 'the_excerpt' ) ||
        (
            ! $is_admin_preview &&
            (
                is_archive() ||
                is_search() ||
                ! is_singular()
            )
        )
    ) {
        return '';
    }

    $atts = shortcode_atts(
        array(
            'id' => 0,
        ),
        $atts,
        'espad_multistep_checkout'
    );

    /*
     * This is the ID of the Multistep Checkout itself.
     */
    $form_id = absint($atts['id']);

    if (!$form_id) {
        return '<p>' .
            esc_html__(
                'Invalid multistep checkout ID.',
                'easy-stripe-payments'
            ) .
        '</p>';
    }

    $table = $wpdb->prefix . 'espad_forms';

    $form = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, mode, checkout_metadata_1
             FROM {$table}
             WHERE id = %d
             AND mode = %s
             LIMIT 1",
            $form_id,
            'Multistep'
        )
    );

    if (
        !$form ||
        empty($form->checkout_metadata_1)
    ) {
        return '<p>' .
            esc_html__(
                'The Multistep Checkout could not be found.',
                'easy-stripe-payments'
            ) .
        '</p>';
    }

    $flow_json = $form->checkout_metadata_1;
    $flow      = json_decode($flow_json, true);

    if (!is_array($flow)) {
        return '<p>' .
            esc_html__(
                'The checkout configuration is invalid.',
                'easy-stripe-payments'
            ) .
        '</p>';
    }

    /*
     * Find the Stripe Payments field inside the saved JSON state.
     */
    $stripe_payment_field = null;

    foreach (($flow['steps'] ?? array()) as $step) {
        foreach (($step['fields'] ?? array()) as $field) {
            if (
                isset($field['type']) &&
                $field['type'] === 'stripe_payments'
            ) {
                $stripe_payment_field = $field;
                break 2;
            }
        }
    }

    if (!$stripe_payment_field) {
        return '<p>' .
            esc_html__(
                'The Stripe Payments component is missing.',
                'easy-stripe-payments'
            ) .
        '</p>';
    }

    /*
     * ID of an existing normal ESPAD Payment Form.
     *
     * Empty or zero means:
     * Use the dynamic React Stripe checkout.
     */
    $selected_payment_form_id = absint(
        $stripe_payment_field['settings']['paymentFormId'] ?? 0
    );

    $payment_mode      = 'dynamic';
    $payment_form_html = '';
    $payment_template  = '';

    /*
     * Load general payment frontend assets & SweetAlert for both payment modes:
     * existing Payment Form and dynamic React checkout.
     */ 
    espd_add_payment_shortcode_scripts();
    wp_enqueue_script('sweetalert');    
 
    /*
     * Existing Payment Form mode.
     */
    if ($selected_payment_form_id > 0) {

        $existing_payment_form = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id
                 FROM {$table}
                 WHERE id = %d
                 AND mode <> %s
                 LIMIT 1",
                $selected_payment_form_id,
                'Multistep'
            )
        );

        if (!$existing_payment_form) {
            return '<p>' .
                esc_html__(
                    'The selected Stripe Payment Form could not be found.',
                    'easy-stripe-payments'
                ) .
            '</p>';
        }

        $payment_mode = 'existing';

        /*
         * Load assets for the legacy, existing Payment Form.
         */
        espad_enqueue_existing_checkout_assets();

        /* 
         * Pass the selected normal Payment Form ID to main-form.php.
         */
        $shortcode_form_id = $selected_payment_form_id;
        $mode              = '';

        ob_start();

        require ESPAD_PLUGIN_PATH . 'frontend/main-form.php';

        $payment_form_html = ob_get_clean();

        /*
         * React later moves this wrapper into the Stripe step.
         */
        $payment_template = sprintf(
            '<div
                id="espad-payment-form-template-%1$d"
                class="espad-payment-form-template"
                data-payment-form-id="%2$d"
                style="display:none;"
            >%3$s</div>',
            $form_id,
            $selected_payment_form_id,
            $payment_form_html
        );

    } else {

        /*
         * Dynamic React Payment Element mode.
         *
         * Do not load checkout.js here.
         */
        espad_enqueue_stripe_base_assets();
        
    }

    return sprintf(
        '<div
            class="espad-multistep-checkout"
            data-form-id="%1$d"
            data-flow="%2$s"
            data-payment-mode="%3$s"
            data-payment-form-id="%4$d"
        ></div>
        %5$s',
        $form_id,
        esc_attr($flow_json),
        esc_attr($payment_mode),
        $selected_payment_form_id,
        $payment_template
    );

}

/**
 * Registers and enqueues the React Multi-Step Checkout assets.
 *
 * Can be used in both frontend and admin preview.
 *
 * @return void
 */
function espad_enqueue_multistep_frontend_assets() {

    $asset_file = ESPAD_PLUGIN_PATH . 'build/index.asset.php';
    $js_file    = ESPAD_PLUGIN_PATH . 'build/index.js';
    $css_file   = ESPAD_PLUGIN_PATH . 'build/style-index.css';

    $asset = file_exists( $asset_file )
        ? include $asset_file
        : array(
            'dependencies' => array(
                'wp-element',
                'wp-i18n',
            ),
            'version' => '1.0.0',
        );

    $dependencies = (
        isset( $asset['dependencies'] ) &&
        is_array( $asset['dependencies'] )
    )
        ? $asset['dependencies']
        : array(
            'wp-element',
            'wp-i18n',
        );
 
    /*
     * Use each generated file's modification time.
     * The version changes automatically whenever the file
     * is rewritten by npm run build.
     */
    $js_version = file_exists( $js_file )
        ? (string) filemtime( $js_file )
        : (string) ( $asset['version'] ?? '1.0.0' );

    $css_version = file_exists( $css_file )
        ? (string) filemtime( $css_file )
        : (string) ( $asset['version'] ?? '1.0.0' );

    wp_register_script(
        'espad-multistep-frontend',
        ESPAD_PLUGIN_URL . 'build/index.js',
        $dependencies,
        $js_version,
        true
    );
 
    wp_register_style(
        'espad-multistep-frontend',
        ESPAD_PLUGIN_URL . 'build/style-index.css',
        array(),
        $css_version
    );

    /*
     * Create the token used for Stripe return URLs.
     */
    try {
        $espad_payment_token = bin2hex(
            random_bytes( 16 )
        );
    } catch ( Exception $exception ) {
        $espad_payment_token = wp_generate_password(
            32,
            false,
            false
        );
    }

    $current_url = is_admin()
        ? admin_url(
            'admin.php?page=espd_main&tab=preview'
        )
        : ESPAD_CURRENT_URL;

    $espad_return_url = remove_query_arg(
        array(
            'payment_intent',
            'payment_intent_client_secret',
            'redirect_status',
            'espad_payment_token',
        ),
        $current_url
    );

    $espad_return_url = add_query_arg(
        'espad_payment_token',
        $espad_payment_token,
        $espad_return_url
    );

    wp_add_inline_script(
        'espad-multistep-frontend',
        'window.espadMultistepData = ' .
        wp_json_encode(
            array(
                'createPaymentIntentUrl' => esc_url_raw(
                    rest_url(
                        'espad-stripe/v1/multistep-payment-intent'
                    )
                ),
                'updateExistingMetadataUrl' => esc_url_raw(
                    rest_url(
                        'espad-stripe/v1/update-existing-payment-metadata'
                    )
                ),
                'returnUrl' => esc_url_raw(
                    $espad_return_url
                ),
                'paymentToken' => sanitize_text_field(
                    $espad_payment_token
                ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
            )
        ) . ';',
        'before'
    );

    wp_enqueue_script(
        'espad-multistep-frontend'
    );

    wp_enqueue_style(
        'espad-multistep-frontend'
    );

    wp_set_script_translations(
        'espad-multistep-frontend',
        'easy-stripe-payments',
        ESPAD_PLUGIN_PATH . 'languages'
    );
}

/**
 * Registers and enqueues the React-based Multi-Step Checkout assets.
 */
add_action('wp_enqueue_scripts', 'espad_register_multistep_frontend_assets');
 
function espad_register_multistep_frontend_assets() {

    if ( ! is_singular() ) {
        return;
    }

    $post = get_queried_object();

    if ( ! ( $post instanceof WP_Post ) ) {
        return;
    }

    if (
        ! has_shortcode(
            $post->post_content,
            'espad_multistep_checkout'
        )
    ) {
        return;
    }
 
    espad_enqueue_multistep_frontend_assets();

}

/**
 * Loads the React Multi-Step Checkout bundle in the ESPAD Preview tab.
 *
 * @param string $hook_suffix Current WordPress admin page hook.
 *
 * @return void
 */ 
add_action('admin_enqueue_scripts', 'espad_enqueue_multistep_preview_assets');

function espad_enqueue_multistep_preview_assets(
    $hook_suffix
) {

    // phpcs:disable WordPress.Security.NonceVerification.Recommended

    $page = isset( $_GET['page'] )
        ? sanitize_key(
            wp_unslash( $_GET['page'] )
        )
        : '';

    $tab = isset( $_GET['tab'] )
        ? sanitize_key(
            wp_unslash( $_GET['tab'] )
        )
        : '';

    $form_id = isset( $_GET['form_id'] )
        ? absint( $_GET['form_id'] )
        : 0;

    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    if (
        $page !== 'espd_main' ||
        $tab !== 'preview' ||
        ! $form_id
    ) {
        return;
    }

    global $wpdb;

    $table = $wpdb->prefix . 'espad_forms';

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $form_mode = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT mode
             FROM {$table}
             WHERE id = %d
             LIMIT 1",
            $form_id
        )
    );

    if ( $form_mode !== 'Multistep' ) {
        return;
    }

    espad_enqueue_multistep_frontend_assets();

    /*
     * Stripe.js is needed for the dynamic Stripe Payment Element.
     */
    espad_enqueue_stripe_base_assets();

    wp_enqueue_script( 'sweetalert' );
    
}

/**
 * Converts a checkout amount into a consistently formatted
 * decimal string for Stripe metadata.
 *
 * @param float|int|string $amount Amount in the checkout currency.
 * @return string
 */
function espad_format_checkout_metadata_amount( $amount ) {

    return number_format(
        round( (float) $amount, 2 ),
        2,
        '.',
        ''
    );

}

/**
 * Converts a submitted checkout value into a readable string.
 *
 * @param mixed  $value      Submitted frontend value.
 * @param string $field_type Checkout Builder field type.
 * @return string
 */
function espad_format_checkout_metadata_value(
    $value,
    $field_type = ''
) {

    if ( $field_type === 'checkbox' ) {
        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN
        ) ? 'Yes' : 'No';
    }

    if ( is_bool( $value ) ) {
        return $value ? 'Yes' : 'No';
    }

    if ( is_array( $value ) ) {
        $value = implode(
            ', ',
            array_map(
                'sanitize_text_field',
                $value
            )
        );
    }

    if ( ! is_scalar( $value ) ) {
        return '';
    }

    return sanitize_text_field(
        (string) $value
    );

}

/**
 * Returns the readable label of a selected dropdown option.
 *
 * @param array  $field           Checkout Builder field configuration.
 * @param string $submitted_value Submitted dropdown value.
 * @return string
 */
function espad_get_dropdown_metadata_value(
    array $field,
    $submitted_value
) {

    $submitted_value = sanitize_text_field(
        (string) $submitted_value
    );

    foreach (
        ( $field['settings']['options'] ?? array() )
        as $option
    ) {
        $option_value = sanitize_text_field(
            (string) ( $option['value'] ?? '' )
        );

        if ( $option_value === $submitted_value ) {
            return sanitize_text_field(
                (string) (
                    $option['label'] ??
                    $submitted_value
                )
            );
        }
    }

    return $submitted_value;

}

/**
 * Adds a safe value to Stripe metadata.
 *
 * Stripe metadata is flat and has strict limits. The helper
 * therefore shortens keys and values and prevents an excessive
 * number of metadata entries.
 *
 * @param array  $metadata Metadata array passed by reference.
 * @param string $key      Metadata key.
 * @param mixed  $value    Metadata value.
 * @return void
 */
function espad_add_checkout_metadata(
    array &$metadata,
    $key,
    $value
) {

    /*
     * Keep two metadata slots available for possible future
     * plugin-level values.
     */
    if ( count( $metadata ) >= 48 ) {
        return;
    }

    if ( ! is_scalar( $value ) ) {
        return;
    }

    $key = sanitize_key( $key );

    /*
     * Stripe metadata keys must remain short.
     */
    $key = substr( $key, 0, 40 );

    $value = sanitize_text_field(
        (string) $value
    );

    if ( $key === '' || $value === '' ) {
        return;
    }

    $metadata[ $key ] = substr(
        $value,
        0,
        500
    );

}

/*
 * Register the REST API endpoint for the Checkout Builder.
 *
 * This endpoint validates the submitted checkout data, calculates
 * the final payment amount, creates a Stripe PaymentIntent, and
 * returns the client secret required by the Stripe Payment Element.
 */
add_action('rest_api_init', function () {
    register_rest_route(
        'espad-stripe/v1',
        '/multistep-payment-intent',
        [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'espad_create_multistep_payment_intent',
            'permission_callback' => '__return_true',
        ]
    );
}); 

function espad_create_multistep_payment_intent( WP_REST_Request $request ) {

    global $wpdb;

    $checkout_id = absint(
        $request->get_param( 'checkoutId' )
    );

    $submitted_cart = $request->get_param(
        'cartItems'
    );

    $submitted_data = $request->get_param(
        'formData'
    );

    if ( ! $checkout_id ) {
        return new WP_Error(
            'invalid_checkout',
            __(
                'Invalid checkout ID.',
                'easy-stripe-payments'
            ),
            array(
                'status' => 400,
            )
        );
    }

    if ( ! is_array( $submitted_cart ) ) {
        $submitted_cart = array();
    }

    if ( ! is_array( $submitted_data ) ) {
        $submitted_data = array();
    }

    $table = $wpdb->prefix . 'espad_forms';

    $form = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, currency, checkout_metadata_1
             FROM {$table}
             WHERE id = %d
             AND mode = %s
             LIMIT 1",
            $checkout_id,
            'Multistep'
        )
    );

    if (
        ! $form ||
        empty( $form->checkout_metadata_1 )
    ) {
        return new WP_Error(
            'checkout_not_found',
            __(
                'Checkout not found.',
                'easy-stripe-payments'
            ),
            array(
                'status' => 404,
            )
        );
    }

    $flow = json_decode(
        $form->checkout_metadata_1,
        true
    );

    if ( ! is_array( $flow ) ) {
        return new WP_Error(
            'invalid_flow',
            __(
                'Invalid checkout configuration.',
                'easy-stripe-payments'
            ),
            array(
                'status' => 500,
            )
        );
    }

    $currency = strtolower(
        sanitize_key(
            $flow['settings']['currency'] ??
            $form->currency ??
            'usd'
        )
    );

    $available_products = array();
    $frontend_fields    = array();
    $tax_items          = array();
    $coupon_items       = array();
    $donation_fields    = array(); 

    $cover_fees_enabled = false;
    $cover_fees_label   = 'Cover transaction fees';
    $cover_fees_percent = 1.8;    

    /*
     * Read the authoritative checkout configuration from the
     * database. Prices, fees and discounts must never be trusted
     * directly from the frontend request.
     */
    foreach (
        ( $flow['steps'] ?? array() )
        as $step
    ) {
        foreach (
            ( $step['fields'] ?? array() )
            as $field
        ) {
            $field_id = sanitize_text_field(
                (string) ( $field['id'] ?? '' )
            );

            $field_type = sanitize_key(
                $field['type'] ?? ''
            );

            $field_label = sanitize_text_field(
                (string) (
                    $field['label'] ??
                    $field_type ??
                    'Field'
                )
            );

            /*
             * Products.
             */
            if ( $field_type === 'product' ) {
                $product_key = (string) (
                    $field['settings']['productId'] ??
                    $field_id
                );

                if ( $product_key === '' ) {
                    continue;
                }

                $available_products[ $product_key ] = array(
                    'id'           => $product_key,
                    'field_id'     => $field_id,
                    'title'        => $field_label ?: 'Product',
                    'price'        => max(
                        0,
                        (float) (
                            $field['settings']['price'] ??
                            0
                        )
                    ),
                    'max_quantity' => max(
                        1,
                        absint(
                            $field['settings']['maxQuantity'] ??
                            1
                        )
                    ),
                );

                continue;
            }

            /*
             * Fees and taxes.
             */
            if ( $field_type === 'tax' ) {
                $tax_items[] = array(
                    'id'     => $field_id,
                    'label'  => $field_label ?: 'Fees & Taxes',
                    'amount' => max(
                        0,
                        (float) (
                            $field['settings']['amount'] ??
                            0
                        )
                    ),
                );

                continue;
            }

            /*
             * Discounts and coupons.
             */
            if ( $field_type === 'coupon' ) {
                $coupon_items[] = array(
                    'id'     => $field_id,
                    'label'  => $field_label ?: 'Discount',
                    'amount' => max(
                        0,
                        (float) (
                            $field['settings']['amount'] ??
                            0
                        )
                    ),
                );

                continue;
            }
             
            /*
             * Donation Widgets.
             *
             * The selected donation is submitted through formData,
             * but its validity is checked against the authoritative
             * server-side Checkout Builder configuration.
             */
            if ( $field_type === 'donation' ) {

                $configured_amounts = array();

                foreach (
                    (array) (
                        $field['settings']['amounts'] ??
                        array()
                    )
                    as $configured_amount
                ) {
                    $configured_amount = round(
                        max(
                            0,
                            (float) $configured_amount
                        ),
                        2
                    );

                    if ( $configured_amount > 0 ) {
                        $configured_amounts[] =
                            $configured_amount;
                    }
                }

                $donation_fields[ $field_id ] = array(
                    'id' => $field_id,

                    'amounts' => array_slice(
                        array_values(
                            array_unique(
                                $configured_amounts
                            )
                        ),
                        0,
                        6
                    ),

                    'allow_custom_amount' =>
                        ! empty(
                            $field['settings']['allowCustomAmount']
                        ),

                    'minimum_amount' => round(
                        max(
                            0,
                            (float) (
                                $field['settings']['minimumAmount'] ??
                                1
                            )
                        ),
                        2
                    ),
                );

                continue;
            }            
            
            /*
             * Dynamic transaction fee.
             *
             * The amount is never read from the frontend. The component
             * only determines whether the fixed 1.8% fee is enabled.
             */
            if ( $field_type === 'cover_fees' ) {
                $cover_fees_enabled = true;

                $cover_fees_label = $field_label
                    ?: 'Cover transaction fees';

                continue;
            }            

            /*
             * Components without a user-submitted value must not
             * be stored as form fields.
             */
            $excluded_types = array(
                'stripe_payments',
                'image_field',
                'stripe_metadata',
                'cover_fees',
            );

            if (
                $field_id !== '' &&
                ! in_array(
                    $field_type,
                    $excluded_types,
                    true
                )
            ) {
                $frontend_fields[ $field_id ] = array(
                    'id'       => $field_id,
                    'type'     => $field_type,
                    'label'    => $field_label ?: 'Field',
                    'settings' => is_array(
                        $field['settings'] ?? null
                    )
                        ? $field['settings']
                        : array(),
                );
            }
        }
    }

    /*
     * Validate the submitted products and calculate the product
     * subtotal from the saved server-side configuration.
     */
    $product_total = 0;
    $line_items    = array();

    foreach (
        $submitted_cart
        as $cart_key => $cart_item
    ) {
        $cart_key = (string) $cart_key;

        if (
            ! isset(
                $available_products[ $cart_key ]
            )
        ) {
            continue;
        }

        if ( ! is_array( $cart_item ) ) {
            continue;
        }

        $configured_product =
            $available_products[ $cart_key ];

        $quantity = min(
            $configured_product['max_quantity'],
            max(
                0,
                absint(
                    $cart_item['quantity'] ?? 0
                )
            )
        );

        if ( $quantity < 1 ) {
            continue;
        }

        $unit_price = round(
            (float) $configured_product['price'],
            2
        );

        $line_total = round(
            $unit_price * $quantity,
            2
        );

        $product_total += $line_total;

        $line_items[] = array(
            'product_id' => $configured_product['id'],
            'title'      => $configured_product['title'],
            'quantity'   => $quantity,
            'unit_price' => $unit_price,
            'line_total' => $line_total,
        );
    }

    $product_total = round(
        $product_total,
        2
    );
    
    /*
     * Validate and calculate selected donations.
     */
    $donation_total = 0.0;

    foreach (
        $donation_fields
        as $donation_field_id => $donation_field
    ) {
        if (
            ! array_key_exists(
                $donation_field_id,
                $submitted_data
            )
        ) {
            continue;
        }

        $submitted_donation = round(
            (float) $submitted_data[
                $donation_field_id
            ],
            2
        );

        /*
         * An empty or zero donation does not contribute
         * to the checkout total.
         */
        if ( $submitted_donation <= 0 ) {
            continue;
        }

        $is_predefined_amount = false;

        foreach (
            $donation_field['amounts']
            as $configured_amount
        ) {
            if (
                abs(
                    $submitted_donation -
                    (float) $configured_amount
                ) < 0.001
            ) {
                $is_predefined_amount = true;

                break;
            }
        }

        /*
         * Values that are not predefined are only accepted
         * when Custom Amount is enabled and the configured
         * minimum amount is respected.
         */
        if ( ! $is_predefined_amount ) {

            if (
                ! $donation_field[
                    'allow_custom_amount'
                ]
            ) {
                return new WP_Error(
                    'invalid_donation_amount',
                    __(
                        'The selected donation amount is invalid.',
                        'easy-stripe-payments'
                    ),
                    array(
                        'status' => 400,
                    )
                );
            }

            if (
                $submitted_donation <
                $donation_field['minimum_amount']
            ) {
                return new WP_Error(
                    'donation_amount_too_low',
                    sprintf(
                        /* translators: %s: minimum donation amount */
                        __(
                            'The minimum donation amount is %s.',
                            'easy-stripe-payments'
                        ),
                        espad_format_checkout_metadata_amount(
                            $donation_field[
                                'minimum_amount'
                            ]
                        ) . ' ' . strtoupper(
                            $currency
                        )
                    ),
                    array(
                        'status' => 400,
                    )
                );
            }
        }

        $donation_total +=
            $submitted_donation;
    }

    $donation_total = round(
        $donation_total,
        2
    );    

    $tax_total = round(
        array_reduce(
            $tax_items,
            static function (
                $total,
                $item
            ) {
                return $total +
                    (float) $item['amount'];
            },
            0
        ),
        2
    );

    $coupon_total = round(
        array_reduce(
            $coupon_items,
            static function (
                $total,
                $item
            ) {
                return $total +
                    (float) $item['amount'];
            },
            0
        ),
        2
    );
    
    /*
     * Checkout total before the transaction fee.
     */  
    $checkout_subtotal = round(
        max(
            0,
            $product_total +
            $donation_total +
            $tax_total -
            $coupon_total
        ),
        2
    );    
 
    /*
     * Fixed 1.8% transaction fee.
     */
    $cover_fees_amount = $cover_fees_enabled
        ? round(
            $checkout_subtotal *
            ( $cover_fees_percent / 100 ),
            2
        )
        : 0.0;

    /*
     * Final amount charged through Stripe.
     */
    $total = round(
        $checkout_subtotal +
        $cover_fees_amount,
        2
    );    

    /*
     * Stripe expects the amount in the smallest currency unit.
     * This assumes a two-decimal currency.
     */
    $amount = (int) round(
        $total * 100
    );

    if ( $amount < 50 ) {
        return new WP_Error(
            'invalid_amount',
            __(
                'The payment amount is too low.',
                'easy-stripe-payments'
            ),
            array(
                'status' => 400,
            )
        );
    }

    /*
     * Build readable Stripe metadata.
     */
    $metadata = array();

    espad_add_checkout_metadata(
        $metadata,
        'checkout_id',
        $checkout_id
    );

    espad_add_checkout_metadata(
        $metadata,
        'currency',
        strtoupper( $currency )
    );

    /*
     * Product line items.
     */
    foreach (
        $line_items
        as $index => $line_item
    ) {
        $number = str_pad(
            (string) ( $index + 1 ),
            2,
            '0',
            STR_PAD_LEFT
        );

        $line_value = sprintf(
            '%s | Qty: %d | Unit: %s %s | Line total: %s %s',
            $line_item['title'],
            $line_item['quantity'],
            espad_format_checkout_metadata_amount(
                $line_item['unit_price']
            ),
            strtoupper( $currency ),
            espad_format_checkout_metadata_amount(
                $line_item['line_total']
            ),
            strtoupper( $currency )
        );

        espad_add_checkout_metadata(
            $metadata,
            'line_item_' . $number,
            $line_value
        );
    }
     
    /*
     * Selected donation.
     */
    if ( $donation_total > 0 ) {
        espad_add_checkout_metadata(
            $metadata,
            'donation',
            espad_format_checkout_metadata_amount(
                $donation_total
            ) . ' ' . strtoupper(
                $currency
            )
        );
    }    

    /*
     * User-entered frontend fields.
     */
    $form_field_number = 1;
    $customer_email    = '';

    foreach (
        $frontend_fields
        as $field_id => $field
    ) {
        if (
            ! array_key_exists(
                $field_id,
                $submitted_data
            )
        ) {
            continue;
        }

        $value = $submitted_data[ $field_id ];

        if (
            $field['type'] === 'dropdown_field'
        ) {
            $value = espad_get_dropdown_metadata_value(
                $field,
                $value
            );
        } else {
            $value = espad_format_checkout_metadata_value(
                $value,
                $field['type']
            );
        }

        /*
         * Empty optional fields are not added to Stripe.
         */
        if (
            $value === '' &&
            $field['type'] !== 'checkbox'
        ) {
            continue;
        }

        $number = str_pad(
            (string) $form_field_number,
            2,
            '0',
            STR_PAD_LEFT
        );

        espad_add_checkout_metadata(
            $metadata,
            'form_field_' . $number,
            sprintf(
                '%s: %s',
                $field['label'],
                $value
            )
        );

        /*
         * Also use the submitted email as the Stripe receipt
         * email when the component is an email field.
         */
        if (
            $field['type'] === 'email' &&
            is_email( $value )
        ) {
            $customer_email = sanitize_email(
                $value
            );
        }

        $form_field_number++;
    }

    /*
     * Individually named fees and taxes.
     */
    foreach (
        $tax_items
        as $index => $tax_item
    ) {
        $number = str_pad(
            (string) ( $index + 1 ),
            2,
            '0',
            STR_PAD_LEFT
        );

        espad_add_checkout_metadata(
            $metadata,
            'fee_tax_' . $number,
            sprintf(
                '%s: +%s %s',
                $tax_item['label'],
                espad_format_checkout_metadata_amount(
                    $tax_item['amount']
                ),
                strtoupper( $currency )
            )
        );
    }

    /*
     * Individually named discounts and coupons.
     */
    foreach (
        $coupon_items
        as $index => $coupon_item
    ) {
        $number = str_pad(
            (string) ( $index + 1 ),
            2,
            '0',
            STR_PAD_LEFT
        );

        espad_add_checkout_metadata(
            $metadata,
            'discount_' . $number,
            sprintf(
                '%s: -%s %s',
                $coupon_item['label'],
                espad_format_checkout_metadata_amount(
                    $coupon_item['amount']
                ),
                strtoupper( $currency )
            )
        );
    }
    
    /*
     * Dynamic Cover transaction fees metadata.
     */
    if ( $cover_fees_enabled ) {
        espad_add_checkout_metadata(
            $metadata,
            'cover_transaction_fees',
            sprintf(
                '%s (%s%%): +%s %s',
                $cover_fees_label,
                espad_format_checkout_metadata_amount(
                    $cover_fees_percent
                ),
                espad_format_checkout_metadata_amount(
                    $cover_fees_amount
                ),
                strtoupper( $currency )
            )
        );
    }     

    /*
     * Complete Stripe Payments overview.
     */
    if ( $product_total > 0 ) {
        espad_add_checkout_metadata(
            $metadata,
            'product_subtotal',
            espad_format_checkout_metadata_amount(
                $product_total
            ) . ' ' . strtoupper( $currency )
        );
    }

    if ( $tax_total > 0 ) {
        espad_add_checkout_metadata(
            $metadata,
            'fees_taxes_total',
            espad_format_checkout_metadata_amount(
                $tax_total
            ) . ' ' . strtoupper( $currency )
        );
    }
 
    if ( $coupon_total > 0 ) {
        espad_add_checkout_metadata(
            $metadata,
            'discounts_total',
            espad_format_checkout_metadata_amount(
                $coupon_total
            ) . ' ' . strtoupper( $currency )
        );
    }
 
    /*
     * The final payment total always exists and is therefore
     * always added to Stripe metadata.
     */        
    espad_add_checkout_metadata(
        $metadata,
        'payment_total',
        espad_format_checkout_metadata_amount(
            $total
        ) . ' ' . strtoupper( $currency )
    );

    try {
        if (
            ! class_exists(
                '\ESPAD\Stripe\StripeESPADManager'
            )
        ) {
            require_once ESPAD_PLUGIN_PATH .
                'inc/StripeESPADManager.php';
        }

        $connect_access_token_encrypted = get_option(
            'espad_stripe_connect_access_token',
            ''
        );

        $connect_publishable_key_encrypted =
            get_option(
                'espad_stripe_connect_publishable_key',
                ''
            );

        if (
            empty( $connect_access_token_encrypted ) ||
            empty( $connect_publishable_key_encrypted )
        ) {
            return new WP_Error(
                'stripe_connect_required',
                __(
                    'To use the Checkout Builder, connect your Stripe account under Settings.',
                    'easy-stripe-payments'
                ),
                array(
                    'status' => 400,
                )
            );
        }

        $connect_access_token = espd_decrypt(
            $connect_access_token_encrypted
        );

        $connect_publishable_key = espd_decrypt(
            $connect_publishable_key_encrypted
        );

        if (
            empty( $connect_access_token ) ||
            empty( $connect_publishable_key )
        ) {
            return new WP_Error(
                'invalid_stripe_connect_credentials',
                __(
                    'The Stripe Connect credentials are invalid. Please reconnect your Stripe account.',
                    'easy-stripe-payments'
                ),
                array(
                    'status' => 500,
                )
            );
        }

        $stripe =
            \ESPAD\Stripe\StripeESPADManager::get_instance()
                ->get_stripe_client();

        $params = array(
            'amount'   => $amount,
            'currency' => $currency,
            'metadata' => $metadata,
            'automatic_payment_methods' => array(
                'enabled' => true,
            ),
        );

        if ( ! empty( $customer_email ) ) {
            $params['receipt_email'] =
                $customer_email;
        }

        $application_fee_amount =
            espad_calculate_platform_fee(
                $amount
            );

        if ( $application_fee_amount > 0 ) {
            $params['application_fee_amount'] =
                $application_fee_amount;
        }

        $payment_intent =
            $stripe->paymentIntents->create(
                $params
            );

        return rest_ensure_response(
            array(
                'clientSecret' =>
                    $payment_intent->client_secret,

                'paymentIntentId' =>
                    $payment_intent->id,

                'publishableKey' =>
                    $connect_publishable_key,

                'amount' =>
                    $amount,

                'currency' =>
                    $currency,
            )  
        );
    } catch ( \Throwable $exception ) {
        return new WP_Error(
            'stripe_error',
            $exception->getMessage(),
            array(
                'status' => 500,
            )
        );
    }
    
}

/*
 * Add Multi-Step Checkout metadata to a PaymentIntent
 * created by an existing legacy ESPAD Payment Form.
 */
add_action(
    'rest_api_init',
    function () {
        register_rest_route(
            'espad-stripe/v1',
            '/update-existing-payment-metadata',
            array(
                'methods'             =>
                    WP_REST_Server::CREATABLE,

                'callback'            =>
                    'espad_update_existing_payment_metadata',

                'permission_callback' =>
                    '__return_true',
            )
        );
    }
);

/**
 * Adds the Multi-Step Checkout metadata to a successfully
 * paid PaymentIntent created by an existing ESPAD form.
 *
 * Existing Stripe metadata is preserved.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response|WP_Error
 */
function espad_update_existing_payment_metadata(
    WP_REST_Request $request
) {

    global $wpdb;

    $checkout_id = absint(
        $request->get_param( 'checkoutId' )
    );

    $payment_form_id = absint(
        $request->get_param( 'paymentFormId' )
    );

    $payment_intent_id = sanitize_text_field(
        (string) $request->get_param(
            'paymentIntentId'
        )
    );

    $payment_intent_client_secret =
        sanitize_text_field(
            (string) $request->get_param(
                'paymentIntentClientSecret'
            )
        );

    $submitted_cart = $request->get_param(
        'cartItems'
    );

    $submitted_data = $request->get_param(
        'formData'
    );

    if (
        ! $checkout_id ||
        ! $payment_form_id ||
        ! preg_match(
            '/^pi_[A-Za-z0-9]+$/',
            $payment_intent_id
        ) ||
        strpos(
            $payment_intent_client_secret,
            $payment_intent_id . '_secret_'
        ) !== 0
    ) {
        return new WP_Error(
            'invalid_metadata_request',
            __(
                'Invalid payment metadata request.',
                'easy-stripe-payments'
            ),
            array(
                'status' => 400,
            )
        );
    }

    if ( ! is_array( $submitted_cart ) ) {
        $submitted_cart = array();
    }

    if ( ! is_array( $submitted_data ) ) {
        $submitted_data = array();
    }

    $table = $wpdb->prefix . 'espad_forms';

    /*
     * Load the Multi-Step Checkout configuration.
     */
    $multistep_form = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, currency, checkout_metadata_1
             FROM {$table}
             WHERE id = %d
             AND mode = %s
             LIMIT 1",
            $checkout_id,
            'Multistep'
        )
    );

    if (
        ! $multistep_form ||
        empty(
            $multistep_form->checkout_metadata_1
        )
    ) {
        return new WP_Error(
            'multistep_checkout_not_found',
            __(
                'The Multi-Step Checkout could not be found.',
                'easy-stripe-payments'
            ),
            array(
                'status' => 404,
            )
        );
    }

    /*
     * Verify that the submitted legacy Payment Form exists.
     */
    $legacy_form_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id
             FROM {$table}
             WHERE id = %d
             AND mode <> %s
             LIMIT 1",
            $payment_form_id,
            'Multistep'
        )
    );

    if ( ! $legacy_form_exists ) {
        return new WP_Error(
            'payment_form_not_found',
            __(
                'The selected payment form could not be found.',
                'easy-stripe-payments'
            ),
            array(
                'status' => 404,
            )
        );
    }

    $flow = json_decode(
        $multistep_form->checkout_metadata_1,
        true
    );

    if ( ! is_array( $flow ) ) {
        return new WP_Error(
            'invalid_checkout_flow',
            __(
                'The checkout flow is invalid.',
                'easy-stripe-payments'
            ),
            array(
                'status' => 500,
            )
        );
    }

    /*
     * Ensure that this Multi-Step Checkout actually uses
     * the submitted existing Payment Form.
     */
    $configured_payment_form_id = 0;

    foreach (
        ( $flow['steps'] ?? array() )
        as $step
    ) {
        foreach (
            ( $step['fields'] ?? array() )
            as $field
        ) {
            if (
                ( $field['type'] ?? '' ) !==
                'stripe_payments'
            ) {
                continue;
            }

            $configured_payment_form_id = absint(
                $field['settings']['paymentFormId'] ?? 0
            );

            break 2;
        }
    }

    if (
        $configured_payment_form_id !==
        $payment_form_id
    ) {
        return new WP_Error(
            'payment_form_mismatch',
            __(
                'The payment form does not belong to this checkout.',
                'easy-stripe-payments'
            ),
            array(
                'status' => 403,
            )
        );
    }

    try {
        if (
            ! class_exists(
                '\ESPAD\Stripe\StripeESPADManager'
            )
        ) {
            require_once ESPAD_PLUGIN_PATH .
                'inc/StripeESPADManager.php';
        }

        $stripe =
            \ESPAD\Stripe\StripeESPADManager::get_instance()
                ->get_stripe_client();

        /*
         * Retrieve the PaymentIntent directly from Stripe.
         */
        $payment_intent =
            $stripe->paymentIntents->retrieve(
                $payment_intent_id,
                array()
            );

        /*
         * Verify that the returned client secret belongs
         * to the retrieved PaymentIntent.
         */
        if (
            empty( $payment_intent->client_secret ) ||
            ! hash_equals(
                (string) $payment_intent->client_secret,
                $payment_intent_client_secret
            )
        ) {
            return new WP_Error(
                'payment_intent_mismatch',
                __(
                    'The PaymentIntent verification failed.',
                    'easy-stripe-payments'
                ),
                array(
                    'status' => 403,
                )
            );
        }

        /*
         * Do not trust redirect_status alone.
         * Stripe must confirm the successful payment.
         */
        if (
            (string) $payment_intent->status !==
            'succeeded'
        ) {
            return new WP_Error(
                'payment_not_succeeded',
                __(
                    'The payment has not been completed.',
                    'easy-stripe-payments'
                ),
                array(
                    'status' => 409,
                )
            );
        }

        /*
         * Build the Multi-Step Checkout metadata.
         *
         * This helper is shown in the next section.
         */
        $multistep_metadata =
            espad_build_multistep_checkout_metadata(
                $checkout_id,
                $flow,
                $submitted_cart,
                $submitted_data,
                $multistep_form->currency
            );

        if ( is_wp_error( $multistep_metadata ) ) {
            return $multistep_metadata;
        }        

        /*
         * Convert StripeObject metadata into a normal array.
         */
        $existing_metadata = array();

        if ( ! empty( $payment_intent->metadata ) ) {
            $existing_metadata =
                $payment_intent->metadata->toArray();
        }
 
        /*
         * Preserve all existing legacy metadata first.
         */
        $merged_metadata = array_slice(
            $existing_metadata,
            0,
            50,
            true
        );

        /*
         * Append as many Multi-Step Checkout metadata entries
         * as Stripe's 50-entry limit allows.
         */
        foreach (
            $multistep_metadata
            as $key => $value
        ) {
            if ( count( $merged_metadata ) >= 50 ) {
                break;
            }

            /*
             * Add a prefix to prevent collisions with metadata
             * created by the existing Payment Form.
             */
            $multistep_key = sanitize_key(
                'multistep_' . $key
            );

            $multistep_key = substr(
                $multistep_key,
                0,
                40
            );

            if (
                array_key_exists(
                    $multistep_key,
                    $merged_metadata
                )
            ) {
                continue;
            }
 
            $merged_metadata[ $multistep_key ] =
                substr(
                    sanitize_text_field(
                        (string) $value
                    ),
                    0,
                    500
                );
        }

        $stripe->paymentIntents->update(
            $payment_intent_id,
            array(
                'metadata' => $merged_metadata,
            )
        );

        return rest_ensure_response(
            array(
                'success'         => true,
                'paymentIntentId' =>
                    $payment_intent_id,
            )
        );

    } catch ( \Stripe\Exception\ApiErrorException $e ) {
        return new WP_Error(
            'stripe_metadata_update_failed',
            sanitize_text_field(
                $e->getMessage()
            ),
            array(
                'status' => 500,
            )
        );
    } catch ( Throwable $e ) {
        return new WP_Error(
            'metadata_update_failed',
            __(
                'The payment metadata could not be updated.',
                'easy-stripe-payments'
            ),
            array(
                'status' => 500,
            )
        );
    }

}

/**
 * Builds the complete readable Stripe metadata for a
 * React-based Multi-Step Checkout.
 *
 * Product prices, maximum quantities, fees, taxes and discounts
 * are always loaded from the saved checkout flow. The submitted
 * frontend cart is only used to determine selected quantities.
 *
 * @param int    $checkout_id      Multi-Step Checkout ID.
 * @param array  $flow             Saved Checkout Builder flow.
 * @param array  $submitted_cart   Cart submitted by React.
 * @param array  $submitted_data   Form data submitted by React.
 * @param string $fallback_currency Fallback checkout currency.
 *
 * @return array<string,string>|WP_Error
 */ 
function espad_build_multistep_checkout_metadata(
    $checkout_id,
    array $flow,
    array $submitted_cart,
    array $submitted_data,
    $fallback_currency = 'USD'
) {
 
    $checkout_id = absint( $checkout_id );

    if ( ! $checkout_id ) {
        return new WP_Error(
            'invalid_checkout',
            __(
                'Invalid checkout ID.',
                'easy-stripe-payments'
            ),
            array(
                'status' => 400,
            )
        );
    }

    /*
     * Determine the authoritative checkout currency.
     */
    $currency = strtolower(
        sanitize_key(
            $flow['settings']['currency'] ??
            $fallback_currency ??
            'usd'
        )
    );

    if (
        $currency === '' ||
        strlen( $currency ) !== 3
    ) {
        $currency = 'usd';
    }

    $available_products = array();
    $frontend_fields    = array();
    $tax_items          = array();
    $coupon_items       = array();

    /*
     * Read all authoritative checkout values from the saved
     * Checkout Builder flow.
     *
     * Never trust product prices, taxes or discounts sent
     * from the frontend.
     */
    foreach (
        ( $flow['steps'] ?? array() )
        as $step
    ) {
        foreach (
            ( $step['fields'] ?? array() )
            as $field
        ) {
            if ( ! is_array( $field ) ) {
                continue;
            }

            $field_id = sanitize_text_field(
                (string) (
                    $field['id'] ?? ''
                )
            );

            $field_type = sanitize_key(
                (string) (
                    $field['type'] ?? ''
                )
            );

            $field_label = sanitize_text_field(
                (string) (
                    $field['label'] ??
                    $field_type ??
                    'Field'
                )
            );

            /*
             * Products.
             */
            if ( $field_type === 'product' ) {
                $product_key = sanitize_text_field(
                    (string) (
                        $field['settings']['productId'] ??
                        $field_id
                    )
                );

                if ( $product_key === '' ) {
                    continue;
                }

                $available_products[ $product_key ] = array(
                    'id'       => $product_key,
                    'field_id' => $field_id,
                    'title'    => $field_label ?: 'Product',

                    'price' => round(
                        max(
                            0,
                            (float) (
                                $field['settings']['price'] ??
                                0
                            )
                        ),
                        2
                    ),

                    'max_quantity' => max(
                        1,
                        absint(
                            $field['settings']['maxQuantity'] ??
                            1
                        )
                    ),
                );

                continue;
            }

            /*
             * Fees and taxes.
             */
            if ( $field_type === 'tax' ) {
                $tax_items[] = array(
                    'id'    => $field_id,

                    'label' => $field_label
                        ?: 'Fees & Taxes',

                    'amount' => round(
                        max(
                            0,
                            (float) (
                                $field['settings']['amount'] ??
                                0
                            )
                        ),
                        2
                    ),
                );

                continue;
            }

            /*
             * Coupons and discounts.
             */
            if ( $field_type === 'coupon' ) {
                $coupon_items[] = array(
                    'id'    => $field_id,

                    'label' => $field_label
                        ?: 'Coupon',

                    'amount' => round(
                        max(
                            0,
                            (float) (
                                $field['settings']['amount'] ??
                                0
                            )
                        ),
                        2
                    ),
                );

                continue;
            }

            /*
             * Components that should be stored as submitted
             * frontend form values.
             *
             * Visual, product, payment and calculation components
             * are intentionally excluded.
             */
            if (
                $field_id !== '' &&
                ! in_array(
                    $field_type,
                    array(
                        'product',
                        'stripe_payments',
                        'tax',
                        'coupon',
                        'image_field',
                    ),
                    true
                )
            ) {
                $frontend_fields[ $field_id ] = array(
                    'id'       => $field_id,
                    'type'     => $field_type,
                    'label'    => $field_label ?: 'Field',
                    'settings' => is_array(
                        $field['settings'] ?? null
                    )
                        ? $field['settings']
                        : array(),
                );
            }
        }
    }

    /*
     * Build validated product line items.
     */
    $line_items   = array();
    $product_total = 0.0;

    foreach (
        $submitted_cart
        as $submitted_product_key => $submitted_item
    ) {
        if ( ! is_array( $submitted_item ) ) {
            continue;
        }

        $submitted_product_key = sanitize_text_field(
            (string) $submitted_product_key
        );

        /*
         * Depending on the React cart structure, the product
         * can be identified either by the cart array key or by
         * the submitted productId value.
         */
        $submitted_product_id = sanitize_text_field(
            (string) (
                $submitted_item['productId'] ??
                $submitted_product_key
            )
        );

        $configured_product = null;

        if (
            isset(
                $available_products[
                    $submitted_product_key
                ]
            )
        ) {
            $configured_product =
                $available_products[
                    $submitted_product_key
                ];
        } elseif (
            $submitted_product_id !== '' &&
            isset(
                $available_products[
                    $submitted_product_id
                ]
            )
        ) {
            $configured_product =
                $available_products[
                    $submitted_product_id
                ];
        }

        /*
         * Ignore unknown frontend products.
         */
        if ( ! is_array( $configured_product ) ) {
            continue;
        }

        $quantity = absint(
            $submitted_item['quantity'] ?? 0
        );

        if ( $quantity < 1 ) {
            continue;
        }

        /*
         * Enforce the maximum quantity stored in the flow.
         */
        $quantity = min(
            $quantity,
            $configured_product['max_quantity']
        );

        $unit_price = round(
            (float) $configured_product['price'],
            2
        );

        $line_total = round(
            $unit_price * $quantity,
            2
        );

        $product_total += $line_total;

        $line_items[] = array(
            'product_id' =>
                $configured_product['id'],

            'title' =>
                $configured_product['title'],

            'quantity' =>
                $quantity,

            'unit_price' =>
                $unit_price,

            'line_total' =>
                $line_total,
        );
    }

    $product_total = round(
        $product_total,
        2
    );

    /*
     * Calculate fees and taxes from the saved flow.
     */
    $tax_total = round(
        array_reduce(
            $tax_items,
            static function (
                $total,
                $item
            ) {
                return $total +
                    (float) $item['amount'];
            },
            0.0
        ),
        2
    );

    /*
     * Calculate coupons and discounts from the saved flow.
     */
    $coupon_total = round(
        array_reduce(
            $coupon_items,
            static function (
                $total,
                $item
            ) {
                return $total +
                    (float) $item['amount'];
            },
            0.0
        ),
        2
    );

    /*
     * The final checkout total can never become negative.
     */
    $payment_total = round(
        max(
            0,
            $product_total +
            $tax_total -
            $coupon_total
        ),
        2
    );

    /*
     * Build the readable Stripe metadata.
     */
    $metadata = array();

    espad_add_checkout_metadata(
        $metadata,
        'checkout_id',
        $checkout_id
    );

    espad_add_checkout_metadata(
        $metadata,
        'currency',
        strtoupper( $currency )
    );

    /*
     * Individual product line items.
     */
    foreach (
        $line_items
        as $index => $line_item
    ) {
        $number = str_pad(
            (string) ( $index + 1 ),
            2,
            '0',
            STR_PAD_LEFT
        );

        espad_add_checkout_metadata(
            $metadata,
            'line_item_' . $number,
            sprintf(
                '%s | Qty: %d | Unit: %s %s | Line total: %s %s',
                $line_item['title'],
                $line_item['quantity'],

                espad_format_checkout_metadata_amount(
                    $line_item['unit_price']
                ),

                strtoupper( $currency ),

                espad_format_checkout_metadata_amount(
                    $line_item['line_total']
                ),

                strtoupper( $currency )
            )
        );
    }

    /*
     * Submitted frontend fields.
     */
    $form_field_number = 1;

    foreach (
        $frontend_fields
        as $field_id => $field
    ) {
        if (
            ! array_key_exists(
                $field_id,
                $submitted_data
            )
        ) {
            continue;
        }

        $value = $submitted_data[ $field_id ];

        if (
            $field['type'] ===
            'dropdown_field'
        ) {
            $value =
                espad_get_dropdown_metadata_value(
                    $field,
                    $value
                );
        } else {
            $value =
                espad_format_checkout_metadata_value(
                    $value,
                    $field['type']
                );
        }

        /*
         * Keep false checkbox values because "No" is meaningful,
         * but skip genuinely empty values.
         */
        if (
            $value === '' ||
            $value === null
        ) {
            continue;
        }

        $number = str_pad(
            (string) $form_field_number,
            2,
            '0',
            STR_PAD_LEFT
        );

        espad_add_checkout_metadata(
            $metadata,
            'form_field_' . $number,
            sprintf(
                '%s: %s',
                $field['label'],
                $value
            )
        );

        $form_field_number++;
    }

    /*
     * Individual fees and taxes.
     */
    foreach (
        $tax_items
        as $index => $tax_item
    ) {
        $number = str_pad(
            (string) ( $index + 1 ),
            2,
            '0',
            STR_PAD_LEFT
        );

        espad_add_checkout_metadata(
            $metadata,
            'fee_tax_' . $number,
            sprintf(
                '%s: +%s %s',
                $tax_item['label'],

                espad_format_checkout_metadata_amount(
                    $tax_item['amount']
                ),

                strtoupper( $currency )
            )
        );
    }

    /*
     * Individual coupons and discounts.
     */
    foreach (
        $coupon_items
        as $index => $coupon_item
    ) {
        $number = str_pad(
            (string) ( $index + 1 ),
            2,
            '0',
            STR_PAD_LEFT
        );

        espad_add_checkout_metadata(
            $metadata,
            'discount_' . $number,
            sprintf(
                '%s: -%s %s',
                $coupon_item['label'],

                espad_format_checkout_metadata_amount(
                    $coupon_item['amount']
                ),

                strtoupper( $currency )
            )
        );
    }

    /*
     * Complete Stripe Payments overview.
     */
    if ( $product_total > 0 ) {
        espad_add_checkout_metadata(
            $metadata,
            'product_subtotal',
            espad_format_checkout_metadata_amount(
                $product_total
            ) . ' ' . strtoupper( $currency )
        );
    }

    if ( $tax_total > 0 ) {
        espad_add_checkout_metadata(
            $metadata,
            'fees_taxes_total',
            espad_format_checkout_metadata_amount(
                $tax_total
            ) . ' ' . strtoupper( $currency )
        );
    }

    if ( $coupon_total > 0 ) {
        espad_add_checkout_metadata(
            $metadata,
            'discounts_total',
            espad_format_checkout_metadata_amount(
                $coupon_total
            ) . ' ' . strtoupper( $currency )
        );
    }
  
    /*
     * The final payment total always exists and is therefore
     * always added to Stripe metadata.
     */    
    espad_add_checkout_metadata(
        $metadata,
        'payment_total',
        espad_format_checkout_metadata_amount(
            $payment_total
        ) . ' ' . strtoupper( $currency )
    );

    return $metadata;
}

