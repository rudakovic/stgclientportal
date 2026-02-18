<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function sliced_get_gateway_stripe_label() {
	$label = __( 'Pay with Stripe', 'sliced-invoices-stripe' );
	
	// For compatibility with Easy Translate Extension < v2.0.0. Will be removed soon.
	if (
		class_exists( 'Sliced_Translate' )
		&& defined( 'SI_TRANSLATE_VERSION' )
		&& version_compare( SI_TRANSLATE_VERSION, '2.0.0', '<' )
	) {
		$translate = get_option( 'sliced_translate' );
		if ( isset( $translate['gateway-stripe-label'] ) ) {
			$label = $translate['gateway-stripe-label'];
		}
	}
	
	return apply_filters( 'sliced_get_gateway_stripe_label', $label );
}
