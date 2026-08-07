<?php 

defined( 'ABSPATH' ) || exit; 

$languages = require ESPAD_PLUGIN_PATH . 'inc/data/languages.php'; 

?>

<select  
    id="form_language" 
    name="form_language" 
    class="form-control has-tooltip"
    data-tooltip="<?php echo esc_html__( 'Select a language for the Checkout Form', 'easy-stripe-payments' ); ?>"
    required>

    <?php foreach ( $languages['primary'] as $code => $label ) : ?>

        <option value="<?php echo esc_attr( $code ); ?>">
            <?php echo esc_html( $label ); ?>
        </option>

    <?php endforeach; ?>

    <option disabled>──────────</option>

    <?php foreach ( $languages['additional'] as $code => $label ) : ?>

        <option value="<?php echo esc_attr( $code ); ?>">
            <?php echo esc_html( $label ); ?>
        </option>

    <?php endforeach; ?>

</select>