<?php defined( 'ABSPATH' ) || exit; ?>

<div class="wrap">

    <h1 class="espad_headline"><span class="dashicons dashicons-money-alt"></span> &nbsp;&nbsp;<?php echo esc_html(__( 'Easy Stripe Payments &amp; Donations', 'easy-stripe-payments' )); ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="?page=espd_main&tab=welcome" class="nav-tab <?php echo esc_attr( $active_tab == 'welcome' ? 'nav-tab-active' : ''); ?>">
            <?php echo esc_html(__( 'Overview', 'easy-stripe-payments' )); ?> &#10024;
        </a>
        <a href="?page=espd_main&tab=setup" class="nav-tab <?php echo esc_attr( $active_tab == 'setup' ? 'nav-tab-active' : ''); ?>">
            <?php echo esc_html(__( 'Setup', 'easy-stripe-payments' )); ?>  &#9881;
        </a>            
        <a href="?page=espd_main&tab=payments" class="nav-tab <?php echo esc_attr( $active_tab == 'payments' ? 'nav-tab-active' : ''); ?>">
            <?php echo esc_html(__( 'Payments', 'easy-stripe-payments' )); ?> &#128176;
        </a>            
        <a href="?page=espd_main&tab=forms" class="nav-tab <?php echo esc_attr( $active_tab == 'forms' ? 'nav-tab-active' : ''); ?>">
            <?php echo esc_html(__( 'Payment Forms', 'easy-stripe-payments' )); ?> &#128221;
        </a> 
        <a href="?page=espd_main&tab=preview" class="nav-tab <?php echo esc_attr( $active_tab == 'preview' ? 'nav-tab-active' : ''); ?>">
            <?php echo esc_html(__( 'Preview', 'easy-stripe-payments' )); ?> &#128270;           
        </a>          
        <a href="?page=espd_main&tab=recurring" class="nav-tab <?php echo esc_attr( $active_tab == 'recurring' ? 'nav-tab-active' : ''); ?>">
            <?php echo esc_html(__( 'Recurring Payments', 'easy-stripe-payments' )); ?> &#128260;           
        </a>                
        <a href="?page=espd_main&tab=settings" class="nav-tab <?php echo esc_attr( $active_tab == 'settings' ? 'nav-tab-active' : ''); ?>">
            <?php echo esc_html(__( 'Settings', 'easy-stripe-payments' )); ?> &#128295;
        </a>            
        <a href="?page=espd_main&tab=mails" class="nav-tab <?php echo esc_attr( $active_tab == 'mails' ? 'nav-tab-active' : ''); ?>">
            <?php echo esc_html(__( 'Emails', 'easy-stripe-payments' )); ?> &#9993;         
        </a>            
        <a href="?page=espd_main&tab=premium" class="nav-tab <?php echo esc_attr( $active_tab == 'premium' ? 'nav-tab-active' : ''); ?>">
            <?php echo esc_html(__( 'Premium', 'easy-stripe-payments' )); ?> &#9733;        
        </a>             
        <a href="?page=espd_main&tab=help" class="nav-tab <?php echo esc_attr( $active_tab == 'help' ? 'nav-tab-active' : ''); ?>">
            <?php echo esc_html(__( 'Help &amp; FAQ', 'easy-stripe-payments' )); ?> &#10067;
        </a>             
    </h2>
 
    <div class="tab-content espad-tab">

        <?php

        switch ( $active_tab ) {

            case 'setup':
                require_once ESPAD_PLUGIN_PATH . 'admin/setup.php';
                break; 

            case 'settings':
                require_once ESPAD_PLUGIN_PATH . 'admin/settings.php';
                break;                    

            case 'payments':
                require_once ESPAD_PLUGIN_PATH . 'admin/payments.php';
                break;

            case 'forms':
                require_once ESPAD_PLUGIN_PATH . 'admin/forms.php';
                break;                    

            case 'preview':
                require_once ESPAD_PLUGIN_PATH . 'admin/preview.php';
                break;

            case 'recurring':
                require_once ESPAD_PLUGIN_PATH . 'admin/recurring.php';
                break;                    

            case 'mails':
                require_once ESPAD_PLUGIN_PATH . 'admin/mails.php';
                break;   

            case 'premium':
                require_once ESPAD_PLUGIN_PATH . 'admin/premium.php';
                break;                     

            case 'help':
                require_once ESPAD_PLUGIN_PATH . 'admin/help.php';
                break;                    

            default:
                require_once ESPAD_PLUGIN_PATH . 'admin/welcome.php';
                break;

        }

        ?>

    </div>

</div>