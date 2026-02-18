<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Sliced Invoices Deposit
 * Plugin URI:        https://slicedinvoices.com/extensions/deposit-invoices/
 * Description:       Create deposit invoices with the click of a button. Requirements: The Sliced Invoices Plugin
 * Version:           2.4.0
 * Author:            Sliced Invoices
 * Author URI:        https://slicedinvoices.com/
 * Text Domain:       sliced-invoices-deposit
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

define( 'SLICED_INVOICES_DEPOSIT_VERSION', '2.4.0' );
define( 'SLICED_INVOICES_DEPOSIT_FILE', __FILE__ );
define( 'SLICED_INVOICES_DEPOSIT_PATH', plugin_dir_path( __FILE__ ) );
define( 'SLICED_INVOICES_DEPOSIT_URL', plugin_dir_url( __FILE__ ) );

require_once( SLICED_INVOICES_DEPOSIT_PATH . 'admin/includes/class-sliced-invoices-deposit-admin.php' );
require_once( SLICED_INVOICES_DEPOSIT_PATH . 'includes/class-sliced-deposit.php' );
require_once( SLICED_INVOICES_DEPOSIT_PATH . 'includes/sliced-invoices-deposit-deactivate.php' );
require_once( SLICED_INVOICES_DEPOSIT_PATH . 'updater/plugin-updater.php' );


/**
 * Make it so...
 */
function sliced_invoices_deposit_init() {
	Sliced_Deposit::get_instance();
}
add_action( 'init', 'sliced_invoices_deposit_init' );
