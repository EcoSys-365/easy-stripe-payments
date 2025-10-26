<?php defined( 'ABSPATH' ) || exit; ?>

<?php
    
if (
    isset($_POST['email_submit']) &&
    isset($_POST['espad_mail_form_nonce']) &&
    wp_verify_nonce(
        sanitize_text_field( wp_unslash( $_POST['espad_mail_form_nonce'] ) ),
        'espad_mail_form_action'
    )
) {    
     
    $subject       = isset($_POST['subject']) ? sanitize_text_field( wp_unslash($_POST['subject']) ) : '';
    $sender_email  = isset($_POST['sender_email']) ? sanitize_text_field( wp_unslash($_POST['sender_email']) ) : '';
    $email_content = isset($_POST['email_content']) ? wp_kses_post( wp_unslash($_POST['email_content']) ) : '';
    
    $is_enabled = isset($_POST['email_toggle']) ? 1 : 0;
     
    update_option('espd_email_notification', $is_enabled);
    update_option('espd_email_subject',      $subject);
    update_option('espd_email_sender_mail',  $sender_email);
    update_option('espd_email_mail_content', $email_content);  
    
    echo '<div class="notice notice-success"><p>'; echo esc_html(__( 'Successfully saved', 'easy-stripe-payments' )); echo '</p></div>';
     
}
  
?>

<h2><?php echo esc_html(__( 'Confirmation Emails', 'easy-stripe-payments' )); ?></h2>
<p><?php echo esc_html(__( 'Set up confirmation Emails for your users and configure them to be automatically sent upon each successful payment.', 'easy-stripe-payments' )); ?></p>

<?php

$db_email_notification = get_option('espd_email_notification');
$db_mail_content       = get_option('espd_email_mail_content');
$db_email_subject      = get_option('espd_email_subject');
$db_sender_mail        = get_option('espd_email_sender_mail');
     
if (
    isset($_POST['email_submit']) &&
    isset($_POST['espad_mail_form_nonce']) &&
    wp_verify_nonce(
        sanitize_text_field( wp_unslash( $_POST['espad_mail_form_nonce'] ) ),
        'espad_mail_form_action'
    ) &&
    (int) $db_email_notification === 0
) {
    
    ?>

    <div id="espad-emails-disabled"></div>

    <?php
    
} else if (
    isset($_POST['email_submit']) &&
    isset($_POST['espad_mail_form_nonce']) &&
    wp_verify_nonce(
        sanitize_text_field( wp_unslash( $_POST['espad_mail_form_nonce'] ) ),
        'espad_mail_form_action'
    ) &&
    (int) $db_email_notification === 1
) {   
    
    ?>

    <div id="espad-emails-enabled"></div>

    <?php  
     
}
  
$email_form_checked = $db_email_notification ? 'checked' : '';

if ( empty( $db_email_subject ) ) {
    $db_email_subject = __( '✅ Payment Confirmation – Thank You', 'easy-stripe-payments' );
}

if ( empty( $db_mail_content ) ) {
    
    $db_mail_content = "
Dear Customer,

Thank you very much for your payment. We have successfully received your transaction and appreciate your prompt action.

If you have any questions or need further assistance, feel free to contact us at any time.

Best regards,
[Your Company Name]";

} 
 
echo '<div class="email_configure_box">';

echo '<form method="post">';

    wp_nonce_field('espad_mail_form_action', 'espad_mail_form_nonce');

    echo '<table class="form-table">

        <tr>
          <th scope="row"><label for="email_toggle">'; echo esc_html(__( 'Enable Email Notification', 'easy-stripe-payments' )); echo '</label></th>
          <td>
            <label class="switch">
              <input type="checkbox" name="email_toggle" id="email_toggle" value="1"'; echo esc_html($email_form_checked); echo '>
              <span class="slider round"></span>
            </label>
          </td>
        </tr>
 
        <tr>
            <th><label for="subject">'; echo esc_html(__( 'Subject', 'easy-stripe-payments' )); echo '</label></th>
            <td><input name="subject" id="subject" type="text" class="regular-text" value="'; echo esc_html($db_email_subject); echo '" readonly required></td>
        </tr>
        
        <tr>
            <th><label for="sender_email">'; echo esc_html(__( 'Sender Email', 'easy-stripe-payments' )); echo '</label></th>
            <td><input name="sender_email" id="sender_email" type="email" class="regular-text" placeholder="mail@'; echo esc_html(ESPAD_SITE_DOMAIN); echo '" value="'; echo esc_html($db_sender_mail); echo '" readonly required></td>
        </tr>        

        <tr>
            <th><label for="email_content">'; echo esc_html(__( 'Mail Content', 'easy-stripe-payments' )); echo '</label></th>
            <td>
            <textarea name="email_content" id="email_content" rows="12" cols="60" class="regular-text" readonly required>';
 
            echo esc_textarea( $db_mail_content );
             
            echo '</textarea>
            </td>
        </tr>

    </table>';

    echo '<input 
        type="submit" 
        name="email_submit" 
        id="email_submit" 
        class="button button-primary" 
        style="padding: 1px 16px; font-size: 16px;" 
        value="'; echo esc_html(__( 'Save', 'easy-stripe-payments' )); echo '">';

echo '</form>';

echo '</div>';

?>
