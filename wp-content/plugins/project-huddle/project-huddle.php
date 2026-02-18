<?php

/**
 * Plugin Name: SureFeedback Admin Site
 * Plugin URI: https://surefeedback.com/
 * Description: A collaboration tool to collect timely feedback and approvals from clients and teammates for all your design projects.
 * Author: Brainstorm Force
 * Author URI: https://www.brainstormforce.com
 * Version: 4.9.0
 * Update URI: https://api.freemius.com
 *
 * Requires at least: 4.7
 * Tested up to: 6.8
 *
 * Text Domain: project-huddle
 * Domain Path: languages
 *
 * @package ProjectHuddle
 * @category Core
 * @author Brainstorm Force
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('Project_Huddle')) :

	/**
	 * Main Project_Huddle Class
	 * Uses singleton design pattern
	 *
	 * @since 1.0.0
	 */
	final class Project_Huddle
	{
		/**
		 * Holds only one Project_Huddle instance
		 *
		 * @var $instance
		 * @since 1.0
		 */
		private static $instance;

		/**
    	 * @var \PH\Controllers\Mail\EmailController
    	 */
    	public $activity_emails;

		/**
		 * @var array
		 */
		public $config;

		/**
		* @var mixed
		*/
		public $approvals;

		/**
		 * @var mixed
		 */
		public $auth;

		/**
		 * @var PH_Session
		 */
		public $session;

		/**
		 * @var PH_Project_Admin
		 */
		public $project_admin;

		/**
		 * @var PH_Roles
		 */
		public $roles;

		/**
		 * @var PH_Admin_Meta_Boxes
		 */
		public $meta;

		/**
		 * @var PH_Mockup_Project
		 */
		public $mockup;

		/**
		 * @var PH_Website_Project
		 */
		public $website;

		/**
		 * @var PH_Mockup_Image
		 */
		public $image;

		/**
		 * @var PH_Website_Page
		 */
		public $page;

		/**
		 * @var PH_Mockup_Thread
		 */
		public $mockup_thread;

		/**
		 * @var PH_Website_Thread
		 */
		public $website_thread;

		/**
		 * @var PH_Comment
		 */
		public $comment;

		/**
		 * @var PH_User
		 */
		public $user;

		/**
		 * @var PH_User_Email_Options
		 */
		public $emails;

		/**
		 * @var PH_System_Status
		 */
		public $status;

		/**
		 * @var mixed
		 */
		public $license;

		/**
		 * Main Project_Huddle Instance
		 *
		 * Insures that only one instance of Project_Huddle exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since  1.0.0
		 * @static var array $instance
		 * @uses   Project_Huddle::setup_constants() Setup the constants needed
		 * @uses   Project_Huddle::includes() Include the required files
		 * @uses   Project_Huddle::load_textdomain() load the language files
		 * @see    PH()
		 * @return Project_Huddle $instance The one true Project_Huddle
		 */
		public static function instance()
		{
			if (!isset(self::$instance) && !(self::$instance instanceof Project_Huddle)) {
				self::$instance = new Project_Huddle();

				// set up constants immediately.
				self::$instance->setup_constants();

				// load textdomain on plugins_loaded hook.
				add_action('init', array(self::$instance, 'load_textdomain'));

				// check versions.
				self::$instance->check_versions();
				self::$instance->check_secure();

				// get all includes.
				self::$instance->includes();
				self::$instance->activity_emails = new \PH\Controllers\Mail\EmailController();


				add_action('init', array(self::$instance, 'initialize_classes'), 20 );

			}

			return self::$instance;
		}

		/**
		 * Initialize the classes
		 */
		public function initialize_classes() {

			self::$instance->config = include(self::$instance->path('includes/App/Config/app.php'));

			if (self::$instance->config['providers']) {
				foreach (self::$instance->config['providers'] as $name => $class) {
					if (is_string($name)) {
						self::$instance->$name = new $class();
					} else {
						new $class();
					}
				}
			}
			
			self::$instance->session         = new PH_Session();
			self::$instance->project_admin   = new PH_Project_Admin();
			self::$instance->roles           = new PH_Roles();
			self::$instance->meta            = new PH_Admin_Meta_Boxes();

			self::$instance->mockup          = new PH_Mockup_Project();
			self::$instance->website         = new PH_Website_Project();
			self::$instance->image           = new PH_Mockup_Image();
			self::$instance->page            = new PH_Website_Page();
			self::$instance->mockup_thread   = new PH_Mockup_Thread();
			self::$instance->website_thread  = new PH_Website_Thread();
			self::$instance->comment         = new PH_Comment();
			self::$instance->user            = new PH_User();

			self::$instance->emails          = new PH_User_Email_Options();
			self::$instance->status          = new PH_System_Status();
			self::$instance->license         = ph_licensing();

			// Loaded action.
			do_action('projecthuddle_loaded');

			add_action( 'admin_init', array( self::$instance, 'register_notices' ) );
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
			_doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'project-huddle'), '1.0.0');
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
			_doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'project-huddle'), '1.0.0');
		}

		/**
		 * Helper function to get path relative to plugin
		 *
		 * @return void
		 */
		public function path($directory = '')
		{
			return plugin_dir_path(__FILE__) . $directory;
		}

		/**
		 * Helper function to get url relative to plugin
		 *
		 * @param string $path
		 * @return void
		 */
		public function url($path = '')
		{
			return plugin_dir_url(__FILE__) . $path;
		}

		/**
		 * Double check WordPress and php versions
		 */
		private function check_versions()
		{
			global $wp_version;
			if (version_compare($wp_version, '5.0', '<')) {
				add_action('admin_notices', array($this, 'update_wordpress'));
			}
			if (version_compare(phpversion(), '5.6.20', '<')) {
				wp_die(esc_html__('Sorry, versions of PHP 5.6.20 or less are not supported by SureFeedback. Please upgrade PHP to activate.', 'project-huddle'), 403);
			}
			if (version_compare(phpversion(), '7.0', '<')) {
				add_action('admin_notices', array($this, 'update_php'));
			}
		}

		private function check_secure()
		{
			if (!is_ssl()) {
				add_action('admin_notices', array($this, 'secure_notice'));
			}
		}

		/**
		 * Update WordPress notice
		 */
		public function update_wordpress()
		{
			if (!get_option('dismissed-ph-wp-version', false)) {
				echo '<div class="notice notice-error is-dismissible ph-notice" data-notice="ph-wp-version">
					<p>' . esc_html__('You need WordPress version 4.7 or higher to use SureFeedback.', 'project-huddle') . '</p>
				</div>';
				ph_dismiss_js();
			}
		}

		public function secure_notice()
		{
			if (!get_option('dismissed-ph-secure-notice', false)) {
				echo '<div class="notice notice-error is-dismissible ph-notice" data-notice="ph-secure-notice">
					<p><strong>SureFeedback</strong><br>' .
					wp_kses_post(sprintf(__('Your site does not appear to be using a secure connection. A HTTPS SSL connection is required for SureFeedback to work with external website connections. <div><a href="%s" class="button button-primary">Learn more</a></div>', 'project-huddle'), 'https://surefeedback.com/docs/ssl-and-https/'))
					. '</p>
				</div>';
				ph_dismiss_js();
			}
		}

		/**
		 * Update PHP notice
		 */
		public function update_php()
		{
			if (!get_option('dismissed-ph-php-version', false)) {
				echo '<div class="notice notice-error is-dismissible ph-notice" data-notice="ph-php-version">
					<p>' . esc_html(sprintf(__('SureFeedback detected you are running an older version of PHP that WordPress will soon not support. Please update your current version of php (%s) to 7.0+ to make sure your can update WordPress in the future!', 'project-huddle'), phpversion())) . '</p>
					<p><a class="button button-primary" href="https://wordpress.org/support/update-php/" target="_blank">Read More</a><p>
				</div>';
				ph_dismiss_js();
			}
		}

		/**
		 * Setup plugin constants
		 *
		 * @access private
		 * @since 1.0.0
		 * @return void
		 */
		private function setup_constants()
		{
			// Plugin version.
			if (!defined('PH_VERSION')) {
				define('PH_VERSION', '4.8.2');
			}

			// Plugin Folder Path.
			if (!defined('PH_PLUGIN_DIR')) {
				define('PH_PLUGIN_DIR', plugin_dir_path(__FILE__));
			}

			// Plugin Folder URL.
			if (!defined('PH_PLUGIN_URL')) {
				define('PH_PLUGIN_URL', plugin_dir_url(__FILE__));
			}

			// Plugin Root File.
			if (!defined('PH_PLUGIN_FILE')) {
				define('PH_PLUGIN_FILE', __FILE__);
			}

			// set template path.
			if (!defined('PH_TEMPLATE_PATH')) {
				define('PH_TEMPLATE_PATH', apply_filters('ph_template_path', 'project-huddle/'));
			}

			// this is the URL our updater / license checker pings. Do not change.
			if (!defined('PH_SL_STORE_URL')) {
				define('PH_SL_STORE_URL', 'https://surefeedback.com/');
			}

			// item name (for updates) do no change.
			if (!defined('PH_SL_ITEM_NAME')) {
				define('PH_SL_ITEM_NAME', 'SureFeedback');
			}

			// item id.
			if (!defined('PH_SL_ITEM_ID')) {
				define('PH_SL_ITEM_ID', 54);
			}

			// Debug plugin.
			if (!defined('SCRIPT_DEBUG')) {
				define('SCRIPT_DEBUG', false);
			}

			if (!defined('PROJECT_HUDDLE_DEBUG')) {
				define('PROJECT_HUDDLE_DEBUG', false);
			}
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
			global $ph_options;

			require_once __DIR__ . '/vendor/autoload.php';

			require_once PH_PLUGIN_DIR . 'includes/ph-i8ln.php';

			require_once PH_PLUGIN_DIR . 'includes/ph-logging-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/settings/settings-fields.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/post-type-ui.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-style-options.php';

			$ph_options = new PH_Settings();

			// classes.	
			require_once PH_PLUGIN_DIR . 'includes/class-ph-roles.php';
			require_once PH_PLUGIN_DIR . 'includes/class-ph-permissions-controller.php';

			// translations.
			require_once PH_PLUGIN_DIR . 'includes/ph-mockup-translations.php';

			// sessions.
			require_once PH_PLUGIN_DIR . 'includes/class-ph-session.php';

			// misc.
			require_once PH_PLUGIN_DIR . 'includes/scripts.php';
			require_once PH_PLUGIN_DIR . 'includes/post-types.php';
			require_once PH_PLUGIN_DIR . 'includes/templates.php';

			// functions.
			require_once PH_PLUGIN_DIR . 'includes/ph-template-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-form-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-cache-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-project-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-image-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-comment-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-version-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-approval-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-misc-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/compatibility.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-permission-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/transient-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-notice-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-url-rewrites.php';
			require_once PH_PLUGIN_DIR . 'includes/membership/ph-member-functions.php';

			// email.
			require_once PH_PLUGIN_DIR . 'includes/email/class-ph-mail-v2.php';
			require_once PH_PLUGIN_DIR . 'includes/email/email-utility-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/email/email-send-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/email/background-emails.php';
			require_once PH_PLUGIN_DIR . 'includes/email/class-ph-user-email-options.php';

			// shortcodes.
			require_once PH_PLUGIN_DIR . 'includes/shortcodes.php';

			// endpoints.
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-users-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-posts-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-batch-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-multiple-posttype-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-manual-notifications-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-request-chnanges-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-comments-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-versions-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-attachments-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-multiple-posttype-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/ph-ajax-actions.php';

			// auth endpoints
			foreach (glob(PH_PLUGIN_DIR . 'includes/libraries/php-jwt/*.php') as $filename) {
				require_once $filename;
			}
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-token.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-widget-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-key-pair.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/jwt/class-ph-key-pair-list-table.php';

			// admin includes.
			require_once PH_PLUGIN_DIR . 'includes/admin/class-ph-admin-menu.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/class-ph-project-admin.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/meta-boxes/class-ph-admin-meta-boxes.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/meta-boxes/class-ph-project-meta-box-options.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/meta-boxes/class-ph-project-meta-box-activity.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/meta-boxes/class-ph-meta-box-images.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/meta-boxes/class-ph-project-meta-box-email-notifications.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/meta-boxes/class-ph-project-meta-box-members.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/ph-uploads-function.php';

			// models.
			require_once PH_PLUGIN_DIR . 'includes/models/services/class-ph-rest-request.php';
			require_once PH_PLUGIN_DIR . 'includes/models/abstract-class-ph-rest-object.php';
			require_once PH_PLUGIN_DIR . 'includes/models/abstract-class-ph-project.php';
			require_once PH_PLUGIN_DIR . 'includes/models/abstract-class-ph-item.php';
			require_once PH_PLUGIN_DIR . 'includes/models/abstract-class-ph-thread.php';
			require_once PH_PLUGIN_DIR . 'includes/models/class-ph-user.php';
			require_once PH_PLUGIN_DIR . 'includes/models/class-ph-comment.php';
			require_once PH_PLUGIN_DIR . 'includes/models/class-ph-mockup-thread.php';
			require_once PH_PLUGIN_DIR . 'includes/models/class-ph-mockup-image.php';
			require_once PH_PLUGIN_DIR . 'includes/models/class-ph-mockup-project.php';
			require_once PH_PLUGIN_DIR . 'includes/models/class-ph-website-project.php';
			require_once PH_PLUGIN_DIR . 'includes/models/class-ph-website-page.php';
			require_once PH_PLUGIN_DIR . 'includes/models/class-ph-website-thread.php';

			// addons.
			if (is_file(PH_PLUGIN_DIR . 'addons/ph-website-comments/ph-website-comments.php')) {
				include_once PH_PLUGIN_DIR . 'addons/ph-website-comments/ph-website-comments.php';
			}

			// Show notice if SureFeedback File Uploads Addon is activated.
			if ( $this->_is_file_uploads_addon_installed() || is_plugin_active( 'ph-file-uploads/ph-file-uploads.php' ) || is_plugin_active_for_network( 'ph-file-uploads/ph-file-uploads.php' ) ) {
				add_action( 'admin_notices', array( self::$instance, 'file_addon_already_active' ));
			} else if ( is_file(PH_PLUGIN_DIR . 'addons/ph-file-uploads/ph-file-uploads.php') && ! function_exists( 'PH_Files' ) ) {
				include_once PH_PLUGIN_DIR . 'addons/ph-file-uploads/ph-file-uploads.php';
			}

			// Show notice if SureFeedback PDF Mockups Addon is activated.
			if ( $this->_is_pdf_mockups_addon_installed() || is_plugin_active( 'ph-pdf-mockups/ph-pdf-mockups.php' ) || is_plugin_active_for_network( 'ph-pdf-mockups/ph-pdf-mockups.php' ) ) {
				add_action( 'admin_notices', array( self::$instance, 'pdf_addon_already_active' ));
			} else if ( is_file(PH_PLUGIN_DIR . 'addons/ph-pdf-mockups/ph-pdf-mockups.php') ) {
				include_once PH_PLUGIN_DIR . 'addons/ph-pdf-mockups/ph-pdf-mockups.php';
			}

			// system status.
			require_once PH_PLUGIN_DIR . 'includes/tools/status/system-status.php';

			// Include SureTriggers.
			require_once PH_PLUGIN_DIR . 'includes/tools/suretriggers/suretriggers.php';

			require_once PH_PLUGIN_DIR . 'includes/admin/upgrade-processing/upgrade-functions.php';

			// run installation.
			require_once PH_PLUGIN_DIR . 'includes/install.php';

			add_action( 'init', array( self::$instance, 'load_files' ), 6 );

			// Astra notice class.
			require_once PH_PLUGIN_DIR . 'includes/libraries/astra-notices/class-astra-notices.php';

			// Load the NPS Survey library.
			if ( ! class_exists( 'Surefeedback_Nps_Survey' ) ) {
				require_once PH_PLUGIN_DIR . 'includes/libraries/class-surefeedback-nps-survey.php';
			}

		}

		/**
		 * Load files for SureFeedback.
		 *
		 * @since x.x.x
		 *
		 * @access public
		 * @return void
		 */
		public function load_files() {

			// license handler.
			require_once PH_PLUGIN_DIR . 'includes/ph-license-handler.php';

			require_once PH_PLUGIN_DIR . 'includes/models/class-ph-website-user.php';

			// Include Upgrade Base Class
			require_once PH_PLUGIN_DIR . 'includes/admin/upgrade-processing/class-ph-upgrade.php';

			// Include Upgrades
			require_once PH_PLUGIN_DIR . 'includes/admin/upgrade-processing/upgrade-functions.php';

			// Include Upgrade Handler
			require_once PH_PLUGIN_DIR . 'includes/admin/upgrade-processing/class-ph-upgrade-handler-page.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/upgrade-processing/class-ph-upgrade-handler.php';
			
		}

		/**
		 * Checks if SureFeedback File Uploads addon is installed.
		 *
		 * @return bool
		 * @since 4.6.0
		 *
		 * @access public
		 */
		public function _is_file_uploads_addon_installed()
		{
			$path    = 'ph-file-uploads/ph-file-uploads.php';
			$plugins = get_plugins();

			return isset( $plugins[ $path ] );
		}

		/**
		 * Checks if SureFeedback PDF Mockups addon is installed.
		 *
		 * @return bool
		 * @since 4.6.0
		 *
		 * @access public
		 */
		public function _is_pdf_mockups_addon_installed()
		{
			$path    = 'ph-pdf-mockups/ph-pdf-mockups.php';
			$plugins = get_plugins();

			return isset( $plugins[ $path ] );
		}

		/**
		 * Show notice if SureFeedback File Uploads Addon is already active.
		 *
		 * @since 4.6.0
		 *
		 * @access protected
		 * @return void
		 */
		public function file_addon_already_active() {
			$screen = get_current_screen();
			if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
				return;
			}

			$class = 'notice notice-error';

			$plugin = 'ph-file-uploads/ph-file-uploads.php';

			if( is_plugin_active( 'ph-file-uploads/ph-file-uploads.php' ) || is_plugin_active_for_network( 'ph-file-uploads/ph-file-uploads.php' ) ) {
				if ( ! current_user_can( 'deactivate_plugins' ) ) {
					return;
				}
	
				/* translators: %s: html tags */
				$message = sprintf( __('%3$sGreat news!%4$s The %1$sFile Uploads Addon%2$s is now integrated into the %1$sSureFeedback Admin plugin%2$s. No need for separate activation. Please deactivate & delete the %1$sFile Uploads plugin%2$s for smoother functionality.', 'project-huddle' ), '<b>', '</b>', '<i>', '</i>' );

				if ( is_multisite() && is_plugin_active_for_network( $plugin ) && current_user_can( 'manage_network_plugins' ) ) {
					$action_url   = wp_nonce_url( network_admin_url( 'plugins.php?action=deactivate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s' ), 'deactivate-plugin_' . $plugin );

					$button_label = __( 'Network Deactivate - File Uploads Plugin', 'project-huddle' );
				} else {
					$action_url   = wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'deactivate-plugin_' . $plugin );
					$button_label = __( 'Deactivate - File Uploads Plugin', 'project-huddle' );
				}
				
			} else {
				if ( ! current_user_can( 'delete_plugins' ) ) {
					return;
				}
	
				/* translators: %s: html tags */
				$message = sprintf( __('%3$sGreat news!%4$s The %1$sFile Uploads Addon%2$s is now integrated into the %1$sSureFeedback Admin plugin%2$s. Please delete the %1$sFile Uploads plugin%2$s for smoother functionality.', 'project-huddle' ), '<b>', '</b>', '<i>', '</i>' );
	
				if ( is_multisite() ) {
					deactivate_plugins( $plugin );
    				delete_plugins( array( $plugin ) );
				} else {
				
					$action_url = add_query_arg(
						array(
							'action' => 'delete-selected',
							'checked' => urlencode( $plugin ),
							'plugin_status' => 'all',
							'paged' => 1,
							'_wpnonce' => wp_create_nonce( 'bulk-plugins' ), // Adding the nonce here.
						),
						admin_url( 'plugins.php' )
					);
				}
		
				$button_label = __( 'Delete - File Uploads Plugin', 'project-huddle' );
			}

			$button = '<p><a href="' . $action_url . '" class="button-primary">' . $button_label . '</a></p><p></p>';

			printf( '<div class="%1$s"><p>%2$s</p>%3$s</div>', esc_attr( $class ), wp_kses_post( $message ), isset($button) ? wp_kses_post( $button ) : '' );

		}

		/**
		 * Show notice if SureFeedback PDF Mockups Addon is already active.
		 *
		 * @since 4.6.0
		 *
		 * @access protected
		 * @return void
		 */
		public function pdf_addon_already_active() {
			$screen = get_current_screen();
			if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
				return;
			}

			$class = 'notice notice-error';

			$plugin = 'ph-pdf-mockups/ph-pdf-mockups.php';

			if( is_plugin_active( 'ph-pdf-mockups/ph-pdf-mockups.php' ) || is_plugin_active_for_network( 'ph-pdf-mockups/ph-pdf-mockups.php' ) ) {
				if ( ! current_user_can( 'deactivate_plugins' ) ) {
					return;
				}
	
				/* translators: %s: html tags */
				$message = sprintf( __('%3$sGreat news!%4$s The %1$sPDF Mockups Addon%2$s is now integrated into the %1$sSureFeedback Admin plugin%2$s. No need for separate activation. Please deactivate & delete the %1$sPDF Mockups plugin%2$s for smoother functionality.', 'project-huddle' ), '<b>', '</b>', '<i>', '</i>' );

				if ( is_multisite() && is_plugin_active_for_network( $plugin ) && current_user_can( 'manage_network_plugins' ) ) {
					$action_url   = wp_nonce_url( network_admin_url( 'plugins.php?action=deactivate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s' ), 'deactivate-plugin_' . $plugin );
					$button_label = __( 'Network Deactivate - PDF Mockups Plugin', 'project-huddle' );
				} else {
					$action_url   = wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'deactivate-plugin_' . $plugin );
					$button_label = __( 'Deactivate - PDF Mockups Plugin', 'project-huddle' );
				}

			} else {
				if ( ! current_user_can( 'delete_plugins' ) ) {
					return;
				}
	
				/* translators: %s: html tags */
				$message = sprintf( __('%3$sGreat news!%4$s The %1$sPDF Mockups Addon%2$s is now integrated into the %1$sSureFeedback Admin plugin%2$s. Please delete the %1$sPDF Mockups plugin%2$s for smoother functionality.', 'project-huddle' ), '<b>', '</b>', '<i>', '</i>' );
				
				if ( is_multisite() ) {
					deactivate_plugins( $plugin );
    				delete_plugins( array( $plugin ) );
				} else {
					$action_url = add_query_arg(
						array(
							'action' => 'delete-selected',
							'checked' => urlencode( $plugin ),
							'plugin_status' => 'all',
							'paged' => 1,
							'_wpnonce' => wp_create_nonce( 'bulk-plugins' ), // Adding the nonce here.
						),
						admin_url( 'plugins.php' )
					);
				}
	
				$button_label = __( 'Delete - PDF Mockups Plugin', 'project-huddle' );
			}

			$button = '<p><a href="' . $action_url . '" class="button-primary">' . $button_label . '</a></p><p></p>';

			printf( '<div class="%1$s"><p>%2$s</p>%3$s</div>', esc_attr( $class ), wp_kses_post( $message ), isset($button) ? wp_kses_post( $button ) : '' );
		}

		/**
		 * Get the template path.
		 *
		 * @return string
		 */
		public function template_path()
		{
			return apply_filters('ph_template_path', 'project-huddle/');
		}

		/**
		 * Loads the plugin language files
		 *
		 * @access public
		 * @since 1.0.0
		 * @return void
		 */
		public function load_textdomain()
		{
			// Set filter for plugin's languages directory.
			$ph_lang_dir = PH_PLUGIN_DIR . '/languages/';
			$ph_lang_dir = apply_filters('ph_languages_directory', $ph_lang_dir);

			// Traditional WordPress plugin locale filter.
			$locale = apply_filters('plugin_locale', get_locale(), 'ph');
			$mofile = sprintf('%1$s-%2$s.mo', 'project-huddle', $locale);

			// Setup paths to current locale file.
			$mofile_local  = $ph_lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/ph/' . $mofile;

			if (file_exists($mofile_global)) {
				// Look in global /wp-content/languages/ph folder.
				load_textdomain('project-huddle', $mofile_global);
			} elseif (file_exists($mofile_local)) {
				// Look in local /wp-content/plugins/project-huddle/languages/ folder.
				load_textdomain('project-huddle', $mofile_local);
			} else {
				// Load the default language files.
				load_plugin_textdomain('project-huddle', false, $ph_lang_dir);
			}
		}

		public function register_notices()
		{
			$image_path = PH_PLUGIN_URL . 'assets/img/project-huddle-icon.png';

			$websites_count = count(
				get_posts(
					array(
						'post_type' => 'ph-website',
					)
				)
			);
			$mockups_count  = count(
				get_posts(
					array(
						'post_type' => 'ph-project',
					)
				)
			);

			Astra_Notices::add_notice(
				array(
					'id'                         => 'project-huddle-rating',
					'type'                       => '',
					'message'                    => sprintf(
						'<div class="notice-image">
							<img src="%1$s" class="custom-logo" alt="Sidebar Manager" itemprop="logo"></div> 
							<div class="notice-content">
								<div class="notice-heading">
									%2$s
								</div>
								%3$s<br />
								<div class="astra-review-notice-container">
									<a href="%4$s" class="astra-notice-close astra-review-notice button-primary" target="_blank">
									%5$s
									</a>
								<span class="dashicons dashicons-calendar"></span>
									<a href="#" data-repeat-notice-after="%6$s" class="astra-notice-close astra-review-notice">
									%7$s
									</a>
								<span class="dashicons dashicons-smiley"></span>
									<a href="#" class="astra-notice-close astra-review-notice">
									%8$s
									</a>
								</div>
							</div>',
						$image_path,
						__( 'Hello! Seems like you have used SureFeedback to collect feedback on this website — Thanks a ton!', 'project-huddle' ),
						__( 'Could you please do us a BIG favor and give it a 5-star rating on WordPress? This would boost our motivation and help other users make a comfortable decision while choosing the SureFeedback.', 'project-huddle' ),
						'https://wordpress.org/support/plugin/projecthuddle-child-site/reviews/?filter=5#new-post',
						__( 'Ok, you deserve it', 'project-huddle' ),
						MONTH_IN_SECONDS,
						__( 'Nope, maybe later', 'project-huddle' ),
						__( 'I already did', 'project-huddle' )
					),
					'show_if'                    => ( $websites_count >= 3 || $mockups_count >= 3 ) ? true : false,
					'repeat-notice-after'        => '',
					'display-notice-after'       => 1296000, // Display notice after 15 days.,
					'priority'                   => 10,
					'display-with-other-notices' => true,
				)
			);
		}

	}
else :
	/**
	 * SureFeedback already activated
	 *
	 * @return void
	 */
	function ph_already_activated_error_notice()
	{
		$message = __('You have both Pro and Lite versions of SureFeedback activated. Please deactivate one of the plugins in order for SureFeedback to work properly.', 'project-huddle');
		echo '<div class="error"> <p>' . esc_html($message) . '</p></div>';
	}
	add_action('admin_notices', 'ph_already_activated_error_notice');
endif;

/**
 * The main function responsible for returning the one true Project_Huddle
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $ph = PH(); ?>
 *
 * @since 1.0.0
 * @return object The one true Project_Huddle Instance
 */
if (!function_exists('PH')) {
	// phpcs:ignore
	function PH($abstract = null, array $parameters = [])
	{
		return Project_Huddle::instance();
	}

	// Get PH Running.
	PH();
}
