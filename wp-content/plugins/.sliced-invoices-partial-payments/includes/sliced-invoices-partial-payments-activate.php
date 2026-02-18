<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

register_activation_hook( SLICED_INVOICES_PARTIAL_PAYMENTS_FILE, 'sliced_partial_payments_activate' );

/**
 * Plugin activation actions.
 *
 * @version 1.1.0
 * @since   1.0.0
 */
function sliced_partial_payments_activate( $network_wide ) {
	
	$main = Sliced_Partial_Payments::get_instance();
	$main->new_taxonomy_terms();
	
	$email = get_option( 'sliced_emails' );
	$email_to_replace = 'Thanks for your payment, %client_first_name%.

Your recent payment for %total% on invoice %number% has been successful.<br>';
	if ( isset( $email['payment_received_client_content'] ) && $email['payment_received_client_content'] === $email_to_replace ) {
		$email['payment_received_client_content'] = 'Thanks for your payment, %client_first_name%.

Your recent payment for %last_payment% on invoice %number% has been successful.<br>';
		update_option( 'sliced_emails', $email );
	}
	
	do_action( 'sliced_invoices_partial_payments_activated', $network_wide );
	
}
