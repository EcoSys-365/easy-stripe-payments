<?php

defined( 'ABSPATH' ) || exit;

// Define the registration link constant if it hasn't been defined yet
defined( 'ESPAD_REGISTER_LINK' )     || define( 'ESPAD_REGISTER_LINK', 'https://www.payments-and-donations.com?register_stripe_domain=' . urlencode(ESPAD_DOMAIN) );
// Define the encrypted Stripe public key
defined( 'ESPAD_STRIPE_PUBLIC_KEY' ) || ($val = get_option('espd_stripe_public_key')) && !empty($val) && define('ESPAD_STRIPE_PUBLIC_KEY', $val);
// Define the encrypted Stripe secret key
defined( 'ESPAD_STRIPE_SECRET_KEY' ) || ($val = get_option('espd_stripe_secret_key')) && !empty($val) && define('ESPAD_STRIPE_SECRET_KEY', $val); 
// Define the Stripe Access 'true'   
(!defined('ESPAD_STRIPE_ACCESS') && defined('ESPAD_STRIPE_PUBLIC_KEY') && defined('ESPAD_STRIPE_SECRET_KEY')) && define('ESPAD_STRIPE_ACCESS', true);
// Define the Stripe Access 'false'
(!defined('ESPAD_STRIPE_ACCESS') && (!defined('ESPAD_STRIPE_PUBLIC_KEY') || !defined('ESPAD_STRIPE_SECRET_KEY'))) && define('ESPAD_STRIPE_ACCESS', false);
// Allowed Image Tags
if ( ! defined( 'ESPAD_ALLOWED_IMG_TAGS' ) ) {
    define( 'ESPAD_ALLOWED_IMG_TAGS', [
        'img' => [
            'src' => [],
            'alt' => [],
            'class' => [],
            'width' => [],
            'style' => [],
            'height' => [],
            'loading' => [],
            'decoding' => [],
        ],
    ]);
}

/**
 * Retrieves and hashes the encryption key from a predefined file.
 *
 * This function attempts to load a PHP file containing a raw encryption key,
 * hashes it using SHA-256, and returns the resulting hash.
 * If the file does not exist, it returns false.
 *
 * @return string|false The hashed encryption key as a SHA-256 string, or false if the key file is missing.
 */
function espd_get_encryption_key() {
    
    // Define the path to the key file
    $key_file = ESPAD_PLUGIN_URL . 'inc/materials/components/web/k_384733092.php';
    
    // If the key file exists, load its contents and hash it using SHA-256
    if ( file_exists( $key_file ) ) {
        
        return hash( 'sha256', require $key_file ); 
        
    }
    
    // Return false if the key file does not exist
    return false;
    
}

/**
 * Encrypts the given data using AES-256-CBC with a dynamic IV.
 *
 * This function retrieves a hashed encryption key, generates a random 16-byte IV,
 * encrypts the input data with OpenSSL using AES-256-CBC, and returns the
 * result as a base64-encoded string. The IV is prepended to the encrypted data
 * before encoding to allow for proper decryption later.
 *
 * @param string $data The plain text data to be encrypted.
 * @return string The base64-encoded string containing the IV and the encrypted data.
 */
function espd_encrypt( $data ) {
    
    // Retrieve the encryption key
    $key = espd_get_encryption_key();

    // Generate a 16-byte initialization vector (IV)
    $iv = openssl_random_pseudo_bytes( 16 );

    // Encrypt the data using AES-256-CBC with the provided key and IV
    $encrypted = openssl_encrypt( $data, 'AES-256-CBC', $key, 0, $iv );

    // Return the base64-encoded IV + encrypted data (concatenated)
    return base64_encode( $iv . $encrypted );
}

/**
 * Decrypts data that was encrypted using espd_encrypt().
 *
 * This function takes a base64-encoded string containing the IV and the encrypted data,
 * decodes it, extracts the first 16 bytes as the IV, and decrypts the remaining
 * ciphertext using AES-256-CBC and the same encryption key.
 *
 * @param string $data The base64-encoded string containing the IV and encrypted data.
 * @return string|false The decrypted plain text on success, or false on failure.
 */
