<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

register_activation_hook( SLICED_INVOICES_SECURE_FILE, 'sliced_secure_activate' );

/**
 * Plugin activation actions.
 *
 * @version 1.3.0
 * @since   1.0.0
 */
function sliced_secure_activate( $network_wide ) {
	
	// Add hash to database for all existing users.
	$user_ids = get_users( array( 'fields' => 'id' ) ); 
	if ( $user_ids ) {
		foreach ( $user_ids as $user_id ) {
			$existing = get_user_meta( $user_id, '_sliced_secure_hash', true );
			if( empty( $existing ) ) {
				Sliced_Secure::store_users_hash( $user_id );
			}
		}
	}
	
	do_action( 'sliced_invoices_secure_activated', $network_wide );
	
}
