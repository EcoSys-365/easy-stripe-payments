<?php

defined( 'ABSPATH' ) || exit; 

/**
 * Safely executes a Stripe API call and handles errors gracefully.
 *
 * @param callable $fn       The Stripe API call wrapped in a closure.
 * @param string   $errorMsg The error message to display if the call fails.
 * @return mixed|null        The result of the API call, or null on failure.
 */
function safeStripeCall(callable $fn, string $errorMsg) {
    try {
        return $fn();
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo esc_html($errorMsg . ': ' . $e->getMessage());
        return null;
    }
}
 
if ( defined('ESPAD_STRIPE_ACCESS') && ESPAD_STRIPE_ACCESS ) {
    
    // 1. Retrieve Stripe account information
    $account = safeStripeCall(fn() => \Stripe\Account::retrieve(), 'Error retrieving account');

    // 2. Count subscriptions
    $subscriptions = safeStripeCall(fn() => \Stripe\Subscription::all(['limit' => 100]), 'Error retrieving subscriptions');
    $subscription_count = $subscriptions ? count($subscriptions->data) : 0;

    // 3. Prepare payout data for chart display
    $payouts = safeStripeCall(fn() => \Stripe\Payout::all(['limit' => 100]), 'Error retrieving payouts');
    $payoutData = [];
    if ($payouts) {
        foreach ($payouts->data as $payout) {
            $payoutData[] = [
                'date' => gmdate('Y-m-d', $payout->created),
                'amount' => $payout->amount / 100,
            ];
        }
        payout_chart($payoutData, $account->id ?? null);
    }

    // 4. Count products and classify them by active/inactive status
    $products = safeStripeCall(fn() => \Stripe\Product::all(['limit' => 10]), 'Error retrieving products');
    $totalProducts = $activeProducts = $inactiveProducts = 0;

    if ($products) {
        foreach ($products->data as $product) {
            $totalProducts++;
            $prices = \Stripe\Price::all(['product' => $product->id]);

            foreach ($prices->data as $price) {
                $productDetail = \Stripe\Product::retrieve($price->product);
                if ($productDetail->active) $activeProducts++;
                else $inactiveProducts++;
            }
        }
    }

    // 5. Count customers with pagination support
    $totalCustomers = 0;
    $startingAfter = null;

    do {
        $customers = safeStripeCall(fn() => \Stripe\Customer::all([
            'limit' => 100,
            'starting_after' => $startingAfter,
        ]), 'Error retrieving customers');

        if (!$customers) break;

        $count = count($customers->data);
        $totalCustomers += $count;
        $startingAfter = $count ? end($customers->data)->id : null;

    } while ($startingAfter);

    // 6. Count failed payment attempts
    $paymentIntents = safeStripeCall(fn() => \Stripe\PaymentIntent::all(['limit' => 100]), 'Error retrieving payment intents');
    $failedCount = 0;

    if ($paymentIntents) {
        foreach ($paymentIntents->data as $intent) {
            if (in_array($intent->status, ['requires_payment_method', 'canceled'])) {
                $failedCount++;
            }
        }
    }

    // 7. Retrieve Stripe account balance
    $balance = safeStripeCall(fn() => \Stripe\Balance::retrieve(), 'Error retrieving Stripe balance');
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
    
}

