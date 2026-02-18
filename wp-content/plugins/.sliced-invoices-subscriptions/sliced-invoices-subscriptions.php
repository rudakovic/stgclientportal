<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Sliced Invoices Subscriptions
 * Plugin URI:        https://slicedinvoices.com/extensions/subscription-invoices/
 * Description:       Create subscription invoices with the click of a button. Requirements: The Sliced Invoices Plugin
 * Version:           1.4.0
 * Author:            Sliced Invoices
 * Author URI:        https://slicedinvoices.com/
 * Text Domain:       sliced-invoices-subscriptions
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

define( 'SLICED_INVOICES_SUBSCRIPTIONS_VERSION', '1.4.0' );
define( 'SLICED_INVOICES_SUBSCRIPTIONS_FILE', __FILE__ );
define( 'SLICED_INVOICES_SUBSCRIPTIONS_PATH', plugin_dir_path( __FILE__ ) );

require_once( SLICED_INVOICES_SUBSCRIPTIONS_PATH . 'admin/includes/class-sliced-invoices-subscriptions-admin.php' );
require_once( SLICED_INVOICES_SUBSCRIPTIONS_PATH . 'includes/class-sliced-subscriptions.php' );
require_once( SLICED_INVOICES_SUBSCRIPTIONS_PATH . 'includes/sliced-invoices-subscriptions-deactivate.php' );
require_once( SLICED_INVOICES_SUBSCRIPTIONS_PATH . 'updater/plugin-updater.php' );


/**
 * Make it so...
 */
function sliced_invoices_subscriptions_init() {
	Sliced_Subscriptions::get_instance();
	do_action( 'sliced_invoices_subscriptions_loaded' );
}
add_action( 'init', 'sliced_invoices_subscriptions_init' );