function espd_decrypt( $data ) {

    // Retrieve the encryption key
    $key = espd_get_encryption_key();

    // Decode the base64-encoded data
    $data = base64_decode( $data );

    // Extract the first 16 bytes as the IV
    $iv = substr( $data, 0, 16 );

    // Extract the remaining bytes as the encrypted data
    $encrypted = substr( $data, 16 );

    // Decrypt the data using AES-256-CBC
    return openssl_decrypt( $encrypted, 'AES-256-CBC', $key, 0, $iv );
}

/**
 * Initializes the Stripe manager 
 *
 * This function includes the required StripeESPADManager class file
 * and initializes its singleton instance to set up Stripe integration.
 *
 * @return void
 */
function espad_stripe_manager_init() {
    
    // Include the StripeESPADManager class.
    require_once ESPAD_PLUGIN_PATH . 'inc/StripeESPADManager.php';

    // Initialize the singleton instance of the StripeESPADManager class.
    \ESPAD\Stripe\StripeESPADManager::get_instance();      
    
}

/**
 *
 * Outputs a message that the plugin is not connected with Stripe
 *
 */
function espad_plugin_is_not_connected_to_stripe() { 
    
    require_once ESPAD_PLUGIN_PATH . 'inc/setup/stripe-connection.php'; 
    
}

/**
 * Displays an admin notice if the domain is not registered.
 *
 * This function checks the stored (and decrypted) membership status.
 * If the status matches the specific unregistered value, it shows
 * a dismissible info notice in the WordPress admin with a link to register.
 *
 * @return void
 */ 
function espd_domain_is_not_registered() {
    
    $membership_status = get_current_membership_status();
    
    if ( $membership_status != "1" ) {
    
        echo '<div class="notice notice-info is-dismissible">';
            echo '<p><strong>' . esc_html(__( 'Domain is not registered', 'easy-stripe-payments' )) . '</strong>: ' . esc_html( ESPAD_DOMAIN ) . ' <a href=" ' . esc_url(ESPAD_REGISTER_LINK) . '" target="_blank" rel="noopener">' . esc_html(__( 'Register now', 'easy-stripe-payments' )) . '</a></p>';
        echo '</div>';
        
    }
  
}

/**
 * Retrieves and decrypts the current premium membership status.
 *
 * This function fetches the encrypted membership status from the WordPress options table,
 * decrypts it (if available), and returns the resulting value.
 *
 * @return string The decrypted membership status
 */
function get_current_membership_status() {
    
    $stored_membership_status = get_option( 'espd_membership_status', '' );

    $membership_status = $stored_membership_status ? espd_decrypt( $stored_membership_status ) : ''; 
    
    return $membership_status;
    
}
   
/**
 * Prepares and enqueues the necessary script and data for a JavaScript-based redirect.
 *
 * This function uses wp_localize_script to securely pass the target URL to an external
 * script file (redirect.js).
 *
 * @param string|null $url The valid URL to redirect to.
 */
function espad_redirect_to_url( $url = null ) {
    
    // Trim URL
    $url = trim( $url );    
    
    // Only proceed if a URL is present and valid
    if ( empty( $url ) || ! filter_var( $url, FILTER_VALIDATE_URL ) ) return;

    // Set URL to script
    wp_localize_script(
        'espad-redirect-script', // Handle the Script
        'espadRedirectData',     // Global JS-Object
        array(
            'redirectUrl' => esc_url_raw( $url ), // Set the URL
        )
    );

    // Enqueue script to load in the footer
    wp_enqueue_script( 'espad-redirect-script' );
    
}
 
/**
 * Outputs a safe <img> HTML tag with support for local plugin paths or external URLs.
 *
 * This function takes a relative plugin asset path or an absolute URL,
 * applies proper escaping to all attributes (src, alt, and any custom attributes),
 * and returns a fully formed <img> tag. It ensures security and compatibility
 * with WordPress coding standards and HTML sanitization functions like wp_kses().
 *
 * @param string $path_or_url  Relative path to plugin image (within ESPAD_PLUGIN_URL) or full external URL.
 * @param string $alt          Alternative text for the image.
 * @param array  $attributes   Additional HTML attributes as key => value pairs (e.g. class, width, height).
 *
 * @return string Escaped <img> HTML string.
 */
