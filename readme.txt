=== Easy Stripe Payments & Donations ===
Contributors: ecosys365
Donate link: https://buymeacoffee.com/ecosys365
Tags: stripe, payments, subscription, donation, stripe checkout
Requires at least: 5.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.1.0 
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A light Stripe payment solution that automatically supports all current and future payment methods and currencies for one-time and recurring payments.

== Description ==

**Easy Stripe Payments & Donations** lets you integrate Stripe Checkout into your WordPress site with minimal setup. Perfect for donations, simple eCommerce needs, services, or subscriptions.

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

== Supported Payment Methods & Currencies ==

Easy Stripe Payments & Donations supports all payment methods offered by Stripe, as well as all currencies available through Stripe.
You can easily enable or disable payment methods directly from your Stripe Dashboard, and they will automatically appear in the checkout on your website — no additional configuration required.

**Popular Stripe Payment Methods Supported**

**Credit & debit cards** (Visa, MasterCard, American Express, Discover, etc.)
**Apple Pay**
**Google Pay**
**Klarna**
**PayPal via Stripe** (if enabled in your Stripe account)
**SEPA Direct Debit**
**Bancontact**
**iDEAL**
**Sofort**
**EPS**
**Giropay**
**Przelewy24 (P24)**
**BLIK**
**Afterpay / Clearpay**
**Alipay**
**WeChat Pay**
**Revolut Pay**
**OXXO**
**Pix**
**Amazon Pay**
**Affirm**
**Zip**
**Link by Stripe**
**MobilePay**
**Vipps Payments**
**Satispay**

**Common Currencies Supported**

Easy Stripe Payments & Donations accepts all currencies supported by Stripe, including major global currencies such as:

**USD** – United States Dollar
**EUR** – Euro
**GBP** – British Pound
**AUD** – Australian Dollar
**CAD** – Canadian Dollar
**CHF** – Swiss Franc
**JPY** – Japanese Yen
**SEK** – Swedish Krona
**NOK** – Norwegian Krone
**DKK** – Danish Krone
**NZD** – New Zealand Dollar
**SGD** – Singapore Dollar
**AED** - United Arab Emirates Dirham
**BRL** - Brazilian Real
**MXN** - Mexican Peso
**RON** - Romanian Leu 
**RUB** - Russian Ruble
**TRY** - Turkish Lira

Stripe automatically handles currency conversion and processing according to your account settings.
 
Other features include:

- Shortcode-based checkout button embedding
- Compatible with most themes and page builders
- Lightweight and privacy-conscious (no tracking or bloat)

Premium features (available via a separate service):

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

== Standalone Stripe Payment Solution — No WooCommerce Required ==

Easy Stripe Payments & Donations works completely standalone and does not require WooCommerce or any other eCommerce framework.  
The plugin is designed for simple, fast, and reliable Stripe payments in WordPress without the overhead of a full shop system.

Benefits of a standalone payment plugin include:

- Lightweight and fast performance without unnecessary dependencies  
- Simple setup with no products, carts, or checkout pages required  
- Lower maintenance and fewer compatibility issues  
- Better performance for landing pages, donations, and service payments  
- Full focus on Stripe payments without eCommerce complexity  

This makes Easy Stripe Payments & Donations ideal for websites that need Stripe payments without running a full online store.
  
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

= Which currencies are supported? =
The plugin supports all currencies supported by your Stripe account. Please note that your connected bank account must also be compatible with the currency. These are the supported currencies: USD, EUR, GBP, CNY, HKD, JPY, CHF, CAD, AUD, SGD, AED, AFN, ALL, AMD, ANG, AOA, ARS, AWG, AZN, BAM, BBD, BDT, BGN, BHD, BIF, BMD, BND, BOB, BRL, BSD, BTN, BWP, BYN, BZD, CDF, CLP, COP, CRC, CUP, CVE, CZK, DJF, DKK, DOP, DZD, EGP, ERN, ETB, FJD, FKP, GEL, GHS, GIP, GMD, GNF, GTQ, GYD, HNL, HRK, HTG, HUF, IDR, ILS, INR, IQD, IRR, ISK, JMD, KES, KGS, KHR, KMF, KRW, KWD, KYD, KZT, LAK, LBP, LKR, LRD, LSL, LTL, LVL, LYD, MAD, MDL, MGA, MKD, MMK, MNT, MOP, MRU, MUR, MVR, MWK, MXN, MYR, MZN, NAD, NGN, NIO, NOK, NPR, NZD, OMR, PAB, PEN, PGK, PHP, PKR, PLN, PYG, QAR, RON, RSD, RUB, RWF, SAR, SBD, SCR, SDG, SEK, SHP, SLL, SOS, SRD, SSP, STN, SYP, SZL, THB, TJS, TMT, TND, TOP, TRY, TTD, TWD, TZS, UAH, UGX, UYU, UZS, VEF, VND, VUV, WST, XAF, XCD, XOF, YER, ZAR

