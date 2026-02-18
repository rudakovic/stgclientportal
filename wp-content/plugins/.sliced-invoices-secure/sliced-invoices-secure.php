<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Sliced Invoices Secure
 * Plugin URI:        https://slicedinvoices.com/extensions/secure-invoices/
 * Description:       Provide secure links for your users to view their quotes and invoices. Requirements: The Sliced Invoices Plugin
 * Version:           1.3.0
 * Author:            Sliced Invoices
 * Author URI:        https://slicedinvoices.com/
 * Text Domain:       sliced-invoices-secure
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

/**
 * Initialize
 */
define( 'SLICED_INVOICES_SECURE_VERSION', '1.3.0' );
define( 'SLICED_INVOICES_SECURE_FILE', __FILE__ );
define( 'SLICED_INVOICES_SECURE_PATH', plugin_dir_path( __FILE__ ) );

require_once( SLICED_INVOICES_SECURE_PATH . 'admin/includes/class-sliced-invoices-secure-admin.php' );
require_once( SLICED_INVOICES_SECURE_PATH . 'includes/class-sliced-secure.php' );
require_once( SLICED_INVOICES_SECURE_PATH . 'includes/sliced-invoices-secure-activate.php' );
require_once( SLICED_INVOICES_SECURE_PATH . 'includes/sliced-invoices-secure-deactivate.php' );
require_once( SLICED_INVOICES_SECURE_PATH . 'updater/plugin-updater.php' );


/**
 * Make it so...
 */
function sliced_invoices_secure_init() {
	Sliced_Secure::get_instance();
}
add_action( 'plugins_loaded', 'sliced_invoices_secure_init', 999 );
