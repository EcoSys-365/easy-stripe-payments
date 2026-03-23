<?php

defined( 'ABSPATH' ) || exit; 

// Get encrypted account ID from DB
$encrypted_connected_account_id = get_option('espad_stripe_account_id');

// Decrypt Stripe account ID
$connected_account_id = espd_decrypt($encrypted_connected_account_id);

// Stripe Connect request options
$stripe_opts = ['stripe_account' => $connected_account_id];

// 1. Retrieve Stripe connected account information
$account = espad_safe_stripe_connect_call(
    fn() => \Stripe\Account::retrieve([], $stripe_opts),
    'Error retrieving connected account'
);

// 2. Count subscriptions
$subscriptions = espad_safe_stripe_connect_call(
    fn() => \Stripe\Subscription::all(['limit' => 100], $stripe_opts),
    'Error retrieving subscriptions'
);
$subscription_count = $subscriptions ? count($subscriptions->data) : 0;

// 3. Prepare payout data for chart display
$payouts = espad_safe_stripe_connect_call(
    fn() => \Stripe\Payout::all(['limit' => 100], $stripe_opts),
    'Error retrieving payouts'
);

$payoutData = [];

if ($payouts) {
    foreach ($payouts->data as $payout) {
        $payoutData[] = [
            'date'   => gmdate('Y-m-d', $payout->created),
            'amount' => $payout->amount / 100,
        ];
    }

    payout_chart($payoutData, $account->id ?? null);
}

// 4. Count products and classify them by active/inactive status
$products = espad_safe_stripe_connect_call(
    fn() => \Stripe\Product::all(['limit' => 10], $stripe_opts),
    'Error retrieving products'
);

$totalProducts = $activeProducts = $inactiveProducts = 0;

if ($products) {
    foreach ($products->data as $product) {
        $totalProducts++;

        $prices = espad_safe_stripe_connect_call(
            fn() => \Stripe\Price::all(['product' => $product->id], $stripe_opts),
            'Error retrieving prices'
        );

        if ($prices) {
            foreach ($prices->data as $price) {
                $productDetail = espad_safe_stripe_connect_call(
                    fn() => \Stripe\Product::retrieve($price->product, [], $stripe_opts),
                    'Error retrieving product details'
                );

                if ($productDetail) {
                    if ($productDetail->active) {
                        $activeProducts++;
                    } else {
                        $inactiveProducts++;
                    }
                }
            }
        }
    }
}

// 5. Count customers with pagination support
$totalCustomers = 0;
$startingAfter = null;

do {
    $customer_params = ['limit' => 100];

    if ($startingAfter) {
        $customer_params['starting_after'] = $startingAfter;
    }

    $customers = espad_safe_stripe_connect_call(
        fn() => \Stripe\Customer::all($customer_params, $stripe_opts),
        'Error retrieving customers'
    );

    if (!$customers) {
        break;
    }

    $count = count($customers->data);
    $totalCustomers += $count;
    $startingAfter = $count ? end($customers->data)->id : null;

} while ($startingAfter);

// 6. Count failed payment attempts
$paymentIntents = espad_safe_stripe_connect_call(
    fn() => \Stripe\PaymentIntent::all(['limit' => 100], $stripe_opts),
    'Error retrieving payment intents'
);

$failedCount = 0;

if ($paymentIntents) {
    foreach ($paymentIntents->data as $intent) {
        if (in_array($intent->status, ['requires_payment_method', 'canceled'], true)) {
            $failedCount++;
        }
    }
}

// 7. Retrieve Stripe account balance
$balance = espad_safe_stripe_connect_call(
    fn() => \Stripe\Balance::retrieve([], $stripe_opts),
    'Error retrieving Stripe balance'
);

$espad_amount = $espad_currency = $amount_pending = $currency_pending = null;

if ($balance) {
    if (!empty($balance->available)) {
        $available = $balance->available[0];
        $espad_amount = $available->amount / 100;
        $espad_currency = strtoupper($available->currency);
    }

    if (!empty($balance->pending)) {
        $pending = $balance->pending[0];
        $amount_pending = $pending->amount / 100;
        $currency_pending = strtoupper($pending->currency);
    }
}        
