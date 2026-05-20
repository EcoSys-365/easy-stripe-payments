<?php defined( 'ABSPATH' ) || exit; ?> 

<h2 class="welcome_headline">
    <?php echo esc_html(__( 'Stripe overview', 'easy-stripe-payments' )); ?> &#10024; 
    <span 
          class = "espad-info-box-icon has-tooltip"
          data-tooltip = "<?php echo esc_html( $tooltip_text_stripe ); ?>"
          data-offset-top = "-80">
        <?php 
            echo wp_kses(
                espad_plugin_image('assets/images/info_icon.png', 'Info Icon', ['class' => 'info-icon', 'width' => 24]),
                ESPAD_ALLOWED_IMG_TAGS
            );                
        ?>
    </span>
</h2>

<?php

global $wpdb;

$total_payments = $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->prefix}espad_payments"
);

$total_revenue = $wpdb->get_results(
    "SELECT currency, SUM(CAST(amount AS DECIMAL(10,2))) as total
     FROM {$wpdb->prefix}espad_payments
     GROUP BY currency"
);

?>

<div class="espad-wrapper">
    <div class="espad-column">

        <div class="balance-box">
            <div class="balance-title"><?php echo esc_html(__( 'Total Payments', 'easy-stripe-payments' )); ?></div>
            <div class="balance-amount"><?php echo esc_html($total_payments); ?></div>
        </div>

        <div class="balance-box">
            <div class="balance-title"><?php echo esc_html(__( 'Total Revenue', 'easy-stripe-payments' )); ?></div>
            <div class="balance-amount">
                <?php foreach ( $total_revenue as $revenue ) : ?>

                <span>
                    <?php echo esc_html(number_format( (float) $revenue->total, 2 ) . ' ' . strtoupper( $revenue->currency )); ?>
                </span>

                <?php endforeach; ?>        
            </div>
        </div>

    </div>
</div>
