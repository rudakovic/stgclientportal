<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

register_activation_hook( SLICED_INVOICES_STRIPE_FILE, 'sliced_invoices_stripe_activate' );

function sliced_invoices_stripe_activate( $network_wide ) {
	
	$translate = get_option( 'sliced_translate' );
	$translate['gateway-stripe-label'] = isset( $translate['gateway-stripe-label'] ) ? $translate['gateway-stripe-label'] : 'Pay with Stripe';
	update_option( 'sliced_translate', $translate );
	
	do_action( 'sliced_invoices_stripe_activated', $network_wide );
}
