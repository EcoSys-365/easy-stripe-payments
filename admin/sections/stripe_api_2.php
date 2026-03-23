<?php defined( 'ABSPATH' ) || exit; ?>

 <div class="espad-wrapper">
  <div class="espad-column">
    <?php if ( isset($espad_amount) ) { ?>
      <div class="balance-box">
        <div class="balance-title"><?php echo esc_html(__( 'Available balance', 'easy-stripe-payments' )); ?></div>
        <div class="balance-amount"><?php echo esc_html($espad_amount . $espad_currency); ?></div>
      </div>
    <?php } ?>

    <?php if ( isset($amount_pending) ) { ?>
      <div class="balance-box">
        <div class="balance-title"><?php echo esc_html(__( 'Pending balance', 'easy-stripe-payments' )); ?></div>
        <div class="balance-amount"><?php echo esc_html($amount_pending . $currency_pending); ?></div>
      </div>
    <?php } ?>

    <div class="balance-box">
      <div class="balance-title"><?php echo esc_html(__( 'Stripe subscriptions', 'easy-stripe-payments' )); ?></div>
      <div class="balance-amount"><?php echo esc_html($subscription_count); ?></div>
    </div>

    <div class="balance-box">
      <div class="balance-title"><?php echo esc_html(__( 'Total Stripe products', 'easy-stripe-payments' )); ?></div>
      <div class="balance-amount"><?php echo esc_html($totalProducts); ?></div>
    </div>
  </div>

  <div class="espad-column">
    <div class="balance-box">
      <div class="balance-title"><?php echo esc_html(__( 'Active Stripe products', 'easy-stripe-payments' )); ?></div>
      <div class="balance-amount"><?php echo esc_html($activeProducts); ?></div>
    </div>

    <div class="balance-box">
      <div class="balance-title"><?php echo esc_html(__( 'Inactive Stripe products', 'easy-stripe-payments' )); ?></div>
      <div class="balance-amount"><?php echo esc_html($inactiveProducts); ?></div>
    </div>

    <div class="balance-box">
      <div class="balance-title"><?php echo esc_html(__( 'Failed Payments', 'easy-stripe-payments' )); ?></div>
      <div class="balance-amount"><?php echo esc_html($failedCount); ?></div>
    </div>

    <div class="balance-box">
      <div class="balance-title"><?php echo esc_html(__( 'Total number of Stripe customers', 'easy-stripe-payments' )); ?></div>
      <div class="balance-amount"><?php echo esc_html($totalCustomers); ?></div>
    </div>
  </div>
</div>