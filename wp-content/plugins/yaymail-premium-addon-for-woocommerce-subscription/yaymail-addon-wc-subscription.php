<?php
/**
 * Plugin Name: YayMail Addon for WooCommerce Subscriptions
 * Plugin URI: https://yaycommerce.com/yaymail-addons/yaymail-premium-addon-for-woocommerce-subscriptions/
 * Description: Customize WooCommerce Subscriptions email templates with YayMail - WooCommerce Email Customizer
 * Version: 4.0.4
 * Author: YayCommerce
 * Author URI: https://yaycommerce.com
 * Text Domain: yaymail
 * WC requires at least: 3.0.0
 * WC tested up to: 10.0
 * Domain Path: /i18n/languages/
 *
 * @package YayMailAddonWcSubscription
 */

namespace YayMailAddonWcSubscription;

defined( 'ABSPATH' ) || exit;

/**
 * WS: Woocommerce Subscription
 */

if ( ! defined( 'YAYMAIL_ADDON_WS_PLUGIN_FILE' ) ) {
    define( 'YAYMAIL_ADDON_WS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'YAYMAIL_ADDON_WS_PLUGIN_PATH' ) ) {
    define( 'YAYMAIL_ADDON_WS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'YAYMAIL_ADDON_WS_PLUGIN_URL' ) ) {
    define( 'YAYMAIL_ADDON_WS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'YAYMAIL_ADDON_WS_VERSION' ) ) {
    define( 'YAYMAIL_ADDON_WS_VERSION', '4.0.4' );
}

if ( ! defined( 'YAYMAIL_ADDON_WS_BASE_NAME' ) ) {
    define( 'YAYMAIL_ADDON_WS_BASE_NAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YAYMAIL_ADDON_WS_NAMES' ) ) {
    define( 'YAYMAIL_ADDON_WS_NAMES', 'WooCommerce Subscriptions' );
}

require_once YAYMAIL_ADDON_WS_PLUGIN_PATH . 'vendor/autoload.php';

add_action(
    'plugins_loaded',
    function () {
        // Initialize license
        License::get_instance();

        // May show notice to install YayMail/ Third-party plugin
        Notices\NoticeMain::get_instance();
    }
);

if ( ! function_exists( 'YayMailAddonWcSubscription\\init' ) ) {
    function init() {
        if ( defined( 'YAYMAIL_VERSION' ) ) {
                $yaymail_version = YAYMAIL_VERSION;
        } else {
            $yaymail_version = '4.0.0';
        }

        // Check if 3rd-party and core are active
        if ( ! class_exists( 'WC_Subscriptions' ) || ! function_exists( 'YayMail\\init' ) || version_compare( $yaymail_version, '4.0', '<' ) ) {
            return;
        }

        add_action(
            'admin_enqueue_scripts',
            function ( $hook_suffix ) {
                if ( in_array( $hook_suffix, [ 'yaycommerce_page_yaymail-settings' ], true ) && class_exists( 'WC_Emails' ) ) {
                    \YayMailAddonWcSubscription\AddonVite::get_instance()->enqueue_entry( 'yaymail-addon.tsx', [ 'react', 'react-dom' ] );
                }
            },
            20
        );

        Controllers\AddonController::get_instance();
    }
}//end if

add_action( 'yaymail_init_start', 'YayMailAddonWcSubscription\\init' );

register_activation_hook( __FILE__, [ \YayMailAddonWcSubscription\Engine\ActDeact::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ \YayMailAddonWcSubscription\Engine\ActDeact::class, 'deactivate' ] );

if ( ! function_exists( 'YayMailAddonWcSubscription\\on_update' ) ) {
    function on_update( $upgrader_object, $options ) {
        // The path to our plugin's main file
        $our_plugin = plugin_basename( __FILE__ );
        // If an update has taken place and the updated type is plugins and the plugins element exists
        if ( $options['action'] === 'update' && $options['type'] === 'plugin' && isset( $options['plugins'] ) ) {
            if ( in_array( $our_plugin, $options['plugins'], true ) ) {
                \YayMailAddonWcSubscription\Migrations\WcSubscriptionMigration::get_instance();

                \YayMail\Migrations\MainMigration::get_instance()->migrate();
            }
        }
    }
}
add_action( 'upgrader_process_complete', 'YayMailAddonWcSubscription\\on_update', 10, 2 );
