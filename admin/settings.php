<?php 
  
defined( 'ABSPATH' ) || exit; 

espad_check_first_visit();

espd_setup_steps_infobox(); 

espd_domain_is_not_registered(); 
 
$espad_code = 'Iweofiw3234feWIEJFqwefidk4893';

if ( isset($_GET['nonce']) && isset($_GET['espad_db_code']) && isset($_GET['espad_code']) && $_GET['espad_code'] === 'fwWIEFJF372183wifefeAEFIEFJ346' ) {
 
    $nonce = sanitize_text_field($_GET['nonce']);
 
    // Nonce verification
    if ( ! wp_verify_nonce($nonce, 'espad_stripe_connect') ) {
        wp_die('Nonce verification failed');
    }
    
    // Sanitize
    $espad_db_code = sanitize_text_field($_GET['espad_db_code']);
     
    // Check data via API
    $stripe_connect_data = get_stripe_connect_data($espad_db_code);
     
    $stripe_account_id              = $stripe_connect_data['stripe_user_id'];
    $stripe_connect_access_token    = $stripe_connect_data['access_token'];
    $stripe_connect_publishable_key = $stripe_connect_data['stripe_publishable_key'];
     
    // Encrypt Stripe Connect Account ID & Stripe Connect Access Token $ Stripe Connect Publishable Key
    $encrypted_stripe_account_id              = espd_encrypt($stripe_account_id);
    $encrypted_stripe_connect_access_token    = espd_encrypt($stripe_connect_access_token);
    $encrypted_stripe_connect_publishable_key = espd_encrypt($stripe_connect_publishable_key);
    
    // Save in DB
    update_option( 'espad_stripe_account_id', $encrypted_stripe_account_id );
    update_option( 'espad_stripe_connect_access_token', $encrypted_stripe_connect_access_token );
    update_option( 'espad_stripe_connect_publishable_key', $encrypted_stripe_connect_publishable_key );
     
	// Success sweetAlert message
    echo '<div id="espad-stripe-connect-is-successful"></div>'; 
    
}

// Decrypt Stripe Keys from DB 
$stored_public_key = get_option( 'espd_stripe_public_key', '' );
$stored_secret_key = get_option( 'espd_stripe_secret_key', '' );

$stripe_public_key = $stored_public_key ? espd_decrypt( $stored_public_key ) : '';
$stripe_secret_key = $stored_secret_key ? espd_decrypt( $stored_secret_key ) : '';
 
// Send formular: Save encrypted in the DB
if ( isset( $_POST['espd_settings_submit'] ) && check_admin_referer( 'espd_save_settings' ) ) {
    
    $stripe_public_key = isset($_POST['espd_stripe_public_key']) ? sanitize_text_field( wp_unslash($_POST['espd_stripe_public_key']) ) : '';
    $stripe_secret_key = isset($_POST['espd_stripe_secret_key']) ? sanitize_text_field( wp_unslash($_POST['espd_stripe_secret_key']) ) : '';
    
    update_option( 'espd_stripe_public_key', espd_encrypt( $stripe_public_key ) );
    update_option( 'espd_stripe_secret_key', espd_encrypt( $stripe_secret_key ) );    

    echo '<div class="updated notice is-dismissible"><p>' . esc_html(__( 'Settings saved.', 'easy-stripe-payments' )) . '</p></div>';
     
}

function check_stripe_connect_connection($stripe_user_id, $access_token) {
    
    try {
        
        $stripe = new \Stripe\StripeClient($access_token);

        $account = $stripe->accounts->retrieve();

        if ( $account && isset($account->id) && $account->id === $stripe_user_id ) {
            return true;
        }

        return false;

    } catch (\Stripe\Exception\AuthenticationException $e) {
        return 'Authentication error: ' . $e->getMessage();
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        return 'Error Connection: ' . $e->getMessage();
    } catch (\Stripe\Exception\ApiErrorException $e) {
        return 'Stripe API error: ' . $e->getMessage();
    } catch (\Exception $e) {
        return 'An error has occurred: ' . $e->getMessage();
    }
    
}
 
// Test the Stripe connection
$encrypted_stripe_account = get_option('espad_stripe_account_id');
$encrypted_access_token   = get_option('espad_stripe_connect_access_token');

// Decrypt
$stripe_connect_account_id   = espd_decrypt( $encrypted_stripe_account );
$stripe_connect_access_token = espd_decrypt( $encrypted_access_token );
 
$result = check_stripe_connect_connection($stripe_connect_account_id, $stripe_connect_access_token);
 
if ( $result === true ) {
    
    ?>
    
    <div class="notice notice-success is-dismissible">
        <p>✅ <?php echo esc_html(__( 'Successfully connected to Stripe', 'easy-stripe-payments' )); ?> &#127942;</p>
    </div>
    
    <?php    
    
} else {
      
    echo '<div class="notice notice-info is-dismissible"><p>';
        echo esc_html(__( 'Please connect your Stripe account to start accepting payments.', 'easy-stripe-payments' ));
    echo '</p></div>';   
    
}


