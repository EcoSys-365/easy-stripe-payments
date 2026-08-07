jQuery(document).ready(function($) {
    
    // Preview Page - Listen for changes on the .size-changer dropdown inside the #espad_page
    $('#espad_page .size-changer').on('change', function() {

        const value = $(this).val(); // Get selected value (desktop, tablet, or mobile)
        const box = $('#payment-form'); // Target the payment form container

        // Remove all previous size classes
        box.removeClass('desktop tablet mobile');

        // Hide all size-specific headings
        $('#espad_page h2.desktop, #espad_page h2.tablet, #espad_page h2.mobile').css("display", "none");

        // Apply styles for desktop layout
        if ( value === 'desktop' ) {

            box.addClass('desktop');
            $('#espad_page h2.desktop').css("display", "block");

            $(".campaign-image-box").css("width", "47%");
            $(".espad-payment-box").css("width", "50%");

            $(".progress-label strong").css("font-size", "26px");
            
            $("#espad_page #payment-form .col-md").css("flex", "1 0 0%");
            $("#espad_page #payment-form .panel-body div.row .col-md:first-of-type").css("margin-bottom", "0px");                

        // Apply styles for tablet layout
        } else if ( value === 'tablet' ) {

            box.addClass('tablet');
            $('#espad_page h2.tablet').css("display", "block");

            $('.prev-mode-Campaign #prices_box div.btn-group').css("display", "block");

            $('#amountInput').css("padding", "13px");
            $('.prev-mode-Campaign #amountInput').css("margin-top", "5px");
            $('#amountInput').css("width", "100%");
            
            $('.prev-mode-Standard #amountInput').css("height", "59px");

            $(".campaign-image-box").css("width", "47%");
            $(".espad-payment-box").css("width", "50%");

            $(".progress-label strong").css("font-size", "18px");
            $(".prev-mode-Standard #prices_box label.btn").css("line-height", "31px");
            
            $("#espad_page #payment-form .col-md").css("flex", "0 1 auto");
            $("#espad_page #payment-form .panel-body div.row .col-md:first-of-type").css("margin-bottom", "17px");

        // Apply styles for mobile layout
        } else if ( value === 'mobile' ) {

            box.addClass('mobile');
            $('#espad_page h2.mobile').css("display", "block");

            $(".campaign-image-box").css("width", "100%");
            $(".espad-payment-box").css("width", "100%");

            $('.prev-mode-Campaign #prices_box div.btn-group').css("display", "block");

            $('#amountInput').css("padding", "13px");
            $('.prev-mode-Campaign #amountInput').css("margin-top", "5px");
            $('#amountInput').css("width", "100%");

            $(".progress-label strong").css("font-size", "26px");
            
            $("#espad_page #payment-form .col-md").css("flex", "0 1 auto");
            $("#espad_page #payment-form .panel-body div.row .col-md:first-of-type").css("margin-bottom", "17px");            

        }

    });
    
    /*
     * Handles the change event on the element with ID "preview_form_id":
     * - Retrieves the selected form ID from the input
     * - If a form ID is selected, updates the current URL's 'form_id' query parameter with the selected ID
     * - Redirects the browser to the updated URL, triggering a page reload with the new form ID
    */    
    $('#preview_form_id').on('change', function() {
        
        var selectedFormId = $(this).val();

        if ( selectedFormId ) {
            
            var newUrl = new URL(window.location.href);
            newUrl.searchParams.set('form_id', selectedFormId);
            window.location.href = newUrl.toString();
            
        }
        
    });   
    
    /*
     * Handles click events on elements with the class "add_new_stripe_product":
     * - Prevents the default link or button behavior
     * - Toggles the visibility of the element with the class "new_stripe_product_box" using a sliding animation
    */
    $('.add_new_stripe_product').on('click', function(e) {
        
        e.preventDefault(); 
        $('.new_stripe_product_box').slideToggle(); 
        
    });
    
    /*
     * This script moves the element with class "espad_powered_by_box" into the WordPress footer ("#wpfooter")
     * to ensure it is displayed within the admin footer area, if both elements are present on the page.    
    */ 
    var $footer = $('#wpfooter');
    var $poweredBy = $('.espad_powered_by_box');

    if ( $footer.length && $poweredBy.length ) {
        
        $poweredBy.appendTo($footer);
        
    }    
    
});

