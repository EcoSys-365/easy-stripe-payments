<?php defined( 'ABSPATH' ) || exit; ?>

<form id="espd-new-campaign-form" class="campaign-mode">
            
    <input type="hidden" id="form_id" name="form_id" value="">
    <input type="hidden" id="mode" name="mode" value="Campaign">

    <div id="espd-admin-campaign-notice-wrapper"></div>

    <div class="form-container">

            <div class="form-row">
                <div class="form-group">
                    <label for="form_name"><?php echo esc_html(__( 'Form Name', 'easy-stripe-payments' )); ?></label>
                    <input type="text" id="form_name" name="form_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="fix_amount"><?php echo esc_html(__( 'Fix Amount', 'easy-stripe-payments' )); ?></label>
                    <input 
                           type="number" 
                           id="fix_amount" 
                           class="has-tooltip"
                           placeholder="<?php echo esc_html(__( 'This field can be left empty depending on the selected amount type', 'easy-stripe-payments' )); ?>"
                           data-tooltip="<?php echo esc_html(__( 'This field can be left empty depending on the selected amount type', 'easy-stripe-payments' )); ?>"
                           name="fix_amount" 
                           class="form-control" 
                           step="1" 
                           min="1">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">                

                    <label for="currency"><?php echo esc_html(__( 'Currency', 'easy-stripe-payments' )); ?></label>
                    <?php require ESPAD_PLUGIN_PATH . 'admin/sections/currency.php'; ?>

                </div>               
                <div class="form-group">

                    <?php 
                        $tooltip_text = __( "Fixed amount e.g.: 50 $\nVariable amount is entered by the user.\nSelect amount e.g.: 100 $, 200 $, 300 $, 400 $, 500 $\nAfter saving the form, you can check it in the preview.", "easy-stripe-payments" );
                    ?>

                    <label for="amount_type"><?php echo esc_html(__( 'Amount type', 'easy-stripe-payments' )); ?></label>
                    <select 
                            name="amount_type" 
                            id="amount_type" 
                            class="form-control has-tooltip-xl"
                            data-tooltip="<?php echo esc_html($tooltip_text); ?>">
                        <option value="fix_amount"><?php echo esc_html(__( 'Fix Amount', 'easy-stripe-payments' )); ?></option>
                        <option value="variable_amount"><?php echo esc_html(__( 'Variable Amount', 'easy-stripe-payments' )); ?></option>
                        <option value="select_amount"><?php echo esc_html(__( 'Select Amount', 'easy-stripe-payments' )); ?></option>
                        <option value="select_and_variable_amount"><?php echo esc_html(__( 'Select &amp; Variable Amount', 'easy-stripe-payments' )); ?></option>
                    </select>

                    <div id="select-amount-wrapper" style="margin-top: 10px; display: none;">
                        <label for="price_list"><?php echo esc_html(__( 'Please select a price list', 'easy-stripe-payments' )); ?></label>
                        <select name="price_list" id="price_list" class="form-control">
                            <option value="5,10,15,20,25">5, 10, 15, 20, 25</option>
                            <option value="5,10,20,30,50">5, 10, 20, 30, 50</option>
                            <option value="10,20,30,40,50">10, 20, 30, 40, 50</option>
                            <option value="10,20,50,80,100">10, 20, 50, 80, 100</option>
                            <option value="20,40,60,80,100">20, 40, 60, 80, 100</option>
                            <option value="30,60,90,120,150">30, 60, 90, 120, 150</option>
                            <option value="50,100,150,200,250">50, 100, 150, 200, 250</option>
                            <option value="100,200,300,400,500">100, 200, 300, 400, 500</option>
                            <option value="150,300,450,600,750">150, 300, 450, 600, 750</option>
                            <option value="200,400,600,800,1000">200, 400, 600, 800, 1000</option>
                            <option value="300,600,900,1200,1500">300, 600, 900, 1200, 1500</option>
                            <option value="400,800,1200,1600,2000">400, 800, 1200, 1600, 2000</option>
                            <option value="500,1000,1500,2000,2500">500, 1000, 1500, 2000, 2500</option>
                            <option value="600,1200,1800,2400,3000">600, 1200, 1800, 2400, 3000</option>
                            <option value="700,1400,2100,2800,3500">700, 1400, 2100, 2800, 3500</option>
                            <option value="800,1600,2400,3200,4000">800, 1600, 2400, 3200, 4000</option>
                            <option value="900,1800,2700,3600,4500">900, 1800, 2700, 3600, 4500</option>
                            <option value="1000,2000,3000,4000,5000">1000, 2000, 3000, 4000, 5000</option>
                        </select>
                    </div>   

                </div>
            </div>        

            <div class="form-row">
                <div class="form-group">
                    <label for="campaign_image"><?php echo esc_html(__( 'Image', 'easy-stripe-payments' )); ?></label>
                    <div class="espad_img_box">
                        <input 
                          type="text" 
                          id="campaign_image" 
                          name="campaign_image" 
                          class="form-control product_image_input" 
                          required>
                        <input 
                          type="button" 
                          class="button product_image_button" 
                          value="<?php echo esc_html(__( 'Select image', 'easy-stripe-payments' )); ?>">
                    </div>                    
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
                            data-tooltip="<?php echo esc_html($tooltip_payment_element_layout); ?>"
                            data-offset-top="5"
                            required>
                        <option value="auto"><?php echo esc_html(__( 'Auto – Stripe selects the layout', 'easy-stripe-payments' )); ?></option>
                        <option value="tabs"><?php echo esc_html(__( 'Tabs – Payment methods as tabs', 'easy-stripe-payments' )); ?></option>
                        <option value="accordion"><?php echo esc_html(__( 'Accordion – Collapsible sections', 'easy-stripe-payments' )); ?></option>
                    </select>                
                </div>
                <div class="form-group">
                    <label for="espad_payment_button"><?php echo esc_html(__( 'Button', 'easy-stripe-payments' )); ?></label>
                    <select 
                            name="espad_payment_button" 
                            id="espad_payment_button" 
                            class="form-control has-tooltip"
                            data-tooltip="<?php echo esc_html(__( 'Customize your button text', 'easy-stripe-payments' )); ?>"
                            required>
                        <option value="<?php echo esc_html(__( 'Pay', 'easy-stripe-payments' )); ?>"><?php echo esc_html(__( 'Pay', 'easy-stripe-payments' )); ?></option>
                        <option value="<?php echo esc_html(__( 'Donate', 'easy-stripe-payments' )); ?>"><?php echo esc_html(__( 'Donate', 'easy-stripe-payments' )); ?></option>
                        <option value="<?php echo esc_html(__( 'Book', 'easy-stripe-payments' )); ?>"><?php echo esc_html(__( 'Book', 'easy-stripe-payments' )); ?></option>
                    </select>
                </div>                       
            </div> 
        
            <div class="form-row">
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
                <div class="form-group">                

                    <label for="form_language"><?php echo esc_html(__( 'Language', 'easy-stripe-payments' )); ?></label>
                    <?php require ESPAD_PLUGIN_PATH . 'admin/sections/form-languages.php'; ?>

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
                <?php
                    $campaign_current_amount_text = __( "Set the current amount for the campaign, or leave this field empty to start from zero.\nThis value is automatically updated in the database after each successful payment.\nIf necessary, you can manually override the current database value from here.", "easy-stripe-payments" );
                ?>
                <div class="form-group">
                    <label for="campaign_current_amount"><?php echo esc_html(__( 'Campaign Current Amount', 'easy-stripe-payments' )); ?></label>
                    <input 
                           type="number" 
                           id="campaign_current_amount" 
                           class="has-tooltip-xl"
                           placeholder=""
                           data-tooltip="<?php echo esc_html( $campaign_current_amount_text ); ?>"
                           data-offset-top="-17"
                           name="campaign_current_amount" 
                           class="form-control" 
                           step="1" 
                           min="0">    
                </div>                 
                <div class="form-group">
                    <label for="campaign_goal_amount"><?php echo esc_html(__( 'Campaign Goal Amount', 'easy-stripe-payments' )); ?></label>
                    <input 
                           type="number" 
                           id="campaign_goal_amount" 
                           class="has-tooltip"
                           placeholder=""
                           data-tooltip="<?php echo esc_html(__( 'Enter the target amount for this campaign, or leave it empty to hide the progress bar.', 'easy-stripe-payments' )); ?>"
                           name="campaign_goal_amount" 
                           class="form-control" 
                           step="1" 
                           min="1">                    
                </div>                       
            </div> 
        
            <div class="form-row">
                <div class="form-group">
                    <label for="description"><?php echo esc_html(__( 'Description', 'easy-stripe-payments' )); ?></label>
                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>                    
                </div>
            </div>         
 
            <?php $tooltip_success_cancel_url = __( "Stripe will redirect the user to the specified landing page after the payment process.", "easy-stripe-payments" ); ?>                

            <div class="form-row">

                <div class="form-group">
                    <label 
                           for="success_url"
                           class="has-tooltip"
                           data-tooltip="<?php echo esc_html($premium_tooltip); ?>">
                        <?php echo esc_html(__( 'Success URL', 'easy-stripe-payments' )); ?> &#x1F48E;</label>
                    <input 
                           type="url" 
                           id="success_url" 
                           name="success_url" 
                           class="form-control has-tooltip"
                           data-offset-top=""
                           placeholder="<?php echo esc_html(__( 'You can leave this field empty or define your own landing page', 'easy-stripe-payments' )); ?>"
                           data-tooltip="<?php echo esc_html($tooltip_success_cancel_url); ?>"
                           <?php echo esc_html( $premium_field ); ?>>
                </div>      

                <div class="form-group">
                    <label 
                           for="cancel_url"
                           class="has-tooltip"
                           data-tooltip="<?php echo esc_html($premium_tooltip); ?>">
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