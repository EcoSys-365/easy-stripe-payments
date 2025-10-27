=== Easy Stripe Payments & Donations ===
Contributors: ecosys365
Donate link: https://buymeacoffee.com/ecosys365
Tags: stripe, payments, subscription, donation, stripe checkout
Requires at least: 5.5
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight and flexible Stripe payment solution for WordPress. Accept one-time or recurring payments with a customizable Stripe Checkout interface.

== Description ==

**Easy Stripe Payments** lets you integrate Stripe Checkout into your WordPress site with minimal setup. Perfect for donations, simple eCommerce needs, services, or subscriptions.

https://www.youtube.com/watch?v=iPNCHNTb_CI

== Screenshots ==
 
1. Campaign Checkout example with donation total
2. Example of a physical product (Smartwatch) integrated with Stripe Checkout
3. Demo of an annual fitness or coaching program with custom Stripe Checkout form, image, and description
4. Admin settings page (Stripe Checkout configuration)
5. Example of received payments in the WordPress Dashboard
6. Settings to manage confirmation emails (toggle on/off)
7. Create a new Stripe subscription product without logging into Stripe
8. Edit an existing Stripe subscription product and link it with a checkout button
9. Example of a Stripe-hosted checkout page for subscription payments

== Three checkout modes included: ==

1. **Standard Checkout** Accept one-time payments with customizable product name, amount, and currency.  
2. **Campaign Checkout** Track fundraising campaigns with a frontend progress display showing how much has already been collected.  
3. **Stripe Subscription Button** Let users subscribe to recurring payments (monthly/yearly) using Stripe's subscription system.
 
Other features include:

- Easy Stripe API integration (Live/Test)
- Shortcode-based checkout button embedding
- Lightweight and privacy-conscious (no tracking or bloat)
- Compatible with most themes and page builders

**Premium features** (available via a separate service):

- Domain-based license activation
- Priority support
- Stripe metadata (e.g. campaign name, custom user data)
- Multiple Stripe checkouts and multiple Stripe subscription payments

Note: This plugin requires a free [Stripe account](https://stripe.com). No additional fees are charged by the plugin author.

== Installation == 

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate via the “Plugins” menu in WordPress.
3. Go to **Stripe Payments > Settings** in your admin dashboard.
4. Enter your Stripe API keys (Live or Test).
5. Create payment forms for one-time payments, campaigns, or subscriptions using the intuitive form builder.
6. Use shortcodes to place payment buttons or forms wherever needed on your site.
  
== External services ==
   
This plugin relies on external services to check premium membership status and to securely process payments via Stripe.  
All external connections are necessary for the plugin to provide its full functionality, and users sensitive data is handled securely according to the respective service’s privacy policies.

=== EcoSys365 API ===

This plugin connects to the external EcoSys365 API service (https://api.ecosys365.com).  
The connection is required in order to check whether the current domain has a valid premium membership status.

- Data sent: the domain name of the WordPress installation (no user or visitor personal data).  
- Purpose: to confirm whether the domain is marked as "premium", so premium features can be enabled or disabled.  
- Service provider: EcoSys 365 Solutions LLC  
  - Terms of Service: https://ecosys365.com/terms  
  - Privacy Policy: https://ecosys365.com/privacy

=== Domain Registration Service ===

This plugin also connects to the external domain registration service www.payments-and-donations.com.
The connection is used when a site administrator registers their domain for premium membership activation.

- Data sent: the domain name of the WordPress installation (no personal data from users or visitors).
- Purpose: to register the current domain for premium membership.
- Service provider: Payments and Donations (https://www.payments-and-donations.com)
  - Terms of Service: https://www.payments-and-donations.com/terms-conditions
  - Privacy Policy: https://www.payments-and-donations.com/privacy-policy

=== Stripe JS Library ===
  
The plugin loads the official Stripe JavaScript library from https://js.stripe.com/v3.  
This is required to securely handle payment forms and communication with Stripe’s payment gateway.

- Data sent: the library may collect payment-related information entered by users during checkout (e.g., card details), which is sent directly to Stripe’s servers and not processed or stored by this plugin.  
- Purpose: to enable secure payment processing via Stripe.  
- Service provider: Stripe  
  - Terms of Service: https://stripe.com/legal  
  - Privacy Policy: https://stripe.com/privacy

=== Stripe API (via PHP SDK) ===
  
This plugin uses the official Stripe PHP SDK to communicate with the Stripe API (https://api.stripe.com).  
These requests are required to manage payments, subscriptions, and products in the WordPress Dashboard, as well as to enable Stripe Checkout for end users on the frontend.

- Data sent: payment-related details such as product information, subscription data, and customer details that are required for processing payments.  
  Sensitive payment information (e.g., credit card numbers) is sent directly to Stripe and never stored by this plugin or on the WordPress site.  
- Purpose: to process payments, create subscriptions, and manage related resources in Stripe.  
- Service provider: Stripe  
  - Terms of Service: https://stripe.com/legal  
  - Privacy Policy: https://stripe.com/privacy
 
== Frequently Asked Questions ==

= Is this plugin free? =  
Yes, core functionality is free. Premium upgrades are available separately via external service.

= Can I track donations for campaigns? =  
Yes, the Campaign Checkout mode shows the current collected total on the frontend using a shortcode. The payments are also displayed in the WordPress dashboard and stored in a separate WordPress database table.

= Can I accept recurring payments? =  
Yes, using the **Stripe Subscription Button**, which integrates with Stripe’s recurring billing system.

= Are there any trial or locked features? =  
No. All code in this plugin is fully usable. Premium features are provided via a separate extension in compliance with WordPress.org guidelines.

= Does this plugin store credit card data? =  
No. All payment information is securely handled by Stripe.

== Changelog ==

= 1.0.2 =
* Documentation update
* No changes to plugin functionality

= 1.0.1 =
* Documentation update: improvements and clarifications in readme.txt
* No changes to plugin functionality
 
= 1.0.0 =
* Initial release of Easy Stripe Payments
* Standard Checkout for one-time payments
* Campaign Checkout with frontend donation total
* Stripe Subscription Button for recurring payments

== Upgrade Notice ==

= 1.0.2 =
* Updated documentation for clarity
* Plugin functionality remains unchanged

= 1.0.1 =
* Updated documentation for clarity and additional instructions
* Plugin functionality remains unchanged

= 1.0.0 =
Initial stable release with support for one-time payments, recurring payments, campaigns and Stripe subscriptions.