function espad_plugin_image( $path_or_url, $alt = '', $attributes = array() ) {
    
    $url = filter_var( $path_or_url, FILTER_VALIDATE_URL ) ? $path_or_url :
        trailingslashit( ESPAD_PLUGIN_URL ) . ltrim( $path_or_url, '/' );

    $attr_html = '';
    foreach ( $attributes as $key => $value ) {
        $attr_html .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
    }
      
    // phpcs:disable WordPress.WP.DirectOutput.ImageTags, WordPress.Security.EscapeOutput.OutputNotEscaped, PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
    return sprintf(
        '<img src="%s" alt="%s"%s />',
        esc_url( $url ),
        esc_attr( $alt ),
        $attr_html
    );
    // phpcs:enable    
     
} 

/**
 * Retrieves and returns a list of active Stripe payment methods for recurring payments.
 *
 *
 */
function active_stripe_payment_methods($show_admin_msg) {
     
    $active_payment_methods     = [];
    $active_payment_methods_raw = [];    
    
    try {
        $configurations = \Stripe\PaymentMethod::all([   
            'limit' => 100,
        ]);
  
        $discovered_methods     = [];
        $discovered_methods_raw = [];

        foreach ($configurations->data as $config) {
            if ($config->active) {
                $potential_payment_method_types = potential_stripe_payment_method_types();

                foreach ($potential_payment_method_types as $method_type) {
                    if (
                        isset($config->$method_type) &&
                        isset($config->$method_type->available) &&
                        $config->$method_type->available === true
                    ) {
                        $discovered_methods[]     = str_replace('_', ' ', ucfirst($method_type));
                        $discovered_methods_raw[] = $method_type;
                    }
                }
            }
        }

        // Overwrite only if a valid result is found
        if ( ! empty($discovered_methods) ) {
            $active_payment_methods = array_values(array_unique($discovered_methods));
        }
        if ( ! empty($discovered_methods_raw) ) {
            $active_payment_methods_raw = array_values(array_unique($discovered_methods_raw));
        }
 
    } catch (\Stripe\Exception\ApiErrorException $e) {
        // error_log('Stripe API Error: ' . $e->getMessage());
    } catch (\Exception $e) {
        // error_log('General Error: ' . $e->getMessage());
    }
 
    $payment_methods_string = implode(', ', $active_payment_methods);
    
    if ( $show_admin_msg ) {
        echo '<div class="notice notice-info is-dismissible">';
            echo '<p>'; 
                echo esc_html__( 'Active Stripe payment methods for Subscription Payments: ', 'easy-stripe-payments' );
                echo ' <b class="blue">' . esc_html($payment_methods_string) . '</b> ';
                echo esc_html__( 'You can manage these in your Stripe Dashboard.', 'easy-stripe-payments' ); 
            echo '</p>';
        echo '</div>';  
    }
     
    return $active_payment_methods_raw;
}


/**
 * Checks and returns the current premium membership status.
 *
 * If the stored status is missing or a forced check is requested,
 * the function calls the external API to verify the domain's premium status,
 * stores the result (encrypted) and updates the last check date.
 *
 * @param bool $force_check Whether to force a fresh status check via the API.
 * @return bool True if the domain has premium membership, false otherwise.
 */
function espad_premium_membership_check( bool $force_check = false ): bool {
    
    $stored_membership_status = get_option( 'espd_membership_status', '' );
    $membership_last_check    = get_option( 'espd_membership_last_check', '' ); 
    $today                    = gmdate('dmY');

    $membership_status = $stored_membership_status ? espd_decrypt( $stored_membership_status ) : '';
    
    if ( $membership_last_check !== $today || $force_check || $membership_status === '' ) {    
        
        $is_premium = is_premium_domain();

        update_option( 'espd_membership_status', espd_encrypt($is_premium ? '1' : '0') );
        update_option( 'espd_membership_last_check', $today );
        
        return $is_premium;
        
    }
    
    return $membership_status === '1';
    
}


