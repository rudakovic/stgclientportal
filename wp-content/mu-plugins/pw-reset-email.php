<?php
/**
 * Plugin Name: Password Reset Email Text
 * Description: Edit the text of pw resset email text
 * Author: Milos Milosevic
 * Author URI: https://valet.io
 * Version: 1.0
 */
function wp_set_html_mail_content_type() {
    return 'text/html';
}
add_filter( 'wp_mail_content_type', 'wp_set_html_mail_content_type' );

add_filter("retrieve_password_message", "my_reset_password_message", 99, 4);

function my_reset_password_message($message, $key, $user_login, $user_data )    {

    $message = "The password for the following account has been requested to be reset:

    " . sprintf(__('%s'), $user_data->user_email) . "<br>

    If this was a mistake, just ignore this email and nothing will happen.

    To reset your password, visit the following address:<br>

    <a href='" . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . "'>Click here to resset your password.</a>";

    return $message;

}

    add_filter('wp_new_user_notification_email', 'change_notification_message', 10, 3);

    function change_notification_message( $wp_new_user_notification_email, $user, $blogname ) {

        // Generate a new key
        $key = get_password_reset_key( $user );

        // Set the subject
        $wp_new_user_notification_email['subject'] = __('Welcome to Valet portal!');

        // Put the username in the message
        $message = sprintf(__('Your Username is: %s'), $user->user_login) . "\r\n\r\n";
        // Give your user the link to reset her password 
        $message .= __('To set your password, please visit the following address:') . "\r\n\r\n";

        $message .= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login') . "\r\n\r\n";


        // Set the email's message
        $wp_new_user_notification_email['message'] = $message;

        return $wp_new_user_notification_email;
    }