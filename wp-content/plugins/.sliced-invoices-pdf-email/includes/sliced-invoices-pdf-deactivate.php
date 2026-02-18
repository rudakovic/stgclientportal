<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

register_deactivation_hook( SLICED_INVOICES_PDF_FILE, 'sliced_pdf_email_deactivate' );

/**
 * Plugin deactivation actions.
 *
 * @version 1.8.0
 * @since   1.0.0
 */
function sliced_pdf_email_deactivate( $network_wide ) {
	
	wp_clear_scheduled_hook( 'sliced_invoices_pdf_invoice_updater' );
	$updater = Sliced_Pdf_Invoice_Updater::get_instance();
	$updater->updater_notices_clear();
	
	if ( class_exists( 'Sliced_Admin_Notices' ) ) {
		Sliced_Admin_Notices::remove_notice( 'pdf_invoice_low_memory_warning' );
		Sliced_Admin_Notices::remove_notice( 'pdf_invoice_mbstring_missing' );
		Sliced_Admin_Notices::remove_notice( 'pdf_invoice_wp_super_cache_warning' );
	}
	
	do_action( 'sliced_invoices_pdf_deactivated', $network_wide );
	
}
