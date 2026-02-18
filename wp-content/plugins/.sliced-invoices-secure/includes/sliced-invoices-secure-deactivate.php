<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

register_deactivation_hook( SLICED_INVOICES_SECURE_FILE, 'sliced_secure_deactivate' );

/**
 * Plugin deactivation actions.
 *
 * @version 1.3.0
 * @since   1.0.0
 */
function sliced_secure_deactivate( $network_wide ) {
	
	wp_clear_scheduled_hook( 'sliced_invoices_secure_invoices_updater' );
	$updater = Sliced_Secure_Invoices_Updater::get_instance();
	$updater->updater_notices_clear();
	
	do_action( 'sliced_invoices_secure_deactivated', $network_wide );
	
}
