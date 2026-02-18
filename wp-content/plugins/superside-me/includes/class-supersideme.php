<?php
/**
 * Main SuperSide Me class.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2020 Robin Cornett
 * @license   GPL-2.0+
 */

class SuperSide_Me {

	/**
	 * Class for building the SuperSide Me[nu] panel.
	 * @var SuperSide_Me_Builder $builder
	 */
	protected $builder;

	/**
	 * Class for setting up cron/schedule
	 * @var SuperSide_Me_Cron $cron
	 */
	protected $cron;
	/**
	 * Class for inline CSS stylesheets.
	 * @var SuperSide_Me_CSS $css
	 */
	protected $css;

	/**
	 * Class for implementing the WP Customizer.
	 * @var SuperSide_Me_Customizer $customizer
	 */
	protected $customizer;

	/**
	 * Class for enqueueing styles
	 * @var $enqueue SuperSideMeEnqueue
	 */
	protected $enqueue;

	/**
	 * Help tabs for settings page.
	 * @var $help SuperSide_Me_HelpTabs
	 */
	protected $help;

	/**
	 * Class to handle EDD Software Licensing updates/checks
	 * @var SuperSide_Me_Licensing $licensing
	 */
	protected $licensing;

	/**
	 * Class to register all plugin settings
	 * @var SuperSide_Me_Settings $settings
	 */
	protected $settings;

	/**
	 * SuperSide_Me constructor.
	 *
	 * @param $builder
	 * @param $cron
	 * @param $customizer
	 * @param $enqueue
	 * @param $help
	 * @param $licensing
	 * @param $settings
	 *
	 * @internal param $css
	 */
	public function __construct( $builder, $cron, $customizer, $enqueue, $help, $licensing, $settings ) {
		$this->builder    = $builder;
		$this->cron       = $cron;
		$this->customizer = $customizer;
		$this->enqueue    = $enqueue;
		$this->help       = $help;
		$this->licensing  = $licensing;
		$this->settings   = $settings;
	}

	/**
	 * Fires up the plugin.
	 */
	public function run() {

		// start up licensing work
		add_action( 'admin_init', array( $this->licensing, 'updater' ) );
		add_action( 'supersideme_weekly_events', array( $this->licensing, 'weekly_license_check' ) );

		// admin
		add_action( 'after_setup_theme', array( $this, 'settings_page' ) );
		add_action( 'customize_register', array( $this->customizer, 'customizer' ) );

		// register nav/widget/textdomain
		add_action( 'widgets_init', array( $this->builder, 'register_widget_area' ) );
		add_action( 'after_setup_theme', array( $this->builder, 'register_superside_nav' ), 5 );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		// js/no-js
		add_filter( 'body_class', array( $this->enqueue, 'no_js_body_class' ) );
		add_action( 'wp_body_open', array( $this->enqueue, 'add_js_class' ), 100 );
		add_action( 'wp_footer', array( $this->enqueue, 'back_compat_add_js_class' ) );

		// REST
		add_action( 'rest_api_init', array( $this->builder, 'rest' ) );

		// filters for helper functions
		add_filter( 'supersideme_get_plugin_setting', array( $this->settings, 'get_setting' ) );
		add_filter( 'supersideme_panel_has_content', array( $this->enqueue, 'panel_has_content' ) );
		add_filter( 'supersideme_get_navigation_options', array( $this->enqueue, 'options' ) );

		// everything else is front-end only, so quit now if we're in the admin
		if ( is_admin() ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this->enqueue, 'maybe_enqueue' ) );
		add_action( 'wp_footer', array( $this->builder, 'do_sidebar' ) );
	}

	/**
	 * admin message if panel can't be generated
	 *
	 * @since 1.4.0
	 */
	public function do_error_message() {
		if ( supersideme_has_content() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$message = sprintf(
			__( 'SuperSide Me works like magic, but you\'ve got to give it something with which to work. Please add a menu to a <a href="%s">registered menu location</a> or add a widget to the new <a href="%s">SuperSide Me widget area</a>. If those both look all right, double check your <a href="%s">Automagic Menu settings</a>.', 'superside-me' ),
			esc_url( admin_url( 'nav-menus.php?action=locations' ) ),
			esc_url( admin_url( 'widgets.php' ) ),
			esc_url( admin_url( 'themes.php?page=supersideme&tab=menus' ) )
		);
		printf( '<div class="error notice"><p>%s</p></div>', wp_kses_post( $message ) );
	}

	/**
	 * Set up text domain for translations
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'superside-me', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' );
	}

	/**
	 * Add link to plugin settings page in plugin table
	 *
	 * @param $links array
	 * @return array
	 *
	 * @since 1.5.0
	 */
	public function add_settings_link( $links ) {
		$links[] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'themes.php?page=supersideme' ) ), esc_attr__( 'Settings', 'superside-me' ) );

		return $links;
	}

	/**
	 * Add the settings page.
	 * @since x.x.x
	 */
	public function settings_page() {
		if ( ! supersideme_do_settings_page() ) {
			return;
		}
		add_filter( 'plugin_action_links_' . SUPERSIDEME_BASENAME, array( $this, 'add_settings_link' ) );
		add_action( 'admin_menu', array( $this->settings, 'do_submenu_page' ) );
		add_action( 'admin_notices', array( $this, 'do_error_message' ) );
		add_action( 'load-appearance_page_supersideme', array( $this->help, 'help' ) );
	}
}
