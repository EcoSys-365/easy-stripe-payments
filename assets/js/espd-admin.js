jQuery(document).ready(function($) {
    
    /*
     * Fades out the loading overlay slowly, and once finished,
     * fades in the main content area slowly.
    */    
    $('#espad-loading-overlay').fadeOut('slow', function() {
        $('#content').fadeIn('slow');
    });    
     
    /*
     * Initializes a jQuery UI dialog on the element with ID "espd-form-modal":
     * - The dialog does not open automatically (autoOpen: false)
     * - Modal behavior is enabled, preventing interaction with the rest of the page while open
     * - Sets the dialog width to 1100 pixels
     * - Sets the dialog title using a localized string from "espd_ajax.standardCheckoutModalTitle"
    */    
    $("#espd-form-modal").dialog({
        autoOpen: false,
        modal: true,
        width: 1100,
        title: espd_ajax.standardCheckoutModalTitle
    });
    
    /*
     * Initializes a jQuery UI dialog on the element with ID "espd-form-campaign-modal":
     * - The dialog does not open automatically (autoOpen: false)
     * - Enables modal behavior to block interaction with the rest of the page while open
     * - Sets the dialog width to 1100 pixels
     * - Uses the localized title from "espd_ajax.campaignCheckoutModalTitle"
    */    
    $("#espd-form-campaign-modal").dialog({
        autoOpen: false,
        modal: true,
        width: 1100,
        title: espd_ajax.campaignCheckoutModalTitle
    });  
    
    /*
     * Initializes a jQuery UI dialog on the element with ID "espd-form-recurring-modal":
     * - Does not open automatically (autoOpen: false)
     * - Enables modal mode to prevent interaction with the page while open
     * - Sets the dialog width to 1100 pixels
     * - Sets the dialog title using the localized string from "espd_ajax.recurringModalTitle"
    */    
    $("#espd-form-recurring-modal").dialog({
        autoOpen: false,
        modal: true,
        width: 1100,
        title: espd_ajax.recurringModalTitle
    });    
    
    /*
     * Handles the click event on the element with ID "espd-create-form":
     * - Resets the form with ID "espd-new-form"
     * - Clears the hidden input field named 'form_id' inside the form
     * - Hides the element with ID "select-amount-wrapper"
     * - Opens the modal dialog with ID "espd-form-modal"
    */    
    $("#espd-create-form").on("click", function() {
        
        $("#espd-new-form")[0].reset();

        $("#espd-new-form input[name='form_id']").val('');

        $("#select-amount-wrapper").hide();

        $("#espd-form-modal").dialog("open");
        
    });
    
    /*
     * Handles the click event on the element with ID "espd-create-campaign-form":
     * - Resets the form with ID "espd-new-campaign-form"
     * - Clears the hidden input field named 'form_id' inside the campaign form
     * - Hides the element with ID "select-amount-wrapper"
     * - Opens the modal dialog with ID "espd-form-campaign-modal"
    */    
    $("#espd-create-campaign-form").on("click", function() {
        
        $("#espd-new-campaign-form")[0].reset();

        $("#espd-new-campaign-form input[name='form_id']").val('');

        $("#select-amount-wrapper").hide();

        $("#espd-form-campaign-modal").dialog("open");
        
    });    
    
    /*
     * This script handles the submission of the form with ID "espd-new-form":
     * - Prevents the default form submission behavior (no page reload)
     * - Serializes the form data into a URL-encoded string
     * - Sends the data via AJAX POST to the server with a specific action and nonce
     * - Displays a success or error message based on the server response
     * - Reloads the page after 1.5 seconds if the submission is successful
    */    
    $("#espd-new-form").on("submit", function(e) {
        
        e.preventDefault();
        
        var formData = $(this).serialize();

        $.post(espd_ajax.ajax_url, {
            action: 'espd_save_form',
            nonce: espd_ajax.nonce,
            data: formData
        }, function(response) {
            if (response.success) {
                showEspdAdminNotice("Form saved successfully", 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showEspdAdminNotice("Error: " + response.data, 'error');
            }
        });   
        
    });
    
    /*
     * This script handles the submission of the form with ID "espd-new-campaign-form":
     * - Prevents the default form submission to avoid page reload
     * - Serializes the form data into a URL-encoded string
     * - Sends an AJAX POST request to the server with a specific action and security nonce
     * - On success:
     *   - Smoothly scrolls to the top of the page
     *   - Displays a success message using showEspdAdminCampaignNotice
     *   - Reloads the page after 1.5 seconds
     * - On failure:
     *   - Displays an error message with details using showEspdAdminCampaignNotice
    */    
    $("#espd-new-campaign-form").on("submit", function(e) {
        
        e.preventDefault();
        
        var formData = $(this).serialize();

        $.post(espd_ajax.ajax_url, {
            action: 'espd_save_form',
            nonce: espd_ajax.nonce,
            data: formData
        }, function(response) {
            if (response.success) {
                
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                showEspdAdminCampaignNotice("Form saved successfully", 'success');
                
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showEspdAdminCampaignNotice("Error: " + response.data, 'error');
            }
        });   
        
    });
    
    /*
     * This script handles the submission of the form with ID "espd-update-recurring-product":
     * - Prevents the default form submission to avoid a page reload
     * - Serializes the form data into a URL-encoded string
     * - Sends an AJAX POST request to the server with:
     *   - A specific action identifier for updating a recurring product
     *   - A security nonce for verification
     *   - The serialized form data
     * - If the server responds with success:
     *   - Displays a success message to the user
     *   - Reloads the page after 1.5 seconds
     * - If the server responds with an error:
     *   - Displays an error message with details
    */    
    $("#espd-update-recurring-product").on("submit", function(e) {
        
        e.preventDefault();
        
        var formData = $(this).serialize();

        $.post(espd_ajax.ajax_url, {
            action: 'espd_update_recurring_product',
            nonce: espd_ajax.nonce,
            data: formData
        }, function(response) {
            if (response.success) {
                showEspdAdminNotice("Form saved successfully", 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showEspdAdminNotice("Error: " + response.data, 'error');
            }
        });   
        
    });    
    
    /*
     * This function displays an admin notice message in the element with ID "espd-admin-notice-wrapper":
     * - Accepts a message and an optional type (default is 'success')
     * - Inserts a dismissible notice box with the given message and type ('success', 'error', etc.)
     * - Automatically hides the notice after 3 seconds
    */    
    function showEspdAdminNotice(message, type = 'success') {
        
        const wrapper = document.getElementById('espd-admin-notice-wrapper');
        
        wrapper.innerHTML = `
            <div class="notice notice-${type} is-dismissible">
                <p>${message}</p>
            </div>
        `;

        setTimeout(() => {
            const notice = wrapper.querySelector('.notice');
            if (notice) notice.style.display = 'none';
        }, 3000);
        
    }
    
    /*
     * This function displays a campaign-specific admin notice message in the element with ID "espd-admin-campaign-notice-wrapper":
     * - Takes a message and an optional type (default is 'success')
     * - Inserts a dismissible WordPress-style notice with the given message and type ('success', 'error', etc.)
     * - Automatically hides the notice after 3 seconds by setting its display to 'none'
    */    
    function showEspdAdminCampaignNotice(message, type = 'success') {
        
        const wrapper = document.getElementById('espd-admin-campaign-notice-wrapper');
        
        wrapper.innerHTML = `
            <div class="notice notice-${type} is-dismissible">
                <p>${message}</p>
            </div>
        `;

        setTimeout(() => {
            const notice = wrapper.querySelector('.notice');
            if (notice) notice.style.display = 'none';
        }, 3000);
        
    }    
    
    /*
     * This script enables "copy to clipboard" functionality for elements with the class "copy-button":
     * - Attaches a click event listener to each copy button
     * - On click:
     *   - Retrieves the target element ID from the button's "data-target" attribute
     *   - Gets the text content of the target element
     *   - Copies the text to the clipboard using the Clipboard API
     *   - If successful:
     *     - Changes the button text to a thumbs-up emoji
     *     - Increases the font size and adds a 'copied' class for visual feedback
     *     - After 2 seconds, resets the button text, font size, and removes the class
     *   - If an error occurs during copying, shows an alert and logs the error to the console
    */    
    const copyButtons = document.querySelectorAll('.copy-button');

    copyButtons.forEach(button => {
        
        button.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const codeElement = document.getElementById(targetId);

            if (codeElement) {
                const text = codeElement.textContent;

                navigator.clipboard.writeText(text).then(() => {
                    this.textContent = '👍🏼';
                    this.style.fontSize = '20px';
                    this.classList.add('copied');

                    setTimeout(() => {
                        this.textContent = 'Copy';
                        this.style.fontSize = '13px';
                        this.classList.remove('copied');
                    }, 2000);
                }).catch(err => {
                    alert('Error beim Kopieren');
                    console.error(err);
                });
            }
        });
        
    });
    
    /*
     * This script handles the deletion of a form when a ".delete-button" is clicked:
     * - Attaches a click event listener to each delete button
     * - On click:
     *   - Retrieves the form name from a data attribute for confirmation display
     *   - Retrieves the form ID from the button's dataset
     *   - Prompts the user for confirmation with the form name
     *   - If confirmed, sends a POST request via Fetch to delete the form:
     *     - Includes action name, nonce, and form ID in the request body
     *   - If the server confirms success, removes the corresponding table row from the DOM
     *   - If there's an error, shows an alert with the error message
    */    
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', function () {
            
            const formName = this.parentElement.parentElement.getAttribute('data-form-name');
            
            const formId = this.dataset.id;
            if (!confirm('Delete: ' + formName)) return;
            
            fetch(espd_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'espd_delete_form',
                    nonce: espd_ajax.nonce,
                    form_id: formId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.closest('tr').remove();
                } else {
                    alert('Error: ' + data.data);
                }
            });
        });
    }); 
     
    /*
     * This script handles deletion of Stripe payment entries in the table with ID "espad-table-stripe-payments":
     * - Listens for click events on the entire table
     * - If the clicked element (or its ancestor) is a ".delete-button-payment":
     *   - Retrieves the form name and payment ID from the DOM and data attributes
     *   - Asks the user for confirmation using the form name
     *   - If confirmed:
     *     - Disables the delete button to prevent multiple submissions
     *     - Sends a POST request to the server to delete the payment
     *     - On success: removes the corresponding table row
     *     - On failure: shows an error alert and re-enables the button
     *     - On network error: alerts the user and re-enables the button
    */    
    const table = document.getElementById('espad-table-stripe-payments');

    if ( table ) {
        
        table.addEventListener('click', function (e) {
        
            const button = e.target.closest('.delete-button-payment');
            if (!button) return; // Nicht der Delete-Button ? abbrechen

            const formName = button.parentElement.parentElement.getAttribute('data-form-name');
            const paymentId = button.dataset.id;

            if (!confirm('Delete: ' + formName)) return;

            // Optional: Button w�hrend der Verarbeitung deaktivieren
            button.disabled = true;

            fetch(espd_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'espd_delete_payment',
                    nonce: espd_ajax.nonce,
                    payment_id: paymentId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    button.closest('tr').remove();
                } else {
                    alert('Error: ' + data.data);
                    button.disabled = false;
                }
            })
            .catch(err => {
                alert('Network error');
                button.disabled = false;
            });
         
        });
    }
    
    /*
     * This script handles the deletion of recurring payment products when a ".delete-button-recurring-payment" is clicked:
     * - Adds a click event listener to each delete button
     * - On click:
     *   - Retrieves the form name from the DOM to display in the confirmation prompt
     *   - Retrieves the product ID from the button's data attribute
     *   - Prompts the user for confirmation with the form name
     *   - If confirmed:
     *     - Sends a POST request to the server with the action, nonce, and product ID
     *     - On success: removes the corresponding table row from the DOM
     *     - On failure: shows an alert with the error message returned by the server
    */    
    document.querySelectorAll('.delete-button-recurring-payment').forEach(button => {
        button.addEventListener('click', function () {
            
            const formName = this.parentElement.parentElement.getAttribute('data-form-name');
            
            const productId = this.dataset.id;
            if (!confirm('Delete: ' + formName)) return;
            
            fetch(espd_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'espd_delete_recurring_payment',
                    nonce: espd_ajax.nonce,
                    product_id: productId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.closest('tr').remove();
                } else {
                    alert('Error: ' + data.data);
                }
            });
        });
    });     
    
    /*
     * This script handles the click event on elements with the class "edit-button":
     * - Selects input fields for campaign amounts and:
     *   - Prevents the user from typing '.' or ',' characters
     *   - Removes any '.' or ',' characters if pasted or inputted
     * - Retrieves the form ID and mode (e.g., "standard" or "campaign") from the clicked button's data attributes
     * - Converts the form mode to lowercase and sets the hidden form ID input accordingly
     * - Sends an AJAX GET request to fetch form data from the server using the form ID and nonce
     * - On success:
     *   - Populates multiple form fields inside the modal dialog based on the returned form data
     *   - Displays or hides the amount selection section based on whether a price list exists and the amount type
     *   - Opens either the standard or campaign modal dialog depending on the form mode
     * - On failure:
     *   - Displays an alert with the error message
    */    
    $(document).on('click', '.edit-button', function() {

        const $inputs = $('#campaign_current_amount, #campaign_goal_amount');

        // Verhindert Eingabe von Punkt und Komma
        $inputs.on('keydown', function(e) {
            if (e.key === '.' || e.key === ',') {
                e.preventDefault();
            }
        });
  
        // Verhindert Copy/Paste
        $inputs.on('input', function() {
            this.value = this.value.replace(/[.,]/g, '');
        });        
        
        var formId = $(this).data('id');
        var formMode = $(this).data('mode');
        
        formMode = formMode.toLowerCase();
        
        $('.' + formMode + '-mode #form_id').val(formId);
        
        $.ajax({
            url: espd_ajax.ajax_url,
            method: 'GET',
            data: {
                action: 'espd_get_form_data',
                nonce: espd_ajax.nonce,
                form_id: formId
            },
            beforeSend: function() {
                $('#espad-loading-overlay').show();
            },            
            success: function(response) {
                
                if (response.success) {
                    
                    $('.' + formMode + '-mode #form_name').val(response.data.form_name);
                    $('.' + formMode + '-mode #fix_amount').val(response.data.fix_amount);
                    $('.' + formMode + '-mode #currency').val(response.data.currency);
                    $('.' + formMode + '-mode #description').val(response.data.description);
                    $('.' + formMode + '-mode #success_url').val(response.data.success_url);
                    $('.' + formMode + '-mode #cancel_url').val(response.data.cancel_url);
                    $('.' + formMode + '-mode #stripe_metadata_campaign').val(response.data.stripe_metadata_campaign);
                    $('.' + formMode + '-mode #stripe_metadata_project').val(response.data.stripe_metadata_project);
                    $('.' + formMode + '-mode #stripe_metadata_product').val(response.data.stripe_metadata_product);
                    $('.' + formMode + '-mode #form_id').val(response.data.id);
                    $('.' + formMode + '-mode #amount_type').val(response.data.amount_type);
                    $('.' + formMode + '-mode #price_list').val(response.data.price_list);
                    $('.' + formMode + '-mode #espad_payment_button').val(response.data.payment_button);
                    $('.campaign-mode #campaign_image').val(response.data.campaign_image);
                    $('.campaign-mode #campaign_current_amount').val(response.data.campaign_current_amount);
                    $('.campaign-mode #campaign_goal_amount').val(response.data.campaign_goal_amount);
                    $('.' + formMode + '-mode #color').val(response.data.color);
                    $('.' + formMode + '-mode #show_fields').val(response.data.choosed_fields);
                    $('.' + formMode + '-mode #form_language').val(response.data.lang);
                    $('.' + formMode + '-mode #payment_layout').val(response.data.payment_layout);
                    
                    if ( response.data.price_list != null && response.data.price_list.trim() !== '' ) {
                        
                        $("#select-amount-wrapper").css("display","block");
                        
                    } 
                    
                    if ( formMode == 'standard' ) {
                        
                        $("#espd-form-modal").dialog("open");
                        
                        let amount_type = response.data.amount_type;
                        
                        if ( amount_type == 'fix_amount' ) {
                           
                            $('#espd-form-modal #select-amount-wrapper').css('display','none');    
                            
                        } else {
                            
                            $('#espd-form-modal #select-amount-wrapper').css('display','block');
                            
                        }
                        
                    } else {
                        
                        $("#espd-form-campaign-modal").dialog("open");
                        
                        let amount_type = response.data.amount_type;
                        
                        if ( amount_type == 'fix_amount' ) {
                           
                            $('#espd-form-campaign-modal #select-amount-wrapper').css('display','none');
                            
                        } else {
                            
                            $('#espd-form-campaign-modal #select-amount-wrapper').css('display','block');
                            
                        }                        
                        
                    }
                    
                } else {
                    
                    alert("Error: " + response.data);
                    
                }
                
            },
            complete: function() {
                $('#espad-loading-overlay').hide();
            },
            error: function() {
                alert('An Error has occurred');
            }            
        });      
        
    });
    
    /*
     * This script handles the click event on elements with the class "edit-recurring-button":
     * - Retrieves the product ID and product price from the clicked button's data attributes
     * - Sets the product ID in a hidden input field
     * - Sends an AJAX GET request to fetch product data from the server using the product ID and nonce
     * - Before the request, shows a loading overlay
     * - On success:
     *   - Populates various input fields with the product data (name, description, images, default price)
     *   - Applies button settings such as title, size, colors, and language, with default fallbacks
     *   - Updates the appearance and text of a subscription button based on these settings
     *   - Opens the recurring product modal dialog
     * - On failure:
     *   - Shows an alert with the error message
     * - After completion (success or failure), hides the loading overlay
     * - On AJAX error, alerts the user that an error occurred
    */    
    $(document).on('click', '.edit-recurring-button', function() {
        
        var productId    = $(this).data('id');
        var productPrice = $(this).data('product-price');
        
        $('#product_id').val(productId);
        
        $.ajax({
            url: espd_ajax.ajax_url,
            method: 'GET',
            data: {
                action: 'espd_get_product_data',
                nonce: espd_ajax.nonce,
                product_id: productId
            },
            beforeSend: function() {
                $('#espad-loading-overlay').show();
            },            
            success: function(response) {
                
                if ( response.success ) {
                    
                    $('#choosed_product_name').val(response.data.product_name);
                    $('#choosed_product_description').val(response.data.product_description);
                    $('#choosed_product_images').val(response.data.product_images);
                    $('#choosed_product_default_price').val(productPrice);
                    
                    const buttonSettings = response.data.button_settings;

                    if ( buttonSettings ) {
                       
                        $('#button_title').val(buttonSettings.button_title || 'Subscribe');
                        $('#button_size').val(buttonSettings.button_size || 'small');
                        $('#button_color').val(buttonSettings.button_color || '#0d8889');
                        $('#button_font_color').val(buttonSettings.button_font_color || '#ffffff');
                        $('#espd-update-recurring-product #form_language').val(buttonSettings.button_language || 'en');
                        
                        const $btn = $('.subscription_btn');

                        $btn
                            .text( buttonSettings.button_title || 'Subscribe' )
                            .css({
                                'background-color': buttonSettings.button_color || '#0d8889',
                                'color': buttonSettings.button_font_color || '#ffffff'
                            });

                        const sizeClasses = ['btn-small', 'btn-medium', 'btn-large', 'btn-x-large', 'btn-xx-large'];

                        $btn.removeClass(sizeClasses.join(' '));

                        if ( buttonSettings.button_size ) {
                            
                            $btn.addClass('btn-' + buttonSettings.button_size);
                            
                        }
                        
                    } 
                    
                    $("#espd-form-recurring-modal").dialog("open");
                    
                } else {
                    
                    alert("Error: " + response.data);
                    
                }
                
            },
            complete: function() {
                $('#espad-loading-overlay').hide();
            },
            error: function() {
                alert('An Error has occurred');
            }
        });
        
    });    
    
    /*
     * This script adds click event listeners to all elements with the class "preview-button":
     * - On click, retrieves the form ID from the button's data attribute
     * - If the form ID exists:
     *   - Constructs a preview URL using the form ID as a query parameter
     *   - Opens the preview URL in a new browser tab or window
    */    
    document.querySelectorAll('.preview-button').forEach(button => {
        
        button.addEventListener('click', function () {
            const formId = this.getAttribute('data-id');

            if (formId) {
                
                const previewUrl = `admin.php?page=espd_main&tab=preview&form_id=${formId}`;
                window.open(previewUrl, '_blank');
                
            }
        });
        
    });
    
    /*
     * This script listens for changes on the "#amount_type" select element inside the "#espd-form-modal":
     * - Retrieves the selected value
     * - Shows the "#select-amount-wrapper" if the selected type is "select_amount" or "select_and_variable_amount"
     * - Otherwise, hides the "#select-amount-wrapper"
     * - Sets the "fix_amount" input field as required if "fix_amount" is selected
     * - Removes the required attribute from "fix_amount" input if any other option is selected
    */    
    $('#espd-form-modal #amount_type').on('change', function() {
        
        const selected = this.value;
        const selectWrapper = $('#espd-form-modal #select-amount-wrapper');
        
        if ( selected === 'select_amount' || selected === 'select_and_variable_amount' ) {
            selectWrapper.show();
        } else {
            selectWrapper.hide();
        }
        
        if ($(this).val() === 'fix_amount') {
            
            $('#espd-form-modal #fix_amount').attr('required', true);
            
        } else {
            
            $('#espd-form-modal #fix_amount').removeAttr('required');
            
        }        
        
    });     
    
    /*
     * This script listens for changes on the "#amount_type" select element inside the "#espd-form-campaign-modal":
     * - Gets the selected value
     * - Shows the "#select-amount-wrapper" if the selected type is "select_amount" or "select_and_variable_amount"
     * - Otherwise, hides the "#select-amount-wrapper"
     * - Marks the "fix_amount" input as required if the selected type is "fix_amount"
     * - Removes the required attribute from "fix_amount" if any other option is selected
    */    
    $('#espd-form-campaign-modal #amount_type').on('change', function() {
        
        const selected = this.value;
        const selectWrapper = $('#espd-form-campaign-modal #select-amount-wrapper');
        
        if ( selected === 'select_amount' || selected === 'select_and_variable_amount' ) {
            selectWrapper.show();
        } else {
            selectWrapper.hide();
        }
        
        if ($(this).val() === 'fix_amount') {
            
            $('#espd-form-campaign-modal #fix_amount').attr('required', true);
            
        } else {
            
            $('#espd-form-campaign-modal #fix_amount').removeAttr('required');
            
        }        
        
    });      
    
    /*
     * This script adds custom tooltip functionality to elements with classes ".has-tooltip" and ".has-tooltip-xl":
     * - On mouse enter:
     *   - Retrieves the tooltip text from the element's data attribute
     *   - Calculates the element's position on the page
     *   - Chooses a tooltip CSS class based on the selector (.has-tooltip-xl uses a larger tooltip)
     *   - Applies an optional vertical offset from a data attribute or defaults to -35 pixels
     *   - Creates a tooltip div with the text and positions it absolutely near the element
     *   - Appends the tooltip to the body and stores a reference to it on the element
     * - On mouse leave:
     *   - Removes the tooltip element associated with the hovered element
    */    
    ['.has-tooltip', '.has-tooltip-xl'].forEach(selector => {
        $(document).on('mouseenter', selector, function () {
            const $this = $(this);
            const tooltipText = $this.data('tooltip');
            const offset = $this.offset();
            const tooltipClass = selector === '.has-tooltip-xl' ? 'espad-custom-tooltip-xl' : 'espad-custom-tooltip';

            const offsetTop = $this.data('offset-top') || -35;

            const $tooltip = $('<div></div>', {
                class: tooltipClass,
                text: tooltipText,
                css: {
                    top: offset.top + offsetTop, 
                    left: offset.left,
                    position: 'absolute'
                }
            });

            $('body').append($tooltip);
            $this.data('tooltipElement', $tooltip);
        });

        $(document).on('mouseleave', selector, function () {
            const $tooltip = $(this).data('tooltipElement');
            if ($tooltip) $tooltip.remove();
        });
    }); 
    
    /*
     * Enables or disables editing of email-related input fields:
     * - If 'enabled' is true, the fields become editable
     * - If 'enabled' is false, the fields become read-only
     * - Affects the fields with IDs: "subject", "sender_email", and "email_content"
    */    
    function toggleEmailFields(enabled) {
        $('#subject, #sender_email, #email_content').prop('readonly', !enabled);
    }

    toggleEmailFields($('#email_toggle').is(':checked'));

    $('#email_toggle').on('change', function() {
        toggleEmailFields($(this).is(':checked'));
    }); 
    
    /*
     * Updates the text of the subscription button in real-time:
     * - Listens for input events on the "#button_title" field inside the "#espd-update-recurring-product" form
     * - On input, sets the text content of all elements with the class ".subscription_btn" to the new value
    */    
    $('form#espd-update-recurring-product #button_title').on('input', function() {
        
        const neuerText = $(this).val();
        $('.subscription_btn').text(neuerText);
        
    });
    
    /*
     * Updates the size class of the subscription button dynamically:
     * - Listens for changes on the "#button_size" select element inside the "#espd-update-recurring-product" form
     * - Removes any existing button size classes matching the pattern "btn-*"
     * - Adds a new class corresponding to the selected size, e.g., "btn-small", "btn-large"
    */    
    $('form#espd-update-recurring-product #button_size').on('change', function() {
        
        const size = $(this).val(); 
        const $btn = $('.subscription_btn');

        $btn.removeClass(function(index, className) {
          return (className.match(/btn-\S+/g) || []).join(' ');
        });

        $btn.addClass('btn-' + size);
        
    });   
    
    /*
     * Changes the background color of the subscription button dynamically:
     * - Listens for changes on the "#button_color" input inside the "#espd-update-recurring-product" form
     * - Updates the "background-color" CSS property of all elements with the class ".subscription_btn" to the selected color
    */    
    $('form#espd-update-recurring-product #button_color').on('change', function() {
        
        const color = $(this).val(); 
        const $btn = $('.subscription_btn');

        $btn.css('background-color', color);
        
    }); 
    
    /*
     * Changes the font color of the subscription button dynamically:
     * - Listens for changes on the "#button_font_color" input inside the "#espd-update-recurring-product" form
     * - Updates the "color" CSS property of all elements with the class ".subscription_btn" to the selected font color
    */    
    $('form#espd-update-recurring-product #button_font_color').on('change', function() {
        
        const fontColor = $(this).val(); 
        const $btn = $('.subscription_btn');

        $btn.css('color', fontColor);
        
    });   
    
});