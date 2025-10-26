<?php defined( 'ABSPATH' ) || exit; ?>

<!-- Ladeanimation -->
<div id="espad-loading-overlay">
  <div class="loader"></div>
</div>

<?php espd_domain_is_not_registered(); ?>
  
<?php 
 
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameters used for admin UI tabs only, no sensitive action performed.
if ( isset($_GET['membership_is_false']) && $_GET['membership_is_false'] === 'true' ) : 

    echo "<div id='membership-forms-is-false'></div>";
        
endif;    

$membership_status = get_current_membership_status();

if ( $membership_status != "1" ) $premium_field = 'readonly';
else $premium_field = '';

global $wpdb;

$table = $wpdb->prefix . 'espad_forms';

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Intentionally used for a custom table
$forms = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Intentionally used for a custom table
$db_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );

$premium_tooltip = __( "Premium members gain access to advanced features such as priority support and enhanced Stripe payment workflows, while all core functionality remains freely available.", "easy-stripe-payments" );

?>

<div class="wrap">
    
    <h3><?php echo esc_html(__( 'Payment Forms', 'easy-stripe-payments' )); ?> &#128221;</h3>
    
    <?php if ( $db_count >= 2 && $membership_status != "1" ) { ?>
    
        <a href="<?php echo esc_url( add_query_arg( 'membership_is_false', 'true', ESPAD_CURRENT_URL ) ); ?>" class="button button-primary">
            <?php echo esc_html(__( 'Add Standard Checkout', 'easy-stripe-payments' )); ?>
        </a>    
    
        <a href="<?php echo esc_url( add_query_arg( 'membership_is_false', 'true', ESPAD_CURRENT_URL ) ); ?>" class="button button-primary">
            <?php echo esc_html(__( 'Add Campaign Checkout', 'easy-stripe-payments' )); ?>
        </a>
    
    <?php } else { ?>
    
        <button id="espd-create-form" class="button button-primary"><?php echo esc_html(__( 'Add Standard Checkout', 'easy-stripe-payments' )); ?></button>

        <button id="espd-create-campaign-form" class="button button-primary"><?php echo esc_html(__( 'Add Campaign Checkout', 'easy-stripe-payments' )); ?></button>  
    
    <?php } ?>

    <div id="espd-form-modal" style="display:none;">
        
        <?php require_once ESPAD_PLUGIN_PATH . 'admin/sections/standard-checkout.php'; ?>
        
    </div>
    
    <div id="espd-form-campaign-modal" style="display:none;">
        
        <?php require_once ESPAD_PLUGIN_PATH . 'admin/sections/campaign-checkout.php'; ?>
        
    </div>    
    
</div>


<div class="wrap_box">

    <table id="espad-table" class="widefat striped" style="margin-top:20px;">
        <thead>
            <tr>
                <th><?php echo esc_html(__( 'ID', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Form Name', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Mode', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Fix Amount', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Currency', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Description', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Shortcode', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Edit', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Delete', 'easy-stripe-payments' )); ?></th>                
                <th><?php echo esc_html(__( 'Preview', 'easy-stripe-payments' )); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($forms): ?>
                <?php foreach ($forms as $form): ?>
                    <tr 
                        data-form-name="<?php echo esc_html($form->form_name); ?>"
                        data-form-mode="<?php echo esc_html($form->mode); ?>">
                        <td><?php echo esc_html($form->id); ?></td>
                        <td><?php echo esc_html($form->form_name); ?></td>
                        <td><?php echo esc_html($form->mode); ?></td>
                        <td><?php echo esc_html($form->fix_amount); ?></td>
                        <td><?php echo esc_html($form->currency); ?></td>
                        <td title="<?php echo esc_html($form->description); ?>">
                            <?php
                                $words = explode(' ', wp_strip_all_tags($form->description));
                                $short = implode(' ', array_slice($words, 0, 3));
                                echo esc_html(count($words) > 3 ? $short . ' ...' : $short);
                            ?>                        
                        </td>
                        <td>
                            <code id="shortcode-<?php echo esc_html($form->id); ?>">[espad_payment_form id="<?php echo esc_html($form->id); ?>"]</code>
                            <button class="button copy-button" data-target="shortcode-<?php echo esc_html($form->id); ?>"><?php echo esc_html(__( 'Copy', 'easy-stripe-payments' )); ?></button>
                        </td>
                        <td>
                            <button 
                                    class="button edit-button" 
                                    data-id="<?php echo esc_html($form->id); ?>"
                                    data-mode="<?php echo esc_html($form->mode); ?>">
                                &#9998; <?php echo esc_html(__( 'Edit', 'easy-stripe-payments' )); ?>
                            </button>                        
                        </td>
                        <td>
                            <button 
                                    class="button delete-button" 
                                    data-id="<?php echo esc_html($form->id); ?>" 
                                    style="color: #b32d2e;">
                                &#128465; <?php echo esc_html(__( 'Delete', 'easy-stripe-payments' )); ?>
                            </button>                        
                        </td>                        
                        <td>
                            <button 
                                    class="button preview-button" 
                                    data-id="<?php echo esc_html($form->id); ?>">
                                &#128270; <?php echo esc_html(__( 'Preview', 'easy-stripe-payments' )); ?>
                            </button>                              
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4"><?php echo esc_html(__( 'No forms have been created yet.', 'easy-stripe-payments' )); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


