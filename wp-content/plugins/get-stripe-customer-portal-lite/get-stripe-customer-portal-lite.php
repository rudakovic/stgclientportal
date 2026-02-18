<?php
/**
 * Get Stripe Customer Portal Lite
 *
 * @package           GetStripeCustomerPortalLite
 * @author            Netfox Software Co.
 * @copyright         Copyright 2019-2021 by Netfox Software Co. - All rights reserved.
 *
 * @wordpress-plugin
 * Plugin Name:       Get Stripe Customer Portal Lite
 * Plugin URI:        
 * Description:       Easy way to send your clients links to their Stripe Customer Portal. Lite version.
 * Version:           1.1.4
 * Requires PHP:      5.3
 * Requires at least: 4.7
 * Author:            Netfox Software Co.
 * Author URI:        https://netfoxsoftware.com
 */

// error_reporting(E_ALL);
// ini_set('display_errors', 'On');

if ( ! defined( 'ABSPATH' ) ) {
    wp_die( 'Direct access is not allowed' );
}

// core initiation 
class GetStripeCustomerPortalLite {
    public $locale;
    function __construct( $locale, $includes, $path ){
        $this->locale = $locale;
        
        // include files
        foreach( $includes as $single_path ){
            include( $path.$single_path );              
        }

        // calling localization
        add_action('plugins_loaded', array( $this, 'myplugin_init' ) );

        register_activation_hook( __FILE__, array( $this, 'plugin_activation' ) );
        
        register_uninstall_hook(__FILE__, 'plugin_uninstall');

        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
    }

    function plugin_activation(){
        flush_rewrite_rules();
    }
    
    function plugin_uninstall(){
    }

    function myplugin_init() {
        $plugin_dir = basename(dirname(__FILE__));
        load_plugin_textdomain( $this->locale , false, $plugin_dir );
    }

    function plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=gscp_lite_main_settings') . '" title="' . __('Settings', 'get-stripe-customer-portal-lite') . '">' . __('Settings', 'get-stripe-customer-portal-lite') . '</a>';

        array_unshift($links, $settings_link);

        return $links;
    }
}

function gscp_pro_active_version_notice() {
?>
   <div class="notice notice-error is-dismissible">
      <p><?php echo sprintf(__( 'Get Stripe Customer Portal Pro is active. You should not have both Lite and Pro plugins running simultaneously as it can lead to conflicts. Please deactivate one of them.' )); ?></p>
   </div>
<?php
}

function gscp_check_pro_version() {
   if (class_exists('GetStripeCustomerPortal')) {
      add_action('admin_notices', 'gscp_pro_active_version_notice');
      return;
   }
}

// initiate main class
$obj = new GetStripeCustomerPortalLite('', array(
    'modules/GetStripeCustomerPortalFormElementsLite.php',
    'modules/scripts.php',
    'modules/settings.php',
    'modules/shortcodes.php',
), dirname(__FILE__).'/' );


if (!class_exists('Stripe\Stripe')) {
    include_once('modules/stripe_library/init.php');
}

add_action( 'plugins_loaded', 'gscp_check_pro_version' );
?>
