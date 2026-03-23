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

<?php printf('<canvas id="payoutChart" width="600" height="300"></canvas>'); ?>
