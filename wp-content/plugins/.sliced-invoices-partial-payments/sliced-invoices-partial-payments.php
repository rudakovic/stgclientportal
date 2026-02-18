<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Sliced Invoices Partial Payments
 * Plugin URI:        https://slicedinvoices.com/extensions/partial-payments/
 * Description:       Allow clients to pay less than the total invoice, based on your criteria. Requirements: The Sliced Invoices Plugin
 * Version:           1.1.2
 * Author:            Sliced Invoices
 * Author URI:        https://slicedinvoices.com/
 * Text Domain:       sliced-invoices-partial-payments
 * Domain Path:       /languages
 *
 * -------------------------------------------------------------------------------
 * Copyright © 2023 Sliced Software, LLC.  All rights reserved.
 * This software may not be resold, redistributed or otherwise conveyed to a third party.
 * -------------------------------------------------------------------------------
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'SLICED_INVOICES_PARTIAL_PAYMENTS_VERSION', '1.1.2' );
define( 'SLICED_INVOICES_PARTIAL_PAYMENTS_FILE', __FILE__ );
define( 'SLICED_INVOICES_PARTIAL_PAYMENTS_PATH', plugin_dir_path( __FILE__ ) );
define( 'SLICED_INVOICES_PARTIAL_PAYMENTS_URL', plugin_dir_url( __FILE__ ) );

require_once( SLICED_INVOICES_PARTIAL_PAYMENTS_PATH . 'admin/includes/class-sliced-invoices-partial-payments-admin.php' );
require_once( SLICED_INVOICES_PARTIAL_PAYMENTS_PATH . 'includes/class-sliced-partial-payments.php' );
require_once( SLICED_INVOICES_PARTIAL_PAYMENTS_PATH . 'includes/sliced-invoices-partial-payments-activate.php' );
require_once( SLICED_INVOICES_PARTIAL_PAYMENTS_PATH . 'includes/sliced-invoices-partial-payments-deactivate.php' );
require_once( SLICED_INVOICES_PARTIAL_PAYMENTS_PATH . 'updater/plugin-updater.php' );


/**
 * Make it so...
 */
function sliced_invoices_partial_payments_init() {
	Sliced_Partial_Payments::get_instance();
}
add_action( 'init', 'sliced_invoices_partial_payments_init' );
