<?php

/**
 * Plugin Name: SureFeedback — File Uploads Addon
 * Plugin URI: https://surefeedback.com/
 * Description: File Uploads is an addon to the SureFeedback plugin that enables clients to upload files alongside their comments to provide examples, samples or anything they like.
 * Author: Brainstorm Force
 * Author URI: https://www.brainstormforce.com
 * Version: 2.2.2
 *
 * Requires at least: 4.7
 * Tested up to: 6.2
 * 
 * Text Domain: ph-file-uploads
 * Domain Path: languages
 *
 * @package ProjectHuddle
 * @author Brainstorm Force, Andre Gagnon
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
if (!defined('PH_UPLOADS_PLUGIN_DIR')) {
	define('PH_UPLOADS_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Plugin Folder URL.
if (!defined('PH_UPLOADS_PLUGIN_URL')) {
	define('PH_UPLOADS_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Plugin Root File.
if (!defined('PH_UPLOADS_PLUGIN_FILE')) {
	define('PH_UPLOADS_PLUGIN_FILE', __FILE__);
}

// Plugin Folder Path.
if (!defined('PH_UPLOADS_PLUGIN_VERSION')) {
	define('PH_UPLOADS_PLUGIN_VERSION', '2.2.2');
}

// this is the URL our updater / license checker pings. Do not change.
if (!defined('PH_UPLOADS_SL_STORE_URL')) {
	define('PH_UPLOADS_SL_STORE_URL', 'https://surefeedback.com/');
}

// item name (for updates) do no change.
if (!defined('PH_UPLOADS_SL_ITEM_NAME')) {
	define('PH_UPLOADS_SL_ITEM_NAME', 'SureFeedback');
}

// item id.
if (!defined('PH_UPLOADS_SL_ITEM_ID')) {
	define('PH_UPLOADS_SL_ITEM_ID', 12101);
}


if (!class_exists('PH_File_Uploads')) :

	/**
	 * Main PH_File_Uploads Class
	 * Uses singleton design pattern
	 *
	 * @since 1.0.0
	 */
	final class PH_File_Uploads
	{

		/**
		 * Holds only one PH_File_Uploads instance
		 *
		 * @var $instance
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * @var PHF_Scripts
		 */
		public $scripts;

		/**
		 * @var PHF_File_Upload_Comment
		 */
		public $upload;

		/**
		 * Main PH_File_Uploads Instance
		 *
		 * Insures that only one instance of PH_File_Uploads exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since  1.0.0
		 * @static var array $instance
		 * @uses   PH_File_Uploads::includes() Include the required files
		 * @uses   PH_File_Uploads::load_textdomain() load the language files
		 * @see    PHW()
		 * @return PH_File_Uploads|bool $instance The one true PH_File_Uploads
		 */
		public static function instance()
		{
			if (!isset(self::$instance) && !(self::$instance instanceof PH_File_Uploads)) {
				// start instance.
				self::$instance = new PH_File_Uploads();

				// do nothing if SureFeedback is not activated.
				// if (!class_exists('Project_Huddle', false)) {
				// 	add_action('admin_notices', array(self::$instance, 'projecthuddle_required'));
				// 	return false;
				// }

				// // make sure we have the correct minimum version.
				// if (version_compare(PH_VERSION, '4.1.0') < 0) {
				// 	add_action('admin_notices', array(self::$instance, 'update_required'));
				// 	return false;
				// }

				// // Show a relevant notice if the installed SureFeedback version doesn't support automatic updates.
				// if (version_compare(PH_VERSION, '3.9.25') < 0) {
				// 	add_action('admin_notices', array(self::$instance, 'add_automatic_updates_not_supported_notice'));
				// }

				// load includes.
				self::$instance->includes();

				// classes.
				self::$instance->scripts = new PHF_Scripts();
				self::$instance->upload  = new PHF_File_Upload_Comment();

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
			_doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'ph-file-uploads'), '1.0.0');
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
			_doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'ph-file-uploads'), '1.0.0');
		}

		/**
		 * Checks if SureFeedback is installed.
		 *
		 * @return bool
		 * @since 2.2.1
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
				$message = sprintf( __('The %1$SureFeedback - File Uploads Addon%2$s plugin requires the %1$SureFeedback Admin Site%2$s plugin to be activated.', 'ph-file-uploads' ), '<b>', '</b>' );

				$action_url   = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
				$button_label = __( 'Activate SureFeedback Admin Site', 'uael' );

				$button = '<p><a href="' . $action_url . '" class="button-primary">' . $button_label . '</a></p><p></p>';

			} else {
				/* translators: %s: html tags */
				$message = sprintf( __('The %1$SureFeedback - File Uploads Addon%2$s plugin requires the %1$SureFeedback Admin Site%2$s plugin to be installed & activated.', 'ph-file-uploads' ), '<b>', '</b>' );
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
					echo sprintf( __('The  %1$SureFeedback File Uploads%2$s addon requires the SureFeedback Admin Site plugin to be at least version 4.0. Please update your %1$SureFeedback Admin Site%2$s plugin.', 'ph-file-uploads' ), '<b>', '</b>' );
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
			if (get_option('dismissed-phf-no-updates-support', false)) {
				return;
			}
			?>
			<div class="notice is-dismissible ph-notice" data-notice="phf-no-updates-support">
				<p>
					<?php 
					echo sprintf( __('The %1$SureFeedback File Uploads%2$s addon version 1.0.10 requires the SureFeedback Admin Site plugin to be at least version 3.9.25 to enable automatic updates. Please update your %1$SureFeedback Admin Site%2$s plugin.', 'ph-file-uploads' ), '<b>', '</b>' );
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
			// class autoloader.
			require_once PH_UPLOADS_PLUGIN_DIR . 'includes/class-phf-autoloader.php';

			// functions.
			require_once PH_UPLOADS_PLUGIN_DIR . 'includes/phf-template-functions.php';
		}
	}

endif; // end if class_exists.

/**
 * The main function responsible for returning the one true PH_File_Uploads
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @since 1.0.0
 * @return object The one true PH_File_Uploads Instance
 */
// phpcs:ignore
function PH_Files()
{
	return PH_File_Uploads::instance();
}
add_action('plugins_loaded', 'PH_Files', 20);
