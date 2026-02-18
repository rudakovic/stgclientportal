<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/* ==============================================================================
 * DATABASE UPDATES
 * ==============================================================================
 *
 * History:
 * 2018-04-12: DB 2, for Sliced Invoices Stripe versions < 1.7.1
 */
function sliced_invoices_stripe_db_update() {
	
	$settings = get_option( 'sliced_general' );
	
	if ( isset( $settings['stripe_db_version'] ) && $settings['stripe_db_version'] >= SLICED_INVOICES_STRIPE_DBVERSION ) {
		// all good
		return;
	}
	
	// upgrade from v1 to 2
	if ( ! isset( $settings['stripe_db_version'] ) || $settings['stripe_db_version'] < 2 ) {	
		$payment_settings = get_option( 'sliced_payments' );
		$payment_settings['stripe_enabled'] = 'on';
		update_option( 'sliced_payments', $payment_settings );
	}
	
	// @TODO: idea for future version
	/*
	// upgrade from v2 to 3
	if ( ! isset( $settings['stripe_db_version'] ) || $settings['stripe_db_version'] < 3 ) {
		// only do this if they were last on DBv2.
		// setting "onsite" maintains the same experience they were used to,
		// however future installations will use "hosted" by default.
		if ( $settings['stripe_db_version'] == 2 ) {
			$payment_settings = get_option( 'sliced_payments' );
			$payment_settings['stripe_checkout_type'] = 'onsite';
			update_option( 'sliced_payments', $payment_settings );
		}
	}
	*/
	
	// Done
	$settings['stripe_db_version'] = SLICED_INVOICES_STRIPE_DBVERSION;
	update_option( 'sliced_general', $settings );
	
}
add_action( 'init', 'sliced_invoices_stripe_db_update' );
