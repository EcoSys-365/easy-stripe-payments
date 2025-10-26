<?php

namespace ESPAD\Stripe;

defined( 'ABSPATH' ) || exit;

/**
 * StripeESPADManager
 *
 * Singleton class responsible for initializing the Stripe API with the
 * decrypted secret key stored in the WordPress options table.
 *
 * @package ESPAD\Stripe
 */

class StripeESPADManager {
    
    private $stripeSecretKey;
	private static $instance = null; // Holds the singleton instance
	private bool $initialized = false; // Flag to track Stripe initialization

	// Private constructor to prevent direct instantiation
	private function __construct() {
        
		$this->init_stripe(); // Initialize Stripe on construction
        
	}

	// Returns the singleton instance of the class
	public static function get_instance(): self {
        
		if ( self::$instance === null ) {
	
            self::$instance = new self();
		
        }
		
        return self::$instance;
	
    }

	// Initializes the Stripe API with the stored secret key
	private function init_stripe(): void {
        
		if ( $this->initialized ) return; // Prevent double initialization

		$stripe_secret_key = \get_option( 'espd_stripe_secret_key', '' );
        
		if ( ! empty( $stripe_secret_key ) ) {
            
			// Decrypt the key if a decryption function exists
			$decrypted_key = function_exists( 'espd_decrypt' )
				? \espd_decrypt( $stripe_secret_key )
				: $stripe_secret_key; // Fallback to raw key if no decryption
            
            $this->stripeSecretKey = $decrypted_key;

            try {
                // Set the API key for Stripe
                \Stripe\Stripe::setApiKey( $decrypted_key );
                $this->initialized = true; // Mark as initialized
            } catch (\Exception $e) {
                // Log Stripe initialization failure
                //error_log('Stripe init failed: ' . $e->getMessage());
            }            
		
        }
        
    }

	// Returns whether the Stripe instance has been initialized
	public function is_initialized(): bool {
	
        return $this->initialized;
	
    }
    
    // Returns a StripeClient instance initialized with the secret key.
    // If the secret key is not set, it initializes it first.    
    public function get_stripe_client() {
        
        if ( empty( $this->stripeSecretKey ) ) {
            
            $this->init_stripe(); 
            
        }
        
        return new \Stripe\StripeClient($this->stripeSecretKey);
        
    }
    
}