/**
 * Queries the external API to check if the current domain has premium status.
 *
 * Sends a GET request to the Ecosys365 API using the defined domain and authorization header.
 * If the response is successful and contains a 'premium' flag, it returns the result as a boolean.
 * Logs errors if the request fails or the response structure is invalid.
 *
 * @return bool True if the domain is marked as premium, false otherwise.
 */
function is_premium_domain() {
    
    $url = 'https://api.ecosys365.com/MembershipController?domain=' . urlencode(ESPAD_DOMAIN);

    $response = wp_remote_get($url, [
        'timeout' => 15, 
        'headers' => [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer wowe3243WIFJEFifj3434wfweQWEFI',
        ],
    ]);

    if ( is_wp_error($response) ) {
        
        return false; 
        
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if ( !isset($data['premium']) ) {
        
        return false;
    
    }

    return (bool) $data['premium'];
    
}

// Returns a comprehensive list of potential Stripe payment method types.
// This list is used to check which payment methods are enabled within Stripe Payment Method Configurations.
function potential_stripe_payment_method_types() {
    
    // These payment methods are accepted by Stripe in a subscription mode
    return [
      'card',
      //'sepa_debit', Needs after some days Webhooks to confirm the payment
      'bacs_debit',
      'acss_debit',
      'au_becs_debit',
      'paypal',
      'us_bank_account',
      'customer_balance',
    ];
    
}

/**
 * Checks if the welcome page has been visited.
 * 
 * If not visited yet, displays the welcome lightbox and marks the page as visited.
 *
 * @return void
 */
function espad_check_first_visit() {
    
    if ( get_option('espd_welcome_page_visited') !== '1' ) {

        espd_welcome_lightbox();

        update_option('espd_welcome_page_visited', '1');

    }    
    
}

/**
 * Displays an admin info box with setup steps for Stripe payment options.
 *
 * This function outputs a dismissible WordPress admin notice containing three
 * easy steps for setting up Stripe payments, including links to the appropriate
 * admin pages for configuring payment forms and recurring payments.
 *
 * @return void
 */
function espd_setup_steps_infobox() {
    
    ?>
 
    <div class="notice notice-info is-dismissible">
        <p style="line-height: 30px;">
 
            <strong><?php echo esc_html(__( 'Easily accept payments with three flexible Stripe options', 'easy-stripe-payments' )); ?></strong>:<br />
            <?php echo esc_html(__( '1. Stripe Checkout integration', 'easy-stripe-payments' )); ?>   
            <a href="<?php echo esc_url( admin_url('admin.php?page=espd_main&tab=forms') ); ?>" target="_blank">
                <?php echo esc_html(__( 'Payment Forms', 'easy-stripe-payments' )); ?>
            </a><br />
            <?php echo esc_html(__( '2. Campaign-based payments with built-in Stripe Checkout', 'easy-stripe-payments' )); ?> 
            <a href="<?php echo esc_url( admin_url('admin.php?page=espd_main&tab=forms') ); ?>" target="_blank">
                <?php echo esc_html(__( 'Payment Forms', 'easy-stripe-payments' )); ?>
            </a><br />
            <?php echo esc_html(__( '3. Recurring payments via button and redirect to Stripe’s secure hosted Checkout page', 'easy-stripe-payments' )); ?>
            <a href="<?php echo esc_url( admin_url('admin.php?page=espd_main&tab=recurring') ); ?>" target="_blank">
                <?php echo esc_html(__( 'Recurring Payments', 'easy-stripe-payments' )); ?>
            </a>

        </p>
    </div>

    <?php
    
}

/**
 * Creates a Stripe Checkout Session for a subscription button using the provided price ID, product details and payment methods.
 *
 * Inputs are sanitized to ensure safe fallback values and to avoid deprecation warnings.
 * If session creation fails due to locale issues, a fallback attempt without locale is made.
 */
function create_stripe_product_and_session($price_id, $product_id, $product_currency, $product_language, $active_payment_methods) {

    $session = null;
    $error_message = null;
    
    // Sanitize inputs to avoid deprecated ltrim() calls
    $price_id         = (string) ($price_id ?? 'dummy_price');
    $product_id       = (string) ($product_id ?? 'dummy_product');
    $product_currency = (string) ($product_currency ?? 'USD');
    $product_language = (string) ($product_language ?? 'en'); 
     
    // Generate Token if not already exists
    if ( ! isset($_SESSION['espad_payment_token']) ) {

        $espad_token = bin2hex(random_bytes(16)); // 32-character hex string

        $_SESSION['espad_payment_token'] = $espad_token; 
        
    } else {
         
        $espad_token = sanitize_text_field( $_SESSION['espad_payment_token']);
        
    }      
     
    try {
        
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => $active_payment_methods,
            'line_items'           => [[
                'price'    => $price_id,
                'quantity' => 1,
            ]],
            'mode'                       => 'subscription',
            'success_url'                => ESPAD_SITE_URL . '/wp-json/easy-stripe-payments/v1/success?espad_payment_token=' . urlencode($espad_token) . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'                 => ESPAD_SITE_URL . '?espad_stripe_status=failed',
            'billing_address_collection' => 'required',
            'locale'                     => $product_language,
        ]);
         
    } catch (\Throwable $e) {
        
        $message = 'Stripe error: ' . $e->getMessage();
        //error_log($message);

        if (str_contains($e->getMessage(), 'locale')) {
            try {
                $session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => $active_payment_methods,
                    'line_items'           => [[
                        'price'    => $price_id,
                        'quantity' => 1,
                    ]],
                    'mode'                       => 'subscription',
                    'success_url'                => ESPAD_SITE_URL . '/wp-json/easy-stripe-payments/v1/success?espad_payment_token=' . urlencode($espad_token) . '&session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url'                 => ESPAD_SITE_URL . '?espad_stripe_status=failed',
                    'billing_address_collection' => 'required',
                    'locale'                     => 'en',
                ]);
            } catch (\Throwable $e2) {
        
                $error_message = 'Stripe Error: ' . $e2->getMessage();
                
            }
        } else {
            $error_message = 'Stripe Error: ' . $e->getMessage();
        }
        
    }

    // If the Stripe session is NULL, do not abort – instead, use a dummy value or display a notice.
    if ( !$session ) {
        return [
            'checkout_url' => null,
            'product_name' => null,
            'error'        => $error_message ?: 'Unbekannter Stripe-Fehler'
        ];
    }

    try {
        
        $product = \Stripe\Product::retrieve($product_id);
        $product_name = $product->name;
        
    } catch (\Throwable $e) {
        
        $product_name = null;
        
    }

    return [
        'checkout_url' => $session->url,
        'product_name' => $product_name
    ];
    
}

