<?php defined( 'ABSPATH' ) || exit; ?>

<?php
$builder_action = isset( $_GET['builder_action'] )
    ? sanitize_key( wp_unslash( $_GET['builder_action'] ) )
    : 'new';

$checkout_id = isset( $_GET['checkout_id'] )
    ? absint( $_GET['checkout_id'] )
    : 0;
?>

<div class="wrap">
    <div
        id="espad-multistep-builder"
        data-builder-action="<?php echo esc_attr( $builder_action ); ?>"
        data-checkout-id="<?php echo esc_attr( $checkout_id ); ?>">
    </div>
</div>