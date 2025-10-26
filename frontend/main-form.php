<?php 

defined( 'ABSPATH' ) || exit;

/**
 * Loads and displays the main preview form on the frontend.
 *
 * Includes required backend data and renders the subscription payment form preview,
 * applying the selected display mode dynamically via CSS class.
 *
 * @package CustomStripeIntegration
 */
require_once ESPAD_PLUGIN_PATH . 'admin/sections/preview/form-db.php'; ?>

<div id="espad_page" class="preview_page prev-mode-<?php echo esc_attr($mode); ?> shortcode_payment_form">
 
    <?php require_once ESPAD_PLUGIN_PATH . 'admin/sections/preview/main-form.php'; ?>

</div>