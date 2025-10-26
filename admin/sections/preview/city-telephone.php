<?php defined( 'ABSPATH' ) || exit; ?>

<div class="row mb-3">

    <div class="col-md">
        <div class="form-floating">                

            <input id="city" name="city" type="name" class="form-control" placeholder="" <?php echo esc_html( $required_fields_string ); ?>>
            <label for="city" class="f-15"><?php echo esc_html(__( 'City', 'easy-stripe-payments' )); ?></label>

        </div>
    </div> 

    <div class="col-md">
        <div class="form-floating">                

            <input id="phone_number" name="phone_number" type="name" class="form-control" placeholder="" <?php echo esc_html($required_fields_string); ?>>
            <label for="phone_number" class="f-15"><?php echo esc_html(__( 'Phone number', 'easy-stripe-payments' )); ?></label>                                                 

        </div>
    </div>                     

</div>