<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Sliced Invoices Better URL's
 * Plugin URI:        https://slicedinvoices.com/extensions/better-urls/
 * Description:       Change the URL slugs on Invoices and Quotes without touching any code. Requirements: The Sliced Invoices Plugin
 * Version:           1.1.7
 * Author:            Sliced Invoices
 * Author URI:        https://slicedinvoices.com/
 * Text Domain:       sliced-invoices-slugs
 * Domain Path:       /languages
 *
 * -------------------------------------------------------------------------------
 * Copyright © 2022 Sliced Software, LLC.  All rights reserved.
 * This software may not be resold, redistributed or otherwise conveyed to a third party.
 * -------------------------------------------------------------------------------
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
	exit;
}

/**
 * Initialize
 */
define( 'SI_URLS_VERSION', '1.1.7' );
define( 'SI_URLS_FILE', __FILE__ );

include( plugin_dir_path( __FILE__ ) . '/updater/plugin-updater.php' );

register_deactivation_hook( __FILE__, array( 'Sliced_Slugs', 'deactivate' ) );

function sliced_slugs_load_textdomain() {
    load_plugin_textdomain( 'sliced-invoices-slugs', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'sliced_slugs_load_textdomain' );


/**
 * Calls the class.
 */
function sliced_call_slugs_class() {
	new Sliced_Slugs();
}
// priority 0 runs before all other extensions (except Secure Invoices), and 
// (most importantly) before we register our CPTs (which is also done on the
// init hook, at priority 1).
add_action( 'init', 'sliced_call_slugs_class', 0 );


/** 
 * The Class.
 */
class Sliced_Slugs {
	
	/**
	 * @var  object  Instance of this class
	 */
	protected static $instance;
	
	public function __construct() {
		
		if ( ! $this->validate_settings() ) {
			return;
		}
		
		add_action( 'admin_init', array( $this, 'sliced_slugs_flush_rewrites' ) );
		add_filter( 'plugin_action_links_sliced-invoices-slugs/sliced-invoices-slugs.php', array( $this, 'plugin_action_links' ) );
		add_filter( 'sliced_quote_option_fields', array( $this, 'sliced_add_quote_options' ), 1 );
		add_filter( 'sliced_invoice_option_fields', array( $this, 'sliced_add_invoice_options' ), 1 );
		add_filter( 'sliced_quote_params', array( $this, 'sliced_new_quote_slug' ) );
		add_filter( 'sliced_invoice_params', array( $this, 'sliced_new_invoice_slug' ) );
		
	}
	
	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Plugin deactivation actions.
	 *
	 * @since   1.1.7
	 */
	public static function deactivate() {
		
		wp_clear_scheduled_hook( 'sliced_invoices_better_urls_updater' );
		$updater = Sliced_Better_Url_Updater::get_instance();
		$updater->updater_notices_clear();
		
	}
	
	
	/**
	 * Add links to plugin page
	 *
	 * @since   1.0.0
	 */
	public function plugin_action_links( $links ) {
		$links[] = '<a href="'. esc_url( get_admin_url( null, 'admin.php?page=sliced_invoices_settings&tab=invoices' ) ) .'">' . __( 'Settings', 'sliced-invoices' ) . '</a>';
		return $links;
	}
	
	
	/**
	 * Add the options fields.
	 *
	 * @since 1.0.0
	 */
	public function sliced_add_quote_options( $options ) {
		
		$options['fields'][] = array(
			'name'      => __( 'Better URLs', 'sliced-invoices-slugs' ),
			'desc'      => '',
			'id'        => 'slugs_title',
			'type'      => 'title',
		);
		$options['fields'][] = array(
			'name'      => __( 'Quote URL Slug', 'sliced-invoices-slugs' ),
			'desc'      => __( 'You can change this from sliced_quote to quotes or estimates (or any other word you like). Must be all lowercase and only underscores, no dashes.', 'sliced-invoices-slugs' ),
			'default'   => 'sliced_quote',
			'id'        => 'new_slug',
			'type'      => 'text',
		);
		
		return $options;
	}
	
	
	/**
	 * Add the options fields.
	 *
	 * @since 1.0.0
	 */
	public function sliced_add_invoice_options( $options ) {
		
		$options['fields'][] = array(
			'name'      => __( 'Better URLs', 'sliced-invoices-slugs' ),
			'desc'      => '',
			'id'        => 'slugs_title',
			'type'      => 'title',
		);
		$options['fields'][] = array(
			'name'      => __( 'Invoice URL Slug', 'sliced-invoices-slugs' ),
			'desc'      => __( 'You can change this from sliced_invoice to invoice or bill (or any other word you like). Must be all lowercase.', 'sliced-invoices-slugs' ),
			'default'   => 'sliced_invoice',
			'id'        => 'new_slug',
			'type'      => 'text',
		);
		
		return $options;
	}
	
	
	/**
	 * Make the changes.
	 *
	 * @since 1.0.0
	 */
	public function sliced_new_quote_slug( $opts ) {
		$quotes = get_option( 'sliced_quotes' );
		$opts['rewrite']['slug'] = isset( $quotes['new_slug'] ) ? $quotes['new_slug'] : $opts['rewrite']['slug'];
		return $opts;
	}
	public function sliced_new_invoice_slug( $opts ) {
		$invoices = get_option( 'sliced_invoices' );
		$opts['rewrite']['slug'] = isset( $invoices['new_slug'] ) ? $invoices['new_slug'] : $opts['rewrite']['slug'];
		return $opts;
	}
	
	
	/**
	 * Flush rewrites upon slug change.
	 *
	 * @version 1.1.7
	 * @since   1.1.6
	 */
	public function sliced_slugs_flush_rewrites() {
		
		global $pagenow;
		
		if (
			$pagenow === 'admin.php'
			&& isset( $_REQUEST['page'] )
			&& $_REQUEST['page'] === 'sliced_invoices_settings'
			&& isset( $_REQUEST['tab'] )
			&& ( $_REQUEST['tab'] === 'invoices' || $_REQUEST['tab'] === 'quotes' )
		) {
			
			// handle normal page load
			if ( delete_transient( 'sliced_invoices_better_urls_flush_needed' ) ) {
				flush_rewrite_rules();
				add_action( 'admin_notices', array( $this, 'settings_saved_notice' ) );
			}
			
			// handle fresh save
			if ( isset( $_POST['new_slug'] ) && ! empty( $_POST['new_slug'] ) ) {
				if ( $_REQUEST['tab'] === 'invoices' ) {
					$invoices = get_option( 'sliced_invoices' );
					if ( $_POST['new_slug'] !== $invoices['new_slug'] ) {
						set_transient( 'sliced_invoices_better_urls_flush_needed', true );
						wp_safe_redirect( admin_url( 'admin.php?page=sliced_invoices_settings&tab=invoices' ) );
					}
				} elseif ( $_REQUEST['tab'] === 'quotes' ) {
					$quotes = get_option( 'sliced_quotes' );
					if ( $_POST['new_slug'] !== $quotes['new_slug'] ) {
						set_transient( 'sliced_invoices_better_urls_flush_needed', true );
						wp_safe_redirect( admin_url( 'admin.php?page=sliced_invoices_settings&tab=quotes' ) );
					}
				}
			}
			
		}
		
	}
	
	
	/**
	 * Output requirements not met notice.
	 *
	 * @since   1.1.5
	 */
	public function requirements_not_met_notice() {
		echo '<div id="message" class="error">';
		echo '<p>' . sprintf( __( 'Sliced Invoices Better URL\'s extension cannot find the required <a href="%s">Sliced Invoices plugin</a>. Please make sure the core Sliced Invoices plugin is <a href="%s">installed and activated</a>.', 'sliced-invoices-slugs' ), 'https://wordpress.org/plugins/sliced-invoices/', admin_url( 'plugins.php' ) ) . '</p>';
		echo '</div>';
	}
	
	
	/**
	 * Output settings saved notice.
	 * Because of our redirect in sliced_slugs_flush_rewrites(), we miss the settings saved notice from core, so we have to do it again.
	 *
	 * @since   1.1.7
	 */
	public function settings_saved_notice() {
		echo '<div class="updated">
			<p>' . __( 'Settings saved successfully.', 'sliced-invoices-slugs' ) . '</p>
		</div>';
	}
	
	
	/**
	 * Validate settings, make sure all requirements met, etc.
	 *
	 * @version 1.1.6
	 * @since   1.1.5
	 */
	public function validate_settings() {
		
		if ( ! class_exists( 'Sliced_Invoices' ) ) {
			
			// Add a dashboard notice.
			add_action( 'admin_notices', array( $this, 'requirements_not_met_notice' ) );
			
			return false;
		}
		
		return true;
	}
	
}
