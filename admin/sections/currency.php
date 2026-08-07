<?php 

defined( 'ABSPATH' ) || exit; 

$tooltip_currencies = __( "These currencies are supported by Stripe.\nPlease ensure that the selected currency is available and properly configured in your Stripe account, depending on your region and payout settings.", "easy-stripe-payments" );

$currencies = require ESPAD_PLUGIN_PATH . 'inc/data/currencies.php';

?>

<select
    id="currency"
    name="currency"
    class="form-control has-tooltip"
    data-tooltip="<?php echo esc_html( $tooltip_currencies ); ?>"
    data-offset-top="-55"
    required>

    <?php foreach ( $currencies['primary'] as $code => $label ) : ?>

        <option value="<?php echo esc_attr( $code ); ?>">
            <?php echo esc_html( $label ); ?>
        </option>

    <?php endforeach; ?>

    <option disabled>──────────</option>

    <?php foreach ( $currencies['additional'] as $code => $label ) : ?>

        <option value="<?php echo esc_attr( $code ); ?>">
            <?php echo esc_html( $label ); ?>
        </option>

    <?php endforeach; ?>

</select>