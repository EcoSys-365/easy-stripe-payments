// If an element with the class 'shortcode_payment_form' exists on the page,
// automatically reload the current URL after 10 seconds.
// This is useful for payment methods like SEPA where confirmation may be delayed.
document.addEventListener('DOMContentLoaded', function () {
    
    if ( document.querySelector('.shortcode_payment_form') ) {
        
        setTimeout(function () {
            window.location.href = window.location.origin + window.location.pathname + window.location.search;
        }, 10000);
        
    }
    
});
