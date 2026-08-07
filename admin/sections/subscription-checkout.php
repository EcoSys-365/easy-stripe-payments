<?php defined( 'ABSPATH' ) || exit; ?>

<form id="espd-new-subscription-form" class="subscription-mode">
             
    <input type="hidden" id="form_id" name="form_id" value="">
    <input type="hidden" id="mode" name="mode" value="Subscription">

    <div id="espd-admin-subscription-notice-wrapper"></div>

    <div class="form-container">

            <div class="form-row">
                <div class="form-group">
                    <label for="form_name"><?php echo esc_html(__( 'Form Name', 'easy-stripe-payments' )); ?></label>
                    <input type="text" id="form_name" name="form_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="subscription_price_id"><?php echo esc_html(__( 'Stripe Product: Price ID', 'easy-stripe-payments' )); ?></label>
                    <input 
                           type="text" 
                           id="subscription_price_id"  
                           data-offset-top="-20"
                           placeholder="<?php echo esc_html(__( 'Example: price_1SYLHrEQHCvjDbcD5JZDCgcg', 'easy-stripe-payments' )); ?>"
                           data-tooltip="<?php echo esc_html(__( 'Enter the Stripe Price ID. You can either create a product under the Recurring Payments tab and use its Price ID, or copy the Price ID directly from your Stripe Dashboard by opening the desired subscription product. The Stripe Price ID can also be found in the URL within the Stripe Dashboard.', 'easy-stripe-payments' )); ?>"
                           name="subscription_price_id" 
                           class="form-control has-tooltip-xl" 
                           step="1" 
                           min="1"
                           required>
                </div>   
            </div>

            <div class="form-row">
                <div class="form-group">                

                    <label for="currency"><?php echo esc_html(__( 'Currency', 'easy-stripe-payments' )); ?></label>
                    <?php require ESPAD_PLUGIN_PATH . 'admin/sections/currency.php'; ?>

                </div>    
                <div class="form-group">

                    <?php 
                        $tooltip_contact_details_text = __( "Choose the fields you want to display in the Checkout Form.\nName &amp; Email are always required fields, as this information is mandatory for Stripe to handle payments.", "easy-stripe-payments" );
                    ?>

                    <label for="show_fields"><?php echo esc_html(__( 'Show Fields', 'easy-stripe-payments' )); ?></label>
                    <select 
                            name="show_fields" 
                            id="show_fields" 
                            class="form-control has-tooltip-xl"
                            data-tooltip="<?php echo esc_html( $tooltip_contact_details_text ); ?>"
                            data-offset-top="5"
                            required>
                        <option value="name_email"><?php echo esc_html(__( 'Name &amp; Email (Required fields)', 'easy-stripe-payments' )); ?></option>
                        <option value="name_email_address"><?php echo esc_html(__( 'Name &amp; Email &amp; Full Address', 'easy-stripe-payments' )); ?></option>
                        <option value="name_email_address_telephone"><?php echo esc_html(__( 'Name &amp; Email &amp; Full Address &amp; Telephone', 'easy-stripe-payments' )); ?></option>
                        <option value="name_email_address_required_fields">
                            <?php echo esc_html(__( 'Name &amp; Email &amp; Full Address (Required fields)', 'easy-stripe-payments' )); ?>
                        </option>
                        <option value="name_email_address_telephone_required_fields">
                            <?php echo esc_html(__( 'Name &amp; Email &amp; Full Address &amp; Telephone (Required fields)', 'easy-stripe-payments' )); ?>
                        </option>                        
                    </select>
                </div>                   
            </div>        

            <div class="form-row">
                <div class="form-group">                

                    <label for="form_language"><?php echo esc_html(__( 'Language', 'easy-stripe-payments' )); ?></label>
                    <?php require ESPAD_PLUGIN_PATH . 'admin/sections/form-languages.php'; ?>

                </div>   
                <div class="form-group">
                    <?php
                        $tooltip_payment_element_layout = __( "Select how the available payment methods are displayed in the Checkout Form.\nChoose (Auto) to let Stripe optimize the layout based on the user's device and context.", "easy-stripe-payments" );
                    ?>
                    <label for="payment_layout"><?php echo esc_html(__( 'Payment Layout', 'easy-stripe-payments' )); ?></label>
                    <select 
                            name="payment_layout" 
                            id="payment_layout"
                            class="form-control has-tooltip-xl"
                            data-tooltip="<?php echo esc_html( $tooltip_payment_element_layout ); ?>"
                            data-offset-top="5"
                            required>
                      <option value="auto"><?php echo esc_html(__( 'Auto – Stripe selects the layout', 'easy-stripe-payments' )); ?></option>
                      <option value="tabs"><?php echo esc_html(__( 'Tabs – Payment methods as tabs', 'easy-stripe-payments' )); ?></option>
                      <option value="accordion"><?php echo esc_html(__( 'Accordion – Collapsible sections', 'easy-stripe-payments' )); ?></option>
                    </select>                
                </div>                  
                <div class="form-group">
                    <label for="color">
                        <?php echo esc_html(__( 'Color', 'easy-stripe-payments' )); ?></label>
                    <input 
                           type="color" 
                           style="height: 47px;" 
                           id="color" 
                           name="color" 
                           value="#0d8889"
                           class="has-tooltip"
                           data-tooltip="<?php echo esc_html(__( 'Choose a color for the Checkout Form', 'easy-stripe-payments' )); ?>">   
                </div>                        
            </div>
 
            <div class="form-row">              
                <div class="form-group">
                    <label for="espad_payment_button"><?php echo esc_html(__( 'Payment Button', 'easy-stripe-payments' )); ?></label>
                    <input 
                           type="text" 
                           id="espad_payment_button"
                           placeholder="<?php echo esc_html(__( 'e.g., Subscribe, Start Subscription, Subscribe Now', 'easy-stripe-payments' )); ?>"
                           data-tooltip="<?php echo esc_html(__( 'Customize your button text', 'easy-stripe-payments' )); ?>"
                           name="espad_payment_button" 
                           class="form-control has-tooltip" 
                           required>                    
                </div>  
                <div class="form-group">
                    <label for="description"><?php echo esc_html(__( 'Description', 'easy-stripe-payments' )); ?></label>
                    <input type="text" id="description" name="description" class="form-control">
                </div>
            </div> 

            <?php $tooltip_success_cancel_url = __( "Stripe will redirect the user to the specified landing page after the payment process.", "easy-stripe-payments" ); ?>                

            <div class="form-row">

                <div class="form-group">
                    <label 
                           for="success_url"
                           class="has-tooltip"
                           data-tooltip="<?php echo esc_html( $premium_tooltip ); ?>">
                        <?php echo esc_html(__( 'Success URL', 'easy-stripe-payments' )); ?> &#x1F48E;</label>
                    <input 
                           type="url" 
                           id="success_url" 
                           name="success_url" 
                           class="form-control has-tooltip"
                           data-offset-top=""
                           placeholder="<?php echo esc_html(__( 'You can leave this field empty or define your own landing page', 'easy-stripe-payments' )); ?>"
                           data-tooltip="<?php echo esc_html( $tooltip_success_cancel_url ); ?>"
                           <?php echo esc_html( $premium_field );?>>
                </div>      

                <div class="form-group">
                    <label 
                           for="cancel_url"
                           class="has-tooltip"
                           data-tooltip="<?php echo esc_html( $premium_tooltip ); ?>">
                        <?php echo esc_html(__( 'Cancel URL', 'easy-stripe-payments' )); ?> &#x1F48E;</label>
                    <input 
                           type="url" 
                           id="cancel_url" 
                           name="cancel_url" 
                           class="form-control has-tooltip"
                           data-offset-top=""
                           placeholder="<?php echo esc_html(__( 'You can leave this field empty or define your own landing page', 'easy-stripe-payments' )); ?>"
                           data-tooltip="<?php echo esc_html( $tooltip_success_cancel_url ); ?>"
                           <?php echo esc_html( $premium_field );?>>
                </div>                          

            </div>

            <?php 
        
                $tooltip_stripe_metadata = __( "You can define custom metadata such as campaign_id, project_name, or product_name to help track your payments more effectively.\nE.g. campaign_id: 5872,\nproject_name: Winter Campaign,\nproduct_name: Blue Hoodie\nYou can fill out any combination of the three metadata fields, or just one, depending on your needs. The metadata will be saved in Stripe accordingly for each payment.", "easy-stripe-payments" );

                $tooltip_stripe_metadata_short = __( "You can define custom metadata such as campaign_id, project_name, or product_name to help track your payments more effectively.", "easy-stripe-payments" );
            ?>                

            <div class="form-row">

                <div class="form-group">
                    <label 
                           for="stripe_metadata_campaign"
                           class="has-tooltip"
                           data-tooltip="<?php echo esc_html( $premium_tooltip ); ?>">
                        <?php echo esc_html(__( 'Stripe Metadata:', 'easy-stripe-payments' )); ?> campaign_id &#x1F48E;</label>
                    <input 
                           type="text" 
                           id="stripe_metadata_campaign" 
                           name="stripe_metadata_campaign" 
                           class="form-control has-tooltip-xl"
                           data-offset-top="-60"
                           placeholder="<?php echo esc_html(__( 'E.g. 5872', 'easy-stripe-payments' )); ?>"
                           data-tooltip="<?php echo esc_html( $tooltip_stripe_metadata ); ?>"
                           <?php echo esc_html( $premium_field );?>>                        
                </div>  

                <div class="form-group">
                    <label 
                           for="stripe_metadata_project"
                           class="has-tooltip"
                           data-tooltip="<?php echo esc_html( $premium_tooltip ); ?>">
                        <?php echo esc_html(__( 'Stripe Metadata:', 'easy-stripe-payments' )); ?> project_name &#x1F48E;</label>
                    <input 
                           type="text" 
                           id="stripe_metadata_project" 
                           name="stripe_metadata_project" 
                           class="form-control has-tooltip-xl"
                           data-offset-top="25"
                           placeholder="<?php echo esc_html(__( 'E.g. Winter Campaign', 'easy-stripe-payments' )); ?>"
                           data-tooltip="<?php echo esc_html( $tooltip_stripe_metadata_short ); ?>"
                           <?php echo esc_html( $premium_field );?>>                        
                </div>      

                <div class="form-group">
                    <label 
                           for="stripe_metadata_product"
                           class="has-tooltip"
                           data-tooltip="<?php echo esc_html( $premium_tooltip ); ?>">
                        <?php echo esc_html(__( 'Stripe Metadata:', 'easy-stripe-payments' )); ?> product_name &#x1F48E;</label>
                    <input 
                           type="text" 
                           id="stripe_metadata_product" 
                           name="stripe_metadata_product" 
                           class="form-control has-tooltip-xl"
                           data-offset-top="5"
                           placeholder="<?php echo esc_html(__( 'E.g. Blue Hoodie', 'easy-stripe-payments' )); ?>"
                           data-tooltip="<?php echo esc_html( $tooltip_stripe_metadata_short ); ?>"
                           <?php echo esc_html( $premium_field );?>>                        
                </div>                          

            </div>                

    </div>

    <p class="submit">
        <button type="submit" class="button button-primary"><?php echo esc_html(__( 'Save Form', 'easy-stripe-payments' )); ?></button>
    </p>

</form>