<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

register_activation_hook( SLICED_INVOICES_PDF_FILE, 'sliced_pdf_email_activate' );

/**
 * Plugin activation actions.
 *
 * @version 1.8.0
 * @since   1.0.0
 */
function sliced_pdf_email_activate( $network_wide ) {
	
	$emails = get_option( 'sliced_emails' );
	
	// if a new install, set some default options
	if ( ! $emails ) {
		
		$emails['body_bg']           = isset( $emails['body_bg'] ) ? $emails['body_bg'] : '#eeeeee';
		$emails['header_bg']         = isset( $emails['header_bg'] ) ? $emails['header_bg'] : '#dddddd';
		$emails['content_bg']        = isset( $emails['content_bg'] ) ? $emails['content_bg'] : '#ffffff';
		$emails['content_color']     = isset( $emails['content_color'] ) ? $emails['content_color'] : '#444444';
		$emails['footer_bg']         = isset( $emails['footer_bg'] ) ? $emails['footer_bg'] : '#f6f6f6';
		$emails['footer_color']      = isset( $emails['footer_color'] ) ? $emails['footer_color'] : '#444444';
		$emails['footer']            = isset( $emails['footer'] ) ? $emails['footer'] :
			sprintf( 'Copyright %1s. %2s', date( 'Y' ), function_exists( 'sliced_get_business_name' ) ? sliced_get_business_name() : '' );
		$emails['quote_available']   = isset( $emails['quote_available'] ) ? $emails['quote_available'] :
			'Hi %client_first_name%,

			Please find attached our quote ( %number% ) for %client_business%.<br>
			';
		$emails['invoice_available'] = isset( $emails['invoice_available'] ) ? $emails['invoice_available'] :
			'Hi %client_first_name%,

			Please find attached our invoice ( %number% ) for %client_business%.<br>
			';
		
		update_option( 'sliced_emails', $emails );
		
	}
	
	do_action( 'sliced_invoices_pdf_activated', $network_wide );
	
}
