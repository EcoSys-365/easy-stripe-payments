<?php defined( 'ABSPATH' ) || exit; ?>

<!-- Ladeanimation -->
<div id="espad-loading-overlay">
  <div class="loader"></div>
</div>

<?php espad_check_first_visit(); ?>
 
<h2><?php echo esc_html(__( 'Payments', 'easy-stripe-payments' )); ?> &#128176;</h2> 

<?php

global $wpdb;
 
$table = esc_sql( $wpdb->prefix . 'espad_payments' );

$sql   = "SELECT * FROM {$table} ORDER BY created_at DESC";

$sql_count = "SELECT COUNT(*) FROM {$table}";

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- Intentionally used for a custom table
$forms = $wpdb->get_results( $sql ); 
  
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- Intentionally used for a custom table
$entry_count = (int) $wpdb->get_var( $sql_count ); 
 
if ( defined('ESPAD_STRIPE_ACCESS') && ESPAD_STRIPE_ACCESS && $entry_count > 0 ) { 
    

} else { 
     
    printf(
        '<i>%s</i>',
        esc_html__( 'There are no payments at this time.', 'easy-stripe-payments' )
    );

    return;

} 

?>

<div class="wrap_box table_stripe_payments_box">

    <div class="export-button-container">
      <button id="export-csv" class="button button-primary"><?php echo esc_html(__( 'Export as CSV', 'easy-stripe-payments' )); ?></button>
    </div>

    <table id="espad-table-stripe-payments" class="widefat striped" style="margin-top:20px;">
        <thead>
            <tr>
                <th><?php echo esc_html(__( 'Name', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Amount', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Currency', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Mode', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Contact Details', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Payment Method', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Stripe Payment ID', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Stripe Metadata', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Checkout Form ID', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Creation Date', 'easy-stripe-payments' )); ?></th>
                <th><?php echo esc_html(__( 'Delete', 'easy-stripe-payments' )); ?></th>  
            </tr>
        </thead>
        <tbody>
            <?php if ($forms): ?>
                <?php foreach ($forms as $form): ?>
                    <tr class="contact-row" data-form-name="<?php echo esc_html($form->name); ?>">
                        <td><?php echo esc_html($form->name); ?></td>
                        <td><?php echo esc_html($form->amount); ?></td>
                        <td><?php echo esc_html($form->currency); ?></td>
                        <td><?php echo esc_html($form->mode); ?></td>
                        <td>  
                            <div class="contact-wrapper">
                                <div class="contact-content">
                                    
                                    <?php echo esc_html($form->email); ?><br />
                                    <?php echo esc_html($form->phone); ?><br />
                                    <?php echo esc_html($form->address_line); ?><br />
                                    <?php echo esc_html($form->address_line_2); ?><br />
                                    <?php echo esc_html($form->postal_code); ?> <?php echo esc_html($form->city); ?> <?php echo esc_html($form->country); ?>
                                    
                                </div>
                            </div>
                        </td>
                        <td><?php echo esc_html($form->payment_method_type); ?></td>
                        <td><?php echo esc_html($form->stripe_payment_id); ?></td>
                        <td>
                            <div class="contact-wrapper">
                                <div class="contact-content"> 
                                    
                                    <?php
                                    
                                        if ( $form->metadata_campaign != "" ) {
                                            echo esc_html(__( 'Campaign', 'easy-stripe-payments' ));
                                            echo "<br />";
                                            echo esc_html($form->metadata_campaign);
                                            echo "<br />";
                                        }
                                    
                                        if ( $form->metadata_project != "" ) {
                                            echo esc_html(__( 'Project', 'easy-stripe-payments' ));
                                            echo "<br />";
                                            echo esc_html($form->metadata_project);
                                            echo "<br />";
                                        }  
                                    
                                        if ( $form->metadata_product != "" ) {
                                            echo esc_html(__( 'Product', 'easy-stripe-payments' ));
                                            echo "<br />";
                                            echo esc_html($form->metadata_product);
                                        }
                                        
                                    ?>
                                    
                                </div>
                            </div>                                    
                        </td>
                        <td><?php echo esc_html($form->payment_form_id); ?></td>
                        <td><?php echo esc_html($form->created_at); ?></td>
                        <td>
                            <button class="button delete-button-payment" data-id="<?php echo esc_html($form->id); ?>" style="color: #b32d2e;">
                                &#128465; <?php echo esc_html(__( 'Delete', 'easy-stripe-payments' )); ?>
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

