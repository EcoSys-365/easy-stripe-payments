<?php defined( 'ABSPATH' ) || exit; ?>

<div class="row mb-3">

    <div class="col-md">
        <div class="form-floating">
             
            <select id="country" name="country" class="form-select" placeholder="" <?php echo esc_html($required_fields_string); ?>>
                <option value=""></option>

                <option value="US"><?php echo esc_html(__( 'United States', 'easy-stripe-payments' )); ?></option>
                <option value="CA"><?php echo esc_html(__( 'Canada', 'easy-stripe-payments' )); ?></option>
                <option value="GB"><?php echo esc_html(__( 'United Kingdom', 'easy-stripe-payments' )); ?></option>
                <option value="AU"><?php echo esc_html(__( 'Australia', 'easy-stripe-payments' )); ?></option>
                <option value="NZ"><?php echo esc_html(__( 'New Zealand', 'easy-stripe-payments' )); ?></option>

                <option value="DE"><?php echo esc_html(__( 'Germany', 'easy-stripe-payments' )); ?></option>
                <option value="AT"><?php echo esc_html(__( 'Austria', 'easy-stripe-payments' )); ?></option>
                <option value="CH"><?php echo esc_html(__( 'Switzerland', 'easy-stripe-payments' )); ?></option>
                <option value="FR"><?php echo esc_html(__( 'France', 'easy-stripe-payments' )); ?></option>
                <option value="ES"><?php echo esc_html(__( 'Spain', 'easy-stripe-payments' )); ?></option>
                <option value="IT"><?php echo esc_html(__( 'Italy', 'easy-stripe-payments' )); ?></option>
                <option value="PT"><?php echo esc_html(__( 'Portugal', 'easy-stripe-payments' )); ?></option>
                <option value="NL"><?php echo esc_html(__( 'Netherlands', 'easy-stripe-payments' )); ?></option>
                <option value="BE"><?php echo esc_html(__( 'Belgium', 'easy-stripe-payments' )); ?></option>
                <option value="LU"><?php echo esc_html(__( 'Luxembourg', 'easy-stripe-payments' )); ?></option>
                <option value="IE"><?php echo esc_html(__( 'Ireland', 'easy-stripe-payments' )); ?></option>

                <option value="SE"><?php echo esc_html(__( 'Sweden', 'easy-stripe-payments' )); ?></option>
                <option value="NO"><?php echo esc_html(__( 'Norway', 'easy-stripe-payments' )); ?></option>
                <option value="DK"><?php echo esc_html(__( 'Denmark', 'easy-stripe-payments' )); ?></option>
                <option value="FI"><?php echo esc_html(__( 'Finland', 'easy-stripe-payments' )); ?></option>

                <option value="PL"><?php echo esc_html(__( 'Poland', 'easy-stripe-payments' )); ?></option>
                <option value="CZ"><?php echo esc_html(__( 'Czech Republic', 'easy-stripe-payments' )); ?></option>
                <option value="SK"><?php echo esc_html(__( 'Slovakia', 'easy-stripe-payments' )); ?></option>
                <option value="HU"><?php echo esc_html(__( 'Hungary', 'easy-stripe-payments' )); ?></option>
                <option value="RO"><?php echo esc_html(__( 'Romania', 'easy-stripe-payments' )); ?></option>
                <option value="BG"><?php echo esc_html(__( 'Bulgaria', 'easy-stripe-payments' )); ?></option>
                <option value="HR"><?php echo esc_html(__( 'Croatia', 'easy-stripe-payments' )); ?></option>
                <option value="SI"><?php echo esc_html(__( 'Slovenia', 'easy-stripe-payments' )); ?></option>
                <option value="EE"><?php echo esc_html(__( 'Estonia', 'easy-stripe-payments' )); ?></option>
                <option value="LV"><?php echo esc_html(__( 'Latvia', 'easy-stripe-payments' )); ?></option>
                <option value="LT"><?php echo esc_html(__( 'Lithuania', 'easy-stripe-payments' )); ?></option>
                <option value="UA"><?php echo esc_html(__( 'Ukraine', 'easy-stripe-payments' )); ?></option>
                <option value="RU"><?php echo esc_html(__( 'Russia', 'easy-stripe-payments' )); ?></option>
                <option value="BY"><?php echo esc_html(__( 'Belarus', 'easy-stripe-payments' )); ?></option>
                <option value="MD"><?php echo esc_html(__( 'Moldova', 'easy-stripe-payments' )); ?></option>                

                <option value="TR"><?php echo esc_html(__( 'Turkey', 'easy-stripe-payments' )); ?></option>
                <option value="GR"><?php echo esc_html(__( 'Greece', 'easy-stripe-payments' )); ?></option>
                <option value="CY"><?php echo esc_html(__( 'Cyprus', 'easy-stripe-payments' )); ?></option>

                <option value="JP"><?php echo esc_html(__( 'Japan', 'easy-stripe-payments' )); ?></option>
                <option value="SG"><?php echo esc_html(__( 'Singapore', 'easy-stripe-payments' )); ?></option>
                <option value="HK"><?php echo esc_html(__( 'Hong Kong', 'easy-stripe-payments' )); ?></option>

                <option value="MX"><?php echo esc_html(__( 'Mexico', 'easy-stripe-payments' )); ?></option>
                <option value="BR"><?php echo esc_html(__( 'Brazil', 'easy-stripe-payments' )); ?></option>
                <option value="AR"><?php echo esc_html(__( 'Argentina', 'easy-stripe-payments' )); ?></option>
                <option value="CL"><?php echo esc_html(__( 'Chile', 'easy-stripe-payments' )); ?></option>
                <option value="CO"><?php echo esc_html(__( 'Colombia', 'easy-stripe-payments' )); ?></option>

                <option value="AE"><?php echo esc_html(__( 'United Arab Emirates', 'easy-stripe-payments' )); ?></option>
                <option value="SA"><?php echo esc_html(__( 'Saudi Arabia', 'easy-stripe-payments' )); ?></option>
                <option value="QA"><?php echo esc_html(__( 'Qatar', 'easy-stripe-payments' )); ?></option>
                <option value="KW"><?php echo esc_html(__( 'Kuwait', 'easy-stripe-payments' )); ?></option>
                <option value="BH"><?php echo esc_html(__( 'Bahrain', 'easy-stripe-payments' )); ?></option>

                <option value="ZA"><?php echo esc_html(__( 'South Africa', 'easy-stripe-payments' )); ?></option>
            </select>            

            <label for="country" class="f-15">
                <?php echo esc_html(__( 'Country', 'easy-stripe-payments' )); ?>
            </label>

        </div>
    </div>

</div>