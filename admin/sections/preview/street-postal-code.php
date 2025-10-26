<?php defined( 'ABSPATH' ) || exit; ?>

<div class="row mb-3">
 
    <div class="col-md">
        <div class="form-floating">                

            <input id="street" name="street" type="name" class="form-control" placeholder="" <?php echo esc_html($required_fields_string); ?>>
            <label for="street" class="f-15"><?php echo esc_html(__( 'Street &amp; No.', 'easy-stripe-payments' )); ?></label>

        </div>
    </div> 

    <div class="col-md">
        <div class="form-floating">                

            <input id="postal_code" name="postal_code" type="name" class="form-control" placeholder="" <?php echo esc_html($required_fields_string); ?>>
            <label for="postal_code" class="f-15"><?php echo esc_html(__( 'Postal code', 'easy-stripe-payments' )); ?></label>

        </div>
    </div>                    

</div> 