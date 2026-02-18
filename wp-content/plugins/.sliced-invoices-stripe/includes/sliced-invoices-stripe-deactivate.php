<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

register_deactivation_hook( SLICED_INVOICES_STRIPE_FILE, 'sliced_invoices_stripe_deactivate' );

function sliced_invoices_stripe_deactivate( $network_wide ) {
	
	wp_clear_scheduled_hook( 'sliced_invoices_stripe_gateway_updater' );
	$updater = Sliced_Stripe_Gateway_Updater::get_instance();
	$updater->updater_notices_clear();
	
	if ( class_exists( 'Sliced_Admin_Notices' ) ) {
		Sliced_Admin_Notices::remove_notice( 'sliced_stripe_old_vendor_library' );
	}
	
	do_action( 'sliced_invoices_stripe_deactivated', $network_wide );
}