?>

<h2 style="margin-bottom: 5px;"><?php echo esc_html(__( 'Stripe Quick Connect', 'easy-stripe-payments' )); ?> <span class="dashicons dashicons-lock" style="font-size: 34px; vertical-align: middle; margin-top: -28px; color: #46b450;"></span></h2>

<?php 

$securely_stripe_connect_text = __( 'Securely connect your Stripe account<br /><br />', 'easy-stripe-payments' );

echo wp_kses_post($securely_stripe_connect_text);   
 
// //////////////////////
// Stripe Connect - TEST
// //////////////////////
$client_id_test = "ca_U5KrziWngC7dOn1pF6x6rFAVd5KWmXlc"; 

$redirect_uri_test = "https://api.ecosys365.com/stripe-connect-callback";

$espad_nonce_test = wp_create_nonce('espad_stripe_connect');
  
$state_data_test = [
    'redirect_wp_admin' => ESPAD_CURRENT_URL,
	'wp_domain'         => ESPAD_DOMAIN,
	'wp_site_url'       => ESPAD_SITE_URL,
	'nonce'             => $espad_nonce_test,
    'espad_code'        => $espad_code
];
 
$state_test = urlencode(base64_encode(json_encode($state_data_test)));

$oauth_url_test = "https://connect.stripe.com/oauth/authorize?response_type=code&client_id={$client_id_test}&scope=read_write&redirect_uri={$redirect_uri_test}&state={$state_test}";
 
// //////////////////////
// Stripe Connect - LIVE
// //////////////////////
$client_id = "ca_UCTYCUgs4LYjMCpwzpOaEXSy82WBFggT"; 

$redirect_uri = "https://api.ecosys365.com/stripe-connect-callback-live";
  
$espad_nonce = wp_create_nonce('espad_stripe_connect');
  
$state_data = [
    'redirect_wp_admin' => ESPAD_CURRENT_URL,
	'wp_domain'         => ESPAD_DOMAIN,
	'wp_site_url'       => ESPAD_SITE_URL,
	'nonce'             => $espad_nonce,
    'espad_code'        => $espad_code
];
 
$state = urlencode(base64_encode(json_encode($state_data)));

$oauth_url = "https://connect.stripe.com/oauth/authorize?response_type=code&client_id={$client_id}&scope=read_write&redirect_uri={$redirect_uri}&state={$state}";

?>	 

<a href="<?php echo esc_url($oauth_url); ?>" class="custom-stripe-btn">
    <?php echo esc_html__( 'Connect with Stripe (Live)', 'easy-stripe-payments' ); ?>
</a>
 
<a href="<?php echo esc_url($oauth_url_test); ?>" class="custom-stripe-btn custom-stripe-btn-test">
    <?php echo esc_html__( 'Connect with Stripe (Test)', 'easy-stripe-payments' ); ?>
</a>

<?php            

$powered_by_ecosys365_text = __( '<br /><small>Powered by</small><br />EcoSys 365 Solutions LLC', 'easy-stripe-payments' );

echo wp_kses_post($powered_by_ecosys365_text);

echo "<br /><br />";

echo wp_kses(
	espad_plugin_image('assets/images/ecosys365-stripe.jpg', 'EcoSys365 Stripe', ['class' => 'ecosys365-stripe', 'width' => 382]),
    ESPAD_ALLOWED_IMG_TAGS
); 

?>

<br /><br /><br /><br /><br />

<details class="espd-more-settings">
	
    <summary style="cursor: pointer; color: #2271b1; text-decoration: underline; margin-bottom: 15px;">
        <?php echo esc_html(__( 'Advanced Settings (Manual API)', 'easy-stripe-payments' )); ?>
    </summary>

    <div class="espd-settings-content" style="margin-top: 20px; border-top: 1px solid #fff; padding-top: 10px;">
        <h2><?php echo esc_html(__( 'Stripe API Keys', 'easy-stripe-payments' )); ?></h2>

        <form method="post" action="">
			
    		<?php wp_nonce_field( 'espd_save_settings' ); ?>

    		<table class="form-table">
        		<tr valign="top">
            		<th scope="row"><?php echo esc_html(__( 'Stripe Public Key', 'easy-stripe-payments' )); ?></th>
            		<td><input type="text" name="espd_stripe_public_key" placeholder="**********" class="regular-text" required /></td>
        		</tr>
        		<tr valign="top">
            		<th scope="row"><?php echo esc_html(__( 'Stripe Secret Key', 'easy-stripe-payments' )); ?></th>
            		<td><input type="text" name="espd_stripe_secret_key" placeholder="**********" class="regular-text" required /></td>
        		</tr>
    		</table>

    		<p><input type="submit" name="espd_settings_submit" class="button-primary" value="<?php echo esc_html(__( 'Save', 'easy-stripe-payments' )); ?>" /></p>
			
        </form>
		
    </div>
	
</details>
