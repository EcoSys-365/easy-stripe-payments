/**
 * Executes a client-side redirect using the URL passed from PHP via wp_localize_script.
 * This is triggered once the DOM is fully loaded.
 */
jQuery(document).ready(function($) {
    
    // Check if the global data object and the target URL are set by PHP
    if ( typeof espadRedirectData !== 'undefined' && espadRedirectData.redirectUrl ) {
        
        var targetUrl = espadRedirectData.redirectUrl;
        
        // Perform the JavaScript redirect
        window.location.href = targetUrl;

        // Stop further execution
        return false; 
         
    }  
    
});