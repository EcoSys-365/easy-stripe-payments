<?php 
  
defined( 'ABSPATH' ) || exit; 

espad_check_first_visit();

espd_setup_steps_infobox(); 

espd_domain_is_not_registered(); 

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

function check_stripe_connection($stripe_public_key, $stripe_secret_key) {
    
    // Set the Secret Key
    \Stripe\Stripe::setApiKey($stripe_secret_key);

    try {
        // Send a test Stripe connection
        $account = \Stripe\Account::retrieve();  // Get account details
        
        // Check
        if ($account) {
            return true;  // Success connection
        } else {
            return false;  // Failed connection
        }
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        // Error Connection
        return 'Error Connection: ' . $e->getMessage();
    } catch (\Stripe\Exception\AuthenticationException $e) {
        // Authentication Error
        return 'Authentication error: ' . $e->getMessage();
    } catch (\Exception $e) {
        // General Error
        return 'An error has occurred: ' . $e->getMessage();
    }
    
}
 
// Test the connection
$result = check_stripe_connection($stripe_public_key, $stripe_secret_key);

if ( $result === true ) {
    
    ?>
    
    <div class="notice notice-success is-dismissible">
        <p>✅ <?php echo esc_html(__( 'Successfully connected to Stripe', 'easy-stripe-payments' )); ?> &#127942;</p>
    </div>
    
    <?php    
    
} else if ( $stripe_public_key != "" ) {
    
    $error_message = '<div class="notice notice-error is-dismissible"><p>❌ <strong>Error:</strong> ' . esc_html( $result ) . '</p></div>';
    
    echo wp_kses_post($error_message);
     
} else {
     
    echo '<div class="notice notice-info is-dismissible"><p>';
        echo esc_html(__( 'Please enter your Stripe Public Key and Stripe Secret Key', 'easy-stripe-payments' ));
    echo '</p></div>';   
    
}


?>

<h2><?php echo esc_html(__( 'Stripe Settings', 'easy-stripe-payments' )); ?></h2>

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