/**
 * Formats a campaign amount based on the given currency.
 *
 * This function ensures the amount is numeric and then formats it 
 * with the appropriate thousands separator, depending on the currency.
 * - Currencies like USD, GBP, etc., use a comma as the thousands separator.
 * - Currencies like EUR, CHF, etc., use a dot as the thousands separator.
 * - If the currency is not recognized, the amount is returned unformatted.
 *
 * @param mixed  $amount   The amount to format. If null, empty, or non-numeric, it defaults to 0.
 * @param string $currency The currency code (e.g., 'USD', 'EUR', 'JPY').
 *
 * @return string|int Formatted amount string or unformatted amount if currency is unknown.
 */
function format_campaign_amount($amount, $currency) {
    
    // Fallback to 0 if the amount is null, empty, or not numeric
    if ( $amount === null || $amount === '' || !is_numeric($amount) ) {
        $amount = 0;
    }    
    
    switch ( strtoupper($currency) ) {
        
        // Use comma as thousands separator (e.g., 1,000,000)
        case 'USD': // US Dollar
        case 'GBP': // British Pound
        case 'HKD': // Hong Kong Dollar
        case 'CAD': // Canadian Dollar
        case 'AUD': // Australian Dollar
        case 'SGD': // Singapore Dollar
            return number_format($amount, 0, '', ',');

        // Use dot as thousands separator (e.g., 1.000.000)
        case 'EUR': // Euro
        case 'CHF': // Swiss Franc
        case 'CNY': // Chinese Yuan
        case 'JPY': // Japanese Yen
            return number_format($amount, 0, '', '.');

        // No formatting for unknown currencies
        default:
            return $amount;
    }
}

