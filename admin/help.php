<?php 
 
defined( 'ABSPATH' ) || exit; 

espad_check_first_visit();

global $wpdb;

$theme = wp_get_theme();
$is_child_theme = is_child_theme() ? 'Yes' : 'No';
$current_user = wp_get_current_user();
$upload_dir = wp_upload_dir();

$info = array(
    // Website & WordPress
    'Site URL'              => site_url(),
    'Home URL'              => home_url(),
    'Multisite Enabled'     => is_multisite() ? 'Yes' : 'No',
    'WordPress Version'     => get_bloginfo('version'),
    'Table Prefix'          => $wpdb->prefix,
 
    // Server & PHP
    'PHP Version'           => phpversion(),
    'MySQL Version'         => $wpdb->db_version(),
    'Server Software'       => isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : 'N/A',
    'Server Name'           => isset($_SERVER['SERVER_NAME']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])) : 'N/A',
    'Server Port'           => isset($_SERVER['SERVER_PORT']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_PORT'])) : 'N/A',
    'HTTPS Enabled'         => is_ssl() ? 'Yes' : 'No',
    'Max Execution Time'    => ini_get('max_execution_time') . ' sec',
    'Memory Limit (PHP)'    => ini_get('memory_limit'),
    'Upload Max Filesize'   => ini_get('upload_max_filesize'),
    'Post Max Size'         => ini_get('post_max_size'),
    'WP Memory Usage'       => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',

    // Theme
    'Active Theme'          => $theme->get('Name') . ' ' . $theme->get('Version'),
    'Child Theme'           => $is_child_theme,

    // User
    'Current User'          => $current_user->user_login,

    // Uploads
    'Uploads Directory'     => $upload_dir['basedir'],

    // Debug
    'WP Debug Mode'         => (defined('WP_DEBUG') && WP_DEBUG) ? 'Enabled' : 'Disabled',

    // Plugins
    'Active Plugins'        => '',
);

// Active Plugins
$active_plugins = get_option('active_plugins');
$plugins_output = "";

foreach ($active_plugins as $plugin_path) {
    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_path);
    $plugins_output .= $plugin_data['Name'] . ' ' . $plugin_data['Version'] . "\n";
}

$info['Active Plugins'] = trim($plugins_output);
?>

<?php espd_domain_is_not_registered(); ?>

<h2><?php echo esc_html(__('Help &amp; FAQ', 'easy-stripe-payments')); ?> &#10067;</h2>

<p>
    <?php echo esc_html(__('Please copy and paste the following system information into your support email to', 'easy-stripe-payments')); ?> 
    <a href="mailto:support@payments-and-donations.com">support@payments-and-donations.com</a>. 
    <?php echo esc_html(__('This helps us troubleshoot your issue faster.', 'easy-stripe-payments')); ?>
</p>

<button id="copySystemInfo" class="button"><?php echo esc_html(__('Copy to Clipboard', 'easy-stripe-payments')); ?></button>

<textarea id="systemInfoText" readonly rows="7" style="width: 100%; font-family: monospace; margin-top: 10px;"><?php
foreach ($info as $label => $value) {
    echo esc_html( $label . ': ' . $value ) . "\n";
} 
?></textarea>

