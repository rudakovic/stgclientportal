<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

register_deactivation_hook( SLICED_INVOICES_PARTIAL_PAYMENTS_FILE, 'sliced_partial_payments_deactivate' );

/**
 * Plugin deactivation actions.
 *
 * @version 1.1.0
 * @since   1.0.0
 */
function sliced_partial_payments_deactivate( $network_wide ) {
	
	wp_clear_scheduled_hook( 'sliced_invoices_partial_payments_updater' );
	$updater = Sliced_Partial_Payments_Updater::get_instance();
	$updater->updater_notices_clear();
	
	if ( class_exists( 'Sliced_Admin_Notices' ) ) {
		Sliced_Admin_Notices::remove_notice( 'partial_payments_core_update_needed' );
		Sliced_Admin_Notices::remove_notice( 'partial_payments_2checkout_update_needed' );
		Sliced_Admin_Notices::remove_notice( 'partial_payments_braintree_update_needed' );
		Sliced_Admin_Notices::remove_notice( 'partial_payments_stripe_update_needed' );
	}
	
	do_action( 'sliced_invoices_partial_payments_deactivated', $network_wide );
	
}