/**
 * Calculates the progress percentage of a campaign for use in a progress bar.
 *
 * This function takes the current amount and goal amount of a campaign
 * and calculates how far along the campaign is in percentage terms.
 * It ensures all values are numeric and protects against division by zero or invalid ranges.
 *
 * @param mixed $campaign_current_amount The initial & current amount raised.
 * @param mixed $campaign_goal_amount    The target goal amount for the campaign.
 *
 * @return int Progress percentage (0–100).
 */
function calculate_progress_bar($campaign_current_amount, $campaign_goal_amount) {
    
    // Ensure all values are numeric; default to 0 if not
    $campaign_current_amount = is_numeric($campaign_current_amount) ? floatval($campaign_current_amount) : 0;
    $campaign_goal_amount    = is_numeric($campaign_goal_amount)    ? floatval($campaign_goal_amount)    : 0;

    // Avoid division by zero
    if ( $campaign_goal_amount > 0 ) {
        // Calculate percentage progress
        $progress = ( $campaign_current_amount / $campaign_goal_amount ) * 100;

        // Clamp to 100% maximum
        $progress = min(100, round($progress));
    } else {
        // No valid goal; fallback to 0%
        $progress = 0;
    }

    return $progress;
    
}

/**
 * Renders a JavaScript-based payout chart using provided payout data.
 *
 * This function outputs the Chart.js library (assets/js/espd-backend-chart.js).
 * It receives payout data and an account ID, passes the data to JavaScript,
 * and extracts labels (dates) and data (amounts) for use in the chart.
 *
 * Note: This assumes that Chart.js is already enqueued and that there is a canvas element
 * with the ID `payoutChart` available in the DOM where the chart should be rendered.
 *
 * @param array  $payoutData Array of payout entries with 'date' and 'amount' keys.
 * @param string $account_id The Stripe account ID.
 *
 * @return void
 */ 
function payout_chart($payoutData, $account_id) {
     
    echo '<div id="espad-payout-chart-welcome-tab"
        data-payout-data="' . wp_json_encode( $payoutData ) . '"
        data-account-id="' . esc_html( $account_id ) . '"></div>';
    
} 

/** 
 * Outputs a hidden <div> element that provides data attributes
 * used by the JavaScript welcome modal.
 *
 * The <div> includes:
 * - data-espad-register-link: the registration URL for Premium membership.
 * - data-espad-plugin-url: the base URL of the plugin's assets (e.g., images).
 *
 * @return void
 */
function espd_welcome_lightbox() {
    
    $espad_register_link = esc_url(ESPAD_REGISTER_LINK);
    $espad_plugin_url    = esc_url(ESPAD_PLUGIN_URL);
       
    echo '<div id="espad-welcome-message" 
        data-espad-register-link="' . esc_url( $espad_register_link ) . '" 
        data-espad-plugin-url="' . esc_url( $espad_plugin_url ) . '">
    </div>';    
      
}  
  
/**
 * Render the hidden Premium Lightbox container in the frontend.
 *
 * This function outputs a <div> element with the ID 'espd-premium-lightbox'.
 * The element itself is not visible to the user, but provides data attributes
 * that are later read by the JavaScript SweetAlert integration.
 *
 * Data attributes included:
 * - data-espad-register-link : The registration URL for becoming a Premium Member.
 * - data-espad-plugin-url    : The base URL of the plugin directory, used to load assets (e.g., images).
 *
 * @return  void
 */
function espd_premium_lightbox() {
       
    $espad_register_link = esc_url(ESPAD_REGISTER_LINK);
    $espad_plugin_url    = esc_url(ESPAD_PLUGIN_URL);
    
    echo '<div id="espd-premium-lightbox" 
        data-espad-register-link="' . esc_url( $espad_register_link ) . '" 
        data-espad-plugin-url="' . esc_url( $espad_plugin_url ) . '">
    </div>';    
       
}