<div class="espd-faq-accordion">
    <div class="espd-faq-item open">
        <div class="espd-faq-question"><?php echo esc_html(__('How do I connect my Stripe account?', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('Go to the plugin settings and enter your Stripe Public Key and Stripe Secret Key.', 'easy-stripe-payments')); ?>
        </div>
    </div>
    <div class="espd-faq-item open">
        <div class="espd-faq-question"><?php echo esc_html(__('Test Payments ( Stripe Sandbox )', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('You can test credit card payments using the following test data:', 'easy-stripe-payments')); ?>
            <br /><br /><?php echo esc_html(__('Card Number:', 'easy-stripe-payments')); ?> <b>4242 4242 4242 4242</b>
            <br /><?php echo esc_html(__('Expiration date:', 'easy-stripe-payments')); ?> <b>01-29</b> <i style="color: #777;"><?php echo esc_html(__('future valid date', 'easy-stripe-payments')); ?></i>
            <br /><?php echo esc_html(__('CVC:', 'easy-stripe-payments')); ?> <b>123</b> <i style="color: #777;"><?php echo esc_html(__('or any number', 'easy-stripe-payments')); ?></i>
            <br /><br /><a href="https://docs.stripe.com/testing" target="_blank"><?php echo esc_html(__('Stripe Test Card Numbers', 'easy-stripe-payments')); ?></a>
        </div>
    </div>     
    <div class="espd-faq-item">
        <div class="espd-faq-question"><?php echo esc_html(__('How are sensitive Stripe API keys stored in this plugin?', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('Your Stripe keys are securely encrypted and stored in your own WordPress database, on the site where the plugin is installed. At no point are these keys shared with any third party.', 'easy-stripe-payments')); ?>
            <br /><br />
            <?php echo esc_html(__('Security and trust are extremely important to us. We understand the responsibility that comes with handling sensitive data. That’s why we’ve built the plugin in a way that ensures unauthorized access is not possible.', 'easy-stripe-payments')); ?> 
            <br /><br />
            <?php echo esc_html(__('All settings and credentials exist strictly within the plugin’s own namespace, following best practices in the WordPress ecosystem to prevent interference or data leaks.', 'easy-stripe-payments')); ?>             
        </div>
    </div>        
    <div class="espd-faq-item">
        <div class="espd-faq-question"><?php echo esc_html(__('How do I receive payments?', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('Payments are deposited directly into your Stripe account. You can manage payouts in your Stripe Dashboard.', 'easy-stripe-payments')); ?>
        </div>
    </div>
    <div class="espd-faq-item">
        <div class="espd-faq-question"><?php echo esc_html(__('Do you charge any Transaction Fees?', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('No. Our plugin does not charge any transaction fees or hidden costs. However, please note that Stripe & other Payment Gateways like PayPal apply their own processing fees.', 'easy-stripe-payments')); ?>
        </div>
    </div>    
    <div class="espd-faq-item">
        <div class="espd-faq-question"><?php echo esc_html(__('Can I use test mode and live mode at the same time?', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('No, you can only have one mode active at a time — either test or live. You can switch the mode using the Stripe Public and Secret Keys in the plugin settings.', 'easy-stripe-payments')); ?>
        </div>
    </div>
    <div class="espd-faq-item">
        <div class="espd-faq-question"><?php echo esc_html(__('Which currencies are supported?', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('The plugin supports all currencies supported by your ', 'easy-stripe-payments')); ?>
            <a href="https://docs.stripe.com/currencies" target="_blank">Stripe</a>
            <?php echo esc_html(__('account. Please note that your connected bank account must also be compatible with the currency.', 'easy-stripe-payments')); ?>
        </div>
    </div>
    <div class="espd-faq-item">
        <div class="espd-faq-question"><?php echo esc_html(__('Can I create recurring payments ( Subscriptions )?', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('Yes, the plugin supports Stripe subscriptions. You can configure these ( Recurring Payments ) via product settings with intervals like "monthly" or "yearly."', 'easy-stripe-payments')); ?>
        </div>
    </div>  
    <div class="espd-faq-item">
        <div class="espd-faq-question"><?php echo esc_html(__('How secure is the plugin?', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('All payments are processed directly through the Stripe API. The plugin does not store any sensitive payment data on your server. Additionally, recommended WordPress security measures like nonces, sanitizing and prepared statements are implemented.', 'easy-stripe-payments')); ?>
        </div>
    </div>  
    <div class="espd-faq-item">
        <div class="espd-faq-question"><?php echo esc_html(__('What happens if I uninstall the plugin?', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('On normal deactivation, your Stripe connections and settings – including your encrypted API keys – remain stored in the database. A complete deletion via the plugin overview page will remove all plugin data.', 'easy-stripe-payments')); ?>
        </div>
    </div>  
    <div class="espd-faq-item">
        <div class="espd-faq-question"><?php echo esc_html(__('How can I issue refunds?', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('Refunds must be made directly through the Stripe dashboard. The plugin currently does not offer an in-plugin refund feature.', 'easy-stripe-payments')); ?>
        </div>
    </div> 
    <div class="espd-faq-item">
        <div class="espd-faq-question"><?php echo esc_html(__('How do I troubleshoot payment failures?', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('Check your Stripe dashboard for detailed error messages. Common reasons include invalid API keys, currency mismatches, or card declines. Make sure your keys are correct and the plugin is in the correct mode ( Test / Live ).', 'easy-stripe-payments')); ?>
        </div>
    </div> 
    <div class="espd-faq-item">
        <div class="espd-faq-question"><?php echo esc_html(__('Can I customize the Checkout payment form?', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('Yes, the plugin offers options to customize labels, button texts, payment types, images, campaigns, appearance, Stripe Metadata etc.', 'easy-stripe-payments')); ?>
        </div>
    </div> 
    <div class="espd-faq-item">
        <div class="espd-faq-question"><?php echo esc_html(__('Is PCI compliance handled by the plugin?', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('Stripe handles all sensitive payment data, so PCI compliance burden on your server is minimal. However, you should still follow best practices for website security.', 'easy-stripe-payments')); ?>
        </div>
    </div> 
    <div class="espd-faq-item">
        <div class="espd-faq-question"><?php echo esc_html(__('Can I accept payments in multiple currencies?', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('You can accept payments in any currency supported by your Stripe account. Ensure your products and pricing are set up accordingly.', 'easy-stripe-payments')); ?>
        </div>
    </div>  
    <div class="espd-faq-item">
        <div class="espd-faq-question"><?php echo esc_html(__('Does the plugin support Apple Pay, Google Pay, PayPal, Alipay etc.?', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('Yes, plugin users can manage all payment methods directly through their Stripe Dashboard and these will be automatically displayed on the website’s checkout form.', 'easy-stripe-payments')); ?>
            <br /><br />
            <a href="https://stripe.com/payments/payment-methods" target="_blank"><?php echo esc_html(__('All Stripe Payment Methods', 'easy-stripe-payments')); ?></a>
        </div>
    </div>   
    <div class="espd-faq-item">
        <div class="espd-faq-question"><?php echo esc_html(__('Can I connect the plugin to multiple different Stripe accounts?', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('Yes, that is possible. However, we recommend fully deleting the plugin using the delete function on the plugin overview page before connecting it to a different Stripe account. Otherwise, inconsistencies with the database (payments, subscription buttons, etc.) may occur.', 'easy-stripe-payments')); ?>
        </div>
    </div> 
    <div class="espd-faq-item">
        <div class="espd-faq-question"><?php echo esc_html(__('Can my Premium Membership be revoked under certain conditions?', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('Yes. We reserve the right to terminate a premium membership without refund if a website using our plugin contains illegal, prohibited or content we deem morally unacceptable.', 'easy-stripe-payments')); ?>
            <br /><br />
            <?php echo esc_html(__('We do not support the use of our plugin on websites that promote hate, violence, discrimination or any content that violates applicable laws or our ethical standards. If such content is detected, we may suspend or revoke access to premium features at any time, without prior notice and without a refund.', 'easy-stripe-payments')); ?>      
            <br /><br />
            <?php echo esc_html(__('We encourage all users to ensure that their websites align with legal standards and basic ethical principles. If you are unsure whether your content meets these guidelines, feel free to contact us in advance.', 'easy-stripe-payments')); ?>            
        </div>
    </div>     
    <div class="espd-faq-item">
        <div class="espd-faq-question"><?php echo esc_html(__('Do you, as the plugin developers, assume liability for payments or the use of the plugin?', 'easy-stripe-payments')); ?></div>
        <div class="espd-faq-answer">
            <?php echo esc_html(__('No. We accept no liability for any damages, outages or losses that may arise from the use of this plugin. The use of the plugin is at your own risk. You are solely responsible for correctly configuring your Stripe settings, monitoring payments and complying with applicable legal and tax regulations. We provide the plugin "as is" without any warranties or guarantees.', 'easy-stripe-payments')); ?>
        </div>
    </div>     
</div>

