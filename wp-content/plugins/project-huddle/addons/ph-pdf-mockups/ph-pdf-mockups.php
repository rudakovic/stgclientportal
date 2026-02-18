<?php

/**
 * Plugin Name:  SureFeedback — PDF Mockups Addon
 * Plugin URI: https://surefeedback.com
 * Description: PDF Mockups is an addon to the SureFeedback plugin. This addon extends the mockup option and allows you to upload PDFs.
 * Version: 2.1.2
 * Author: Brainstorm Force
 * Author URI: https://www.brainstormforce.com
 * Text Domain: ph-pdf-mockups
 * Domain Path: languages
 *
 * Requires at least: 4.7
 * Tested up to: 6.2
 * 
 * @package SureFeedback
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Setup Constants before init because we're running plugin on plugins_loaded
 *
 * @since 1.1.1
 */

// Plugin Folder Path.
if (!defined('PH_PDF_PLUGIN_DIR')) {
	define('PH_PDF_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Plugin Folder URL.
if (!defined('PH_PDF_PLUGIN_URL')) {
	define('PH_PDF_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Plugin Root File.
if (!defined('PH_PDF_PLUGIN_FILE')) {
	define('PH_PDF_PLUGIN_FILE', __FILE__);
}

// Plugin Folder Path.
if (!defined('PH_PDF_PLUGIN_VERSION')) {
	define('PH_PDF_PLUGIN_VERSION', '2.1.2');
}

// this is the URL our updater / license checker pings. Do not change.
if (!defined('PH_PDF_SL_STORE_URL')) {
	define('PH_PDF_SL_STORE_URL', 'http://projecthuddle.io');
}

// item name (for updates) do no change.
if (!defined('PH_PDF_SL_ITEM_NAME')) {
	define('PH_PDF_SL_ITEM_NAME', 'ProjectHuddle');
}

// item id.
if (!defined('PH_PDF_SL_ITEM_ID')) {
	define('PH_PDF_SL_ITEM_ID', 18650);
}


if (!class_exists('PH_PDF_Mockups')) :

	/**
	 * Main PH_PDF_Mockups Class
	 * Uses singleton design pattern
	 *
	 * @since 1.0.0
	 */
	final class PH_PDF_Mockups
	{

		/**
		 * Holds only one PH_PDF_Mockups instance
		 *
		 * @var $instance
		 * @since 1.0
		 */
		private static $instance;
		
		/**
		 * @var PH_PDF_Scripts
		 */
		public $scripts;

		/**
		 * Main PH_PDF_Mockups Instance
		 *
		 * Insures that only one instance of PH_PDF_Mockups exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since  1.0.0
		 * @static var array $instance
		 * @uses   PH_PDF_Mockups::includes() Include the required files
		 * @uses   PH_PDF_Mockups::load_textdomain() load the language files
		 * @see    PHW()
		 * @return PH_PDF_Mockups|bool $instance The one true PH_PDF_Mockups
		 */
		public static function instance()
		{
			if (!isset(self::$instance) && !(self::$instance instanceof PH_PDF_Mockups)) {
				// start instance.
				self::$instance = new PH_PDF_Mockups();

				// do nothing if SureFeedback is not activated.
				// if (!class_exists('Project_Huddle', false)) {
				// 	add_action('admin_notices', array(self::$instance, 'projecthuddle_required'));
				// 	return false;
				// }

				// make sure we have the correct minimum version.
				// if (version_compare(PH_VERSION, '4.1.0') < 0) {
				// 	add_action('admin_notices', array(self::$instance, 'update_required'));
				// 	return false;
				// }

				// load includes.
				self::$instance->includes();

				// classes.
				self::$instance->scripts = new PH_PDF_Scripts();

				// Show a relevant notice if the installed SureFeedback version doesn't support automatic updates.
				// if (version_compare(PH_VERSION, '3.9.25') < 0) {
				// 	add_action('admin_notices', array(self::$instance, 'add_automatic_updates_not_supported_notice'));
				// }

				// Loaded action.
				do_action('ph_file_uploads_loaded');
			}

			return self::$instance;
		}

		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since 1.0.0
		 *
		 * @uses _doing_it_wrong() Mark something as being incorrectly called.
		 *
		 * @access public
		 * @return void
		 */
		public function __clone()
		{
			// Cloning instances of the class is forbidden.
			_doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'ph-pdf-mockups'), '1.0.0');
		}

		/**
		 * Disable un-serializing of the class
		 *
		 * @since 1.0.0
		 *
		 * @uses _doing_it_wrong() Mark something as being incorrectly called.
		 *
		 * @access public
		 * @return void
		 */
		public function __wakeup()
		{
			// Un-serializing instances of the class is forbidden.
			_doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'ph-pdf-mockups'), '1.0.0');
		}

		/**
		 * Checks if SureFeedback is installed.
		 *
		 * @return bool
		 * @since 2.1.1
		 *
		 * @access public
		 */
		public function _is_ph_installed()
		{
			$path    = 'project-huddle/project-huddle.php';
			$plugins = get_plugins();

			return isset( $plugins[ $path ] );
		}

		/**
		 * Show notice if SureFeedback needs updating
		 *
		 * @since 1.0.0
		 *
		 * @access protected
		 * @return void
		 */
		public function projecthuddle_required() {
			$screen = get_current_screen();
			if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
				return;
			}

			$class = 'notice notice-error';

			$plugin = 'project-huddle/project-huddle.php';

			if ( $this->_is_ph_installed() ) {
				if ( ! current_user_can( 'activate_plugins' ) ) {
					return;
				}

				/* translators: %s: html tags */
				$message = sprintf( __('The %1$SureFeedback - PDF Mockups Addon%2$s plugin requires the %1$SureFeedback Admin Site%2$s plugin to be activated.', 'ph-file-uploads' ), '<b>', '</b>' );

				$action_url   = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
				$button_label = __( 'Activate SureFeedback Admin Site', 'uael' );

				$button = '<p><a href="' . $action_url . '" class="button-primary">' . $button_label . '</a></p><p></p>';

			} else {
				/* translators: %s: html tags */
				$message = sprintf( __('The %1$SureFeedback - PDF Mockups Addon%2$s plugin requires the %1$SureFeedback Admin Site%2$s plugin to be installed & activated.', 'ph-file-uploads' ), '<b>', '</b>' );
			}

			printf( '<div class="%1$s"><p>%2$s</p>%3$s</div>', esc_attr( $class ), wp_kses_post( $message ), isset($button) ? wp_kses_post( $button ) : '' );
        }

		/**
		 * Show notice if SureFeedback needs updating
		 *
		 * @since 1.0.0
		 *
		 * @access protected
		 * @return void
		 */
		public function update_required()
		{
		?>
			<div class="notice notice-warning">
				<p>
					<?php 
					echo sprintf( __('The  %1$SureFeedback PDF Mockups%2$s addon requires the SureFeedback Admin Site plugin to be at least version 4.0. Please update your %1$SureFeedback Admin Site%2$s plugin.', 'ph-pdf-mockups' ), '<b>', '</b>' );
					?>
				</p>
			</div>
		<?php
		}

		/**
		 * Show notice if SureFeedback needs updating to enable automatic updates.
		 *
		 * @since 1.0.0
		 *
		 * @access protected
		 * @return void
		 */
		public function add_automatic_updates_not_supported_notice()
		{
			if (get_option('dismissed-ph-pdf-no-updates-support', false)) {
				return;
			}
			?>

			<div class="notice is-dismissible ph-notice" data-notice="ph-pdf-no-updates-support">
				<p>
					<?php 
					echo sprintf( __('The %1$sSureFeedback PDF Mockups%2$s addon version 1.0.5 requires the SureFeedback Admin Site plugin to be at least version 3.9.25 to enable automatic updates. Please update your %1$sSureFeedback Admin Site%2$s plugin.', 'ph-pdf-mockups' ), '<b>', '</b>' );
					?>
				</p>
			</div>
			<?php
			ph_dismiss_js();
		}

		/**
		 * Include required files
		 *
		 * @access private
		 * @since 1.0.0
		 * @return void
		 */
		private function includes()
		{
			require_once PH_PDF_PLUGIN_DIR . 'includes/class-ph-pdf-scripts.php';

			// settings.
			require_once PH_PDF_PLUGIN_DIR . 'includes/ph-pdf-functions.php';
		}
	}

endif; // end if class_exists.

/**
 * The main function responsible for returning the one true PH_PDF_Mockups
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @since 1.0.0
 * @return object The one true PH_PDF_Mockups Instance
 */
// phpcs:ignore
function PH_PDF()
{
	return PH_PDF_Mockups::instance();
}
add_action('plugins_loaded', 'PH_PDF', 20);
