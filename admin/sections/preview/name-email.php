<?php defined( 'ABSPATH' ) || exit; ?>

<div class="row mb-3">

    <div class="col-md">
        <div class="form-floating">                

            <input id="name" name="name" type="name" class="form-control" placeholder="" required>
            <label for="name" class="f-15"><?php echo esc_html(__( 'Full name', 'easy-stripe-payments' )); ?></label>

        </div>

    </div> 

    <div class="col-md">
        <div class="form-floating">                

            <input id="email" name="email" type="email" class="form-control" placeholder="" required>
            <label for="email" class="f-15"><?php echo esc_html(__( 'Email', 'easy-stripe-payments' )); ?></label>

        </div>

    </div>                     

</div> 