= Which payment methods does this plugin support? =
This plugin supports all payment methods that Stripe currently provides. The following payment methods are supported:
ACH Direct Debit, ACH Credit Transfer, Affirm, Afterpay/Clearpay, Alipay, Alma, Amazon Pay, American Express, Apple Pay, Bacs Direct Debit, Bancontact, Bank Transfers, BECS Direct Debit, Billie, BLIK, Boleto, Canadian Pre-authorized Debits (PADs), Capchase Pay, Cartes Bancaires, Cash App Pay, Cash-based Vouchers, China UnionPay, Click to Pay, Diners Club, Discover, EPS, FPX, Giropay, Google Pay, GrabPay, iDEAL, Interac, Japan Bank Transfer (Furikomi), JCB, Kakao Pay, Klarna, Konbini, Kriya, Link, Maestro, Mastercard, MB WAY, Mexico Bank Transfer (SPEI), MobilePay, Mondu, Multibanco, Naver Pay, OXXO, P24 (Przelewy24), Pay by Bank, PayCo, PayNow, PayPal, PayTo, Pix, PromptPay, Revolut Pay, Samsung Pay, Satispay, Scalapay, SEPA Direct Debit, SEPA Bank Transfer, SeQura, Sofort, South Korean Cards, Stablecoins (USDC/USDT), Sunbit, Swish, TWINT, UK Bank Transfer, USD Bank Transfer, Visa, WeChat Pay, Zip.

== Changelog ==

= 1.1.0 =
* Expanded the FAQ section to clearly document which Stripe payment methods are supported.
* Improved readme.txt clarity and structure.

= 1.0.9 =
* Improved the admin preview page by allowing users to copy the shortcode of the selected payment form directly.
* Updated and refined the readme.txt documentation.
* No changes to payment processing or plugin core functionality.

= 1.0.8 =
* Updated readme.txt to include more supported payment methods and currencies.
* Updated plugin assets by replacing images in the assets folder.
* No changes to plugin functionality.

= 1.0.7 =
* Replaced session-based payment validation with secure Stripe Payment Intent verification.
* Improved compatibility with caching plugins, CDNs, and Cloudflare setups.
* Enhanced payment reliability by validating each transaction directly against Stripe.

= 1.0.6 =
* Fixed an issue where the payment form was displayed incorrectly on mobile devices.

= 1.0.5 =
* Updated and refined the readme.txt documentation.
* Updated plugin assets (images) for improved presentation.

= 1.0.4 =
* Tested up to WordPress 6.9 to ensure full compatibility.
* Updated and improved the readme.txt file (added supported payment methods, currencies, and general documentation enhancements).

= 1.0.3 =
* Updating readme and assets

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
 
= 1.1.0 =
* Documentation update: Clarified supported Stripe payment methods.

= 1.0.9 =
* Improves usability in the admin preview page by adding a direct shortcode copy option and includes documentation updates. No changes to payment functionality.

= 1.0.8 =
* This update adds more supported payment methods and currencies to the documentation and refreshes plugin images. No functional changes.

= 1.0.7 =
* Improves payment security and reliability by replacing session-based validation with direct Stripe Payment Intent verification. 
 
= 1.0.6 =
* Fixes a mobile display issue with the payment form. Recommended update for users accepting payments on smartphones.

= 1.0.5 =
* This update includes documentation refinements and updated plugin assets. No functional changes.
 
= 1.0.4 =
* This update ensures full compatibility with WordPress 6.9 and includes important documentation improvements.

= 1.0.3 =
* Updated readme and plugin assets for clarity and presentation improvements.

= 1.0.2 =
* Updated documentation for clarity
* Plugin functionality remains unchanged

= 1.0.1 =
* Updated documentation for clarity and additional instructions
* Plugin functionality remains unchanged

= 1.0.0 =
Initial stable release with support for one-time payments, recurring payments, campaigns and Stripe subscriptions.
