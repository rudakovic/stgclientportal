<?php
/**
 * Plugin Name:       Stripe Subscription to WooCommerce Subscription
 * Description:	      Imports Stripe Subscription Data via CSV and converts to WooCommerce Subscriptions
 * Requires at least: 6.4.3
 * Requires PHP:      8.1
 * Version:           0.1.0
 * Author:            Krissie VandeNoord
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       stripe-sub-to-woo-sub
 *
 * @package stripe-sub-to-woo-sub
 */

namespace NUX\StripeSubToWooSub;

defined( 'ABSPATH' ) || exit;

function get_plugin_dir() {
  return plugin_dir_path( __FILE__ );
}

function get_plugin_url() {
  return plugin_dir_url( __FILE__ );
}

require get_plugin_dir() . 'vendor/autoload.php';

require get_plugin_dir() . 'includes/class-admin-menu.php';
require get_plugin_dir() . 'includes/class-csv-importer.php';
require get_plugin_dir() . 'includes/class-woocommerce-helpers.php';
require get_plugin_dir() . 'includes/class-nux-wp-cli.php';

