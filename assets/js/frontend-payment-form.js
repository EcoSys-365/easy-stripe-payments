jQuery(document).ready(function($) {
    
    // First enter of the page
    // Call updateButtonAmount once when the page first loads
    // Re-call updateButtonAmount every time the "amount" input changes within the payment form    
    updateButtonAmount();
 
    // On change call the function again
    $('#payment-form .btn-group input[name="amount"]').on('change', function() {
        
        updateButtonAmount();
    
    });    
    
    // Updates the displayed amount on the submit button based on the selected or fixed amount and currency.
    // If no currency is set, defaults to USD.
    // Formats the amount properly and updates the button text dynamically.    
    function updateButtonAmount() {
        
        const selectedValue = $('input[name="amount"]:checked').data('value');
        
        let currency = $('#currency').val();

        // Fallback auf USD, wenn kein Wert vorhanden ist
        if ( !currency || currency.trim() === '' ) {
            
            currency = 'USD';
        
        }
        
        if ( selectedValue ) {
            
            let formatted = formatCampaignAmount(selectedValue, currency);
            $('#submit #button-text sup').html(formatted + ' ' + currency.toUpperCase());
            
        } else {
            
            let fix_amount = $("#fix_amount").val();
            
            // Check for empty, "-" or null/undefined, and set fallback to 10
            if ( !fix_amount || fix_amount.trim() === '-' || isNaN(parseFloat(fix_amount)) ) {
                
                fix_amount = 100;
            
            }            
            
            let formatted = formatCampaignAmount(fix_amount, currency);
            $('#submit #button-text sup').html(formatted + ' ' + currency.toUpperCase());
            
        }
        
    }
    
    // Formats the given amount according to the currency's locale style:
    // - US-style thousands separator (comma) for USD, GBP, HKD, CAD, AUD, SGD
    // - European/Swiss-style thousands separator (dot) for EUR, CHF, CNY, JPY
    // Returns the formatted number as a string, or 0 if the input is invalid.    
    function formatCampaignAmount(amount, currency) {
        
        if (amount === null || amount === '' || isNaN(amount)) {
            amount = 0;
        }

        amount = parseInt(amount, 10); 

        switch (currency.toUpperCase()) {
            // Tausendertrennung mit Komma (US-Style)
            case 'USD':
            case 'GBP':
            case 'HKD':
            case 'CAD':
            case 'AUD':
            case 'SGD':
                return amount.toLocaleString('en-US');

            // Tausendertrennung mit Punkt (EU-/CH-Style)
            case 'EUR':
            case 'CHF':
            case 'CNY':
            case 'JPY':
                return amount.toLocaleString('de-DE');

            default:
                return amount;
        }
        
    }    
    
    // Checks if the given jQuery element is fully visible within the viewport.
    // Returns true if the entire element is visible, otherwise false.    
    function isInViewport(element) {
        
        var rect = element.get(0).getBoundingClientRect();
        return (
        rect.top >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight)
        );
        
    }      
    
    // Animate each progress bar only once when it becomes visible in the viewport
    function animateProgressBars() {
        
        $('.progress-bar-fill').each(function () {
            
            var $this = $(this);
            
            if (!$this.hasClass('animated') && isInViewport($this)) {
                
                var progress = $this.data('progress');
                $this.css('width', progress + '%');
                $this.addClass('animated');
                
            }
            
        });
        
    }

    // Run animateProgressBars on window scroll and load events
    $(window).on('scroll load', animateProgressBars); 
    
});