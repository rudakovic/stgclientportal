<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Sliced Invoices PDF Email
 * Plugin URI:        https://slicedinvoices.com/extensions/pdf-email/
 * Description:       Create PDF invoices and email them direct to clients. Requirements: The Sliced Invoices Plugin
 * Version:           1.8.1
 * Author:            Sliced Invoices
 * Author URI:        https://slicedinvoices.com/
 * Text Domain:       sliced-invoices-pdf-email
 * Domain Path:       /languages
 *
 * -------------------------------------------------------------------------------
 * Copyright © 2022 Sliced Software, LLC.  All rights reserved.
 * This software may not be resold, redistributed or otherwise conveyed to a third party.
 * -------------------------------------------------------------------------------
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'SLICED_INVOICES_PDF_VERSION', '1.8.1' );
define( 'SLICED_INVOICES_PDF_FILE', __FILE__ );
define( 'SLICED_INVOICES_PDF_PATH', plugin_dir_path( __FILE__ ) );

require_once( SLICED_INVOICES_PDF_PATH . 'admin/includes/class-sliced-pdf-admin.php' );
require_once( SLICED_INVOICES_PDF_PATH . 'includes/class-sliced-emails.php' );
require_once( SLICED_INVOICES_PDF_PATH . 'includes/class-sliced-pdf.php' );
require_once( SLICED_INVOICES_PDF_PATH . 'includes/class-sliced-pdf-email.php' );
require_once( SLICED_INVOICES_PDF_PATH . 'includes/sliced-invoices-pdf-activate.php' );
require_once( SLICED_INVOICES_PDF_PATH . 'includes/sliced-invoices-pdf-deactivate.php' );
require_once( SLICED_INVOICES_PDF_PATH . 'includes/sliced-invoices-pdf-third-party-compatibility.php' );
require_once( SLICED_INVOICES_PDF_PATH . 'updater/plugin-updater.php' ); 


/**
 * Make it so...
 */
function sliced_invoices_pdf_init() {
	Sliced_Pdf_Email::get_instance();
}
add_action( 'init', 'sliced_invoices_pdf_init', 5 ); // 5 calls this before any other Sliced extensions, except for Secure Invoices
