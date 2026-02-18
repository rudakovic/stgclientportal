<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Sliced Invoices Stripe
 * Plugin URI:        https://slicedinvoices.com/extensions/stripe-payment-gateway/
 * Description:       Accept invoice payments using Stripe payment gateway. Requirements: The Sliced Invoices Plugin
 * Version:           2.1.2
 * Author:            Sliced Invoices
 * Author URI:        https://slicedinvoices.com/
 * Text Domain:       sliced-invoices-stripe
 * Domain Path:       /languages
 *
 * -------------------------------------------------------------------------------
 * Copyright © 2024 Sliced Software, LLC.  All rights reserved.
 * This software may not be resold, redistributed or otherwise conveyed to a third party.
 * -------------------------------------------------------------------------------
 */

if ( ! defined('ABSPATH') ) {
	exit; // Exit if accessed directly
}

define( 'SLICED_INVOICES_STRIPE_VERSION', '2.1.2' );
define( 'SLICED_INVOICES_STRIPE_DBVERSION', '2' );
define( 'SLICED_INVOICES_STRIPE_FILE', __FILE__ );
define( 'SLICED_INVOICES_STRIPE_PATH', plugin_dir_path( __FILE__ ) );
define( 'SLICED_INVOICES_STRIPE_URL', plugin_dir_url( __FILE__ ) );

require_once( SLICED_INVOICES_STRIPE_PATH . 'admin/includes/class-sliced-invoices-stripe-admin.php' );
require_once( SLICED_INVOICES_STRIPE_PATH . 'includes/class-sliced-stripe.php' );
require_once( SLICED_INVOICES_STRIPE_PATH . 'includes/sliced-invoices-stripe-activate.php' );
require_once( SLICED_INVOICES_STRIPE_PATH . 'includes/sliced-invoices-stripe-deactivate.php' );
require_once( SLICED_INVOICES_STRIPE_PATH . 'includes/sliced-invoices-stripe-update.php' );
require_once( SLICED_INVOICES_STRIPE_PATH . 'includes/sliced-invoices-stripe-global-functions.php' );
require_once( SLICED_INVOICES_STRIPE_PATH . 'updater/plugin-updater.php' );


/**
 * Make it so...
 */
function sliced_invoices_stripe_init() {
	Sliced_Stripe::get_instance();
	do_action( 'sliced_invoices_stripe_loaded' );
}

add_action( 'init', 'sliced_invoices_stripe_init' );
