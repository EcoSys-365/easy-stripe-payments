<?php

defined( 'ABSPATH' ) || exit; 

//Email
$email_enabled = get_option('espd_email_notification');

if ( $email_enabled == 1 ) {

    $subject = get_option('espd_email_subject');
    $from    = get_option('espd_email_sender_mail');
    $message = get_option('espd_email_mail_content');            

    $to = $email;

    // WordPress Site Domain holen
    $site_domain = wp_parse_url( home_url(), PHP_URL_HOST );
    // Alternative Absicherung, falls $from leer ist
    $from_email = sanitize_email( $from );

    if ( empty( $from_email ) ) $from_email = 'no-reply@' . $site_domain;

    // Header mit dynamischem From (ESPAD System + WordPress Domain)
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: <' . $from_email . '>',
    ];

    // Absätze in HTML umwandeln und sicher filtern
    $html_message = wpautop( wp_kses_post( $message ) );

    // Mail versenden
    wp_mail( $to, sanitize_text_field( $subject ), $html_message, $headers ); 

}

?>