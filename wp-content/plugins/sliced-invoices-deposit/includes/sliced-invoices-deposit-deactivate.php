<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

register_deactivation_hook( SLICED_INVOICES_DEPOSIT_FILE, 'sliced_invoices_deposit_deactivate' );

/**
 * Plugin deactivation actions.
 *
 * @version 2.4.0
 * @since   1.0.0
 */
function sliced_invoices_deposit_deactivate( $network_wide ) {
	
	wp_clear_scheduled_hook( 'sliced_invoices_deposit_invoices_updater' );
	$updater = Sliced_Deposit_Invoices_Updater::get_instance();
	$updater->updater_notices_clear();
	
	do_action( 'sliced_invoices_deposit_deactivated', $network_wide );
	
}
