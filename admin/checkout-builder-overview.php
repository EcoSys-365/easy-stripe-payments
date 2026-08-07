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
$forms = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $table WHERE mode = %s ORDER BY created_at DESC",
        'Multistep'
    )
);

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Intentionally used for a custom table
$db_count = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE mode = %s",
        'Multistep'
    )
);

$premium_tooltip = __( "Premium members gain access to advanced features such as priority support and enhanced Stripe payment workflows, while all core functionality remains freely available.", "easy-stripe-payments" );

$tooltips = [
    'multistep'     => __('Create fully customized Multi-Step Checkout Forms with drag-and-drop fields, flexible layouts, and Stripe-powered payments.', 'easy-stripe-payments'),
];

?>

<div class="wrap">

    <div class="espad-preview-overlay">

        <div class="espad-preview-overlay__title">

            <?php echo esc_html__( 'Live Demos', 'easy-stripe-payments' ); ?>

            <button type="button" class="espad-toggle">
                <span class="dashicons dashicons-arrow-down-alt2"></span>
            </button>                

        </div>

        <div class="espad-preview-overlay__content">
            <p>
                <?php echo esc_html__( 'Explore our live Stripe Checkout demos', 'easy-stripe-payments' ); ?>
            </p>

            <a 
                href="<?php echo esc_url( 'https://demo.ecosys365.com' ); ?>" 
                target="_blank" 
                rel="noopener noreferrer">
                <?php echo esc_html__( 'Open Live Demos', 'easy-stripe-payments' ); ?>
            </a>

        </div>

    </div>    
    
    <h3><?php echo esc_html(__( 'Multi-Step Checkouts', 'easy-stripe-payments' )); ?> &#129513;</h3>
     
    <?php if ( $db_count >= 2 && $membership_status != "1" ) { ?>
    
        <a 
           href="<?php echo esc_url( add_query_arg( 'membership_is_false', 'true', ESPAD_CURRENT_URL ) ); ?>" 
           class="button button-primary has-tooltip"
           data-tooltip="<?php echo esc_html($tooltips['multistep']); ?>">
            <?php echo esc_html(__( 'Build New Checkout', 'easy-stripe-payments' )); ?>
        </a>    
    
    <?php } else { ?>
    
        <a 
            href="<?php echo esc_url( admin_url( 'admin.php?page=espd_main&tab=checkout-builder&builder_action=new' ) ); ?>"
            class="button button-primary has-tooltip"
            data-tooltip="<?php echo esc_attr( $tooltips['multistep'] ); ?>">
            <?php echo esc_html__( 'Build New Checkout', 'easy-stripe-payments' ); ?>
        </a>    
    
    <?php } ?>     
     
</div>


<div class="wrap_box">

    <table id="espad-table" class="widefat striped" style="margin-top:20px;">
        <thead>
            <tr>
                <th><?php echo esc_html(__( 'ID', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Name', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Checkout Flow', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Currency', 'easy-stripe-payments' )); ?></th>
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
                        <td>
                            <?php
                                $checkout_metadata = json_decode( $form->checkout_metadata_1, true );

                                if (
                                    is_array( $checkout_metadata ) &&
                                    ! empty( $checkout_metadata['steps'] ) &&
                                    is_array( $checkout_metadata['steps'] )
                                ) {
                                    echo '<ol class="espad-checkout-steps-list">';

                                    foreach ( $checkout_metadata['steps'] as $step ) {
                                        $step_title = ! empty( $step['title'] )
                                            ? $step['title']
                                            : __( 'Untitled step', 'easy-stripe-payments' );

                                        $field_count = 0;

                                        if ( ! empty( $step['fields'] ) && is_array( $step['fields'] ) ) {
                                            $field_count = count( $step['fields'] );
                                        }

                                        echo '<li>';
                                        echo esc_html( $step_title );
                                        echo ' <span class="description">(' . esc_html( $field_count ) . ' ';
                                        echo esc_html( _n( 'field', 'fields', $field_count, 'easy-stripe-payments' ) );
                                        echo ')</span>';
                                        echo '</li>';
                                    }

                                    echo '</ol>';
                                } else {
                                    echo '<span class="description">';
                                    echo esc_html__( 'No steps found', 'easy-stripe-payments' );
                                    echo '</span>';
                                }
                            ?>                        
                        </td>
                        <td><?php echo esc_html($form->currency); ?></td>
                        <td>
                            <code id="shortcode-<?php echo esc_html($form->id); ?>">[espad_multistep_checkout id="<?php echo esc_html($form->id); ?>"]</code>
                            <button class="button copy-button" data-target="shortcode-<?php echo esc_html($form->id); ?>"><?php echo esc_html(__( 'Copy', 'easy-stripe-payments' )); ?></button>
                        </td>
                        <td>
                            <a 
                                href="<?php echo esc_url( admin_url( 'admin.php?page=espd_main&tab=checkout-builder&builder_action=edit&checkout_id=' . absint( $form->id ) ) ); ?>"
                                class="button">
                                &#9998; <?php echo esc_html__( 'Edit', 'easy-stripe-payments' ); ?>
                            </a>                            
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


