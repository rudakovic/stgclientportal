<?php
/**
 * Plugin Name:          WooCommerce Order Proposal
 * Plugin URI:           https://wpovernight.com/downloads/woocommerce-order-proposal/
 * Description:          Adds the ability to create order proposals to WooCommerce
 * Version:              2.0.5
 * Requires at least:    4.9
 * Requires PHP:         7.2
 * Author:               WP Overnight
 * Author URI:           https://wpovernight.com/
 * License:              GPLv3
 * License URI:          http://www.gnu.org/licenses/gpl-3.0
 * Text Domain:          woocommerce-order-proposal
 * Domain Path:          /languages
 * WC requires at least: 3.3
 * WC tested up to:      9.0
 */

/**
 * WC_Order_Proposal class
 */
if ( ! class_exists( 'WC_Order_Proposal' ) ) {

	class WC_Order_Proposal {

		/**
		 * Singleton variable
		 *
		 * @var WC_Order_Proposal
		 */
		private static $_this;

		/**
		 * @var string
		 */
		private $plugin_path;

		/**
		 * @var string
		 */
		private $plugin_url;

		/**
		 * @var WPO_Updater|WPO_Update_Helper
		 */
		private $updater;

		/**
		 * @var string
		 */
		public $plugin_basename;

		/**
		 * @var WC_Order_Proposal_Order_Util
		 */
		public $order_util;

		/**
		 * @var array
		 */
		public static $admin_errors = array();

		/**
		 * @var WC_Order_Proposal_Dependencies
		 */
		public $dependency;

		const ORDER_PROPOSAL_VERSION             = '2.0.5';
		const ORDER_PROPOSAL_TIME                = '_order-proposal-time';
		const ORDER_PROPOSAL_START_TIME          = '_order-proposal-start-time';
		const ORDER_PROPOSAL_PREPAY              = '_order-proposal-prepay';
		const ORDER_PROPOSAL_DEFAULT_PREPAY      = '_order-proposal-prepay';
		const ORDER_PROPOSAL_CANCEL_EXPIRED      = '_order-proposal-cancel-expired';
		const ORDER_PROPOSAL_NO_REDUCE_STOCK     = '_order-proposal-no-reduce-stock';
		const ORDER_PROPOSAL_USED                = '_order-proposal-used';
		const ORDER_PROPOSAL_PAY_NO_LOGIN        = '_order_proposal_pay_no_login';
		const ORDER_PROPOSAL_DEFAULT_TIME        = 14;
		const ORDER_PROPOSAL_DEFAULT_TIME_OPTION = 'woocommerce_order_proposal_default_proposal_valid_days';
		const ORDER_OLD_TIME                     = 'order-proposal-old-time'; // to gmt: get_gmt_from_date('Y-M-D H:M:S')
		const ORDER_PROPOSAL_DATE_CHANGED        = 'order-proposal-date-changed';
		const ORDER_PROPOSAL_RESERVE_STOCK       = 'woocommerce_order_proposal_reserve_stock';
		const ORDER_PROPOSAL_HIDE_PROPOSALS      = 'woocommerce_order_proposal_hide_proposals';

		public static function instance(): WC_Order_Proposal {
			if ( ! isset( self::$_this ) ) {
				self::$_this = new WC_Order_Proposal();
			}

			return self::$_this;
		}

		public function __construct() {
			// If singleton exists use it
			if ( isset( self::$_this ) ) {
				return $this->instance();
			}

			$this->dependency = include_once $this->get_plugin_path() . '/includes/class-wc-order-proposal-dependencies.php';

			if ( ! $this->dependency->check_dependencies() ) {
				return;
			}

			$this->dependency->check_quotation_compatibility();

			self::$_this = $this;

			$this->plugin_basename = plugin_basename( __FILE__ );

			// Set plugin path
			$this->get_plugin_path();

			$this->get_plugin_url();

			// Load the textdomain
			add_action( 'init', array( $this, 'load_plugin_textdomain' ), 0 );

			// Direct includes
			add_filter( 'plugins_loaded', array( $this, 'includes_direct') );
			
			// Add compatibility files
			add_filter( 'init', array( $this, 'includes') );

			// Plugin activation
			add_action( 'admin_notices', array( $this, 'plugin_activation' ) ) ;

			// Add order status
			add_filter( 'wc_order_statuses', array( $this, 'wc_order_proposal_add_order_statuses') );

			// Register the payment gateway
			add_filter( 'woocommerce_payment_gateways', array( $this, 'wc_order_proposal_add_to_payment_gateways' ) );

			// Add notification hooks for order changes
			add_filter( 'woocommerce_email_actions', array( $this, 'add_wc_order_proposal_email_actions') );
			add_action( 'woocommerce_email', array( $this, 'add_wc_order_proposal_email') );

			// Add email hooks
			add_action( 'woocommerce_email_classes', array( $this, 'wc_order_proposal_change_woocommerce_email') );
			add_filter( 'wpo_wcpdf_resend_order_emails_available', array( $this, 'wc_order_quote_woocommerce_resend_email' ), 10, 2 );   // WCPDF >= 3.5.7

			// Add order proposal as an editable state
			add_filter( 'wc_order_is_editable', array( $this, 'is_editable'), 10, 2);

			// Add admin javascript/css file
			add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts') );

			// Add frontend javascript/css file
			add_action( 'wp_enqueue_scripts', array( $this, 'register_frontend_scripts') );

			// Cron Jobs
			if ( ! wp_next_scheduled( 'add_task_remove_old_proposal' ) ) {
				wp_schedule_event( time(), 'twicedaily', 'add_task_remove_old_proposal' );
			}

			add_action( 'add_task_remove_old_proposal', array( $this, 'wc_order_proposal_schedule_event_remove_old_proposal') );
			
			// Load the updater
			add_action( 'init', array( $this, 'load_updater' ), 0 );

			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
				add_action( 'wp_loaded', array( $this, 'do_install' ) );
			}

			// HPOS compatibility
			add_action( 'before_woocommerce_init', array( $this, 'woocommerce_hpos_compatible' ) );
		}

		/**
		 * Handles version checking
		 */
		public function do_install() {
			$version_setting   = 'wpo_order_proposal_version';
			$installed_version = get_option( $version_setting );

			// installed version lower than plugin version?
			if ( version_compare( $installed_version, self::ORDER_PROPOSAL_VERSION, '<' ) ) {
				if ( ! $installed_version ) {
					$this->install();
				} else {
					$this->upgrade( $installed_version );
				}

				// new version number
				update_option( $version_setting, self::ORDER_PROPOSAL_VERSION );
			}
		}

		public function install() {
			do_action( 'wc_order_proposal_install' );
		}

		public function upgrade( $version ) {
			// New display number option
			if ( version_compare( $version, '2.0.1', '<' ) ) {
				$document_settings = array(
					'wpo_wcpdf_documents_settings_proposal'           => 'enable_proposal_number',
					'wpo_wcpdf_documents_settings_order-confirmation' => 'enable_confirmation_number'
				);

				foreach ( $document_settings as $settings_key => $number_key ) {
					$settings = get_option( $settings_key, array() );

					if ( ! empty( $settings ) && isset( $settings['display_number'] ) ) {
						$settings['display_number'] = isset( $settings[ $number_key ] ) ? $number_key : 'order_number';
					}

					update_option( $settings_key, $settings );
				}
			}

			do_action( 'wc_order_proposal_upgrade' );
		}

		/**
		 * Register our payment gateway
		 * WP filter: woocommerce_payment_gateways
		 */
		public function wc_order_proposal_add_to_payment_gateways( $methods ) {
			$methods[] = 'WC_Gateway_Order_Proposal';
			return $methods;
		}

		// Plugin activation
		public static function plugin_activation() {
			if ( ! get_option( 'wc_order_proposal_note_shown' ) ) {
				$html = '<div class="updated"><p>';
				$html .= __( 'The <b>Order Proposal</b> documentation is available <a href="https://docs.wpovernight.com/category/order-proposal/" target="_blank">on this page</a>. The settings can be <a href="admin.php?page=wc-settings">found here</a>.', 'woocommerce-order-proposal' );
				$html .= '</p></div>';

				update_option( 'wc_order_proposal_note_shown', 'true' );
				echo $html;
			}
		}

		/**
		 * Load the localization
		 *
		 * @access	public
		 * @uses	load_plugin_textdomain, plugin_basename
		 * @return	void
		 */
		public function load_plugin_textdomain() {
			if ( function_exists( 'determine_locale' ) ) { // WP5.0+
				$locale = determine_locale();
			} else {
				$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			}

			load_textdomain( 'woocommerce-order-proposal', dirname( plugin_basename( __FILE__ ) ) . 'plugins/woocommerce-order-proposal-' . $locale . '.mo' );
			load_plugin_textdomain( 'woocommerce-order-proposal', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		// Direct Includes
		public function includes_direct() {
			require_once 'includes/gateways/class-wc-gateway-order-proposal.php';
		}

		public function includes() {
			$this->wc_order_proposal_status();
			$this->order_util = require_once $this->get_plugin_path() . '/compatibility/class-woocommerce-order-util.php';

			if ( $this->dependency->check_pdf_invoices_compatibility() ) {
				require_once $this->get_plugin_path() . '/compatibility/class-woocommerce-pdf-invoices.php';
			}

			require_once 'compatibility/class-woocommerce-multilingual.php';
			require_once 'includes/wc-order-proposal-functions.php';
			require_once 'includes/class-wc-order-proposal-order.php';
			require_once 'includes/class-wc-order-proposal-ajax.php';
			$this->init_global_order_proposal_time();
		}

		/**
		 * Init the WPO Sidekick updater
		 * @return void
		 */
		public function load_updater() {
			// Init updater data
			$item_name		= 'WooCommerce Order Proposal';
			$file			= __FILE__;
			$license_slug	= 'wpo_wc_order_proposal_license';
			$version		= self::ORDER_PROPOSAL_VERSION;
			$author			= 'WP Overnight';

			// load updater
			if ( class_exists( 'WPO_Updater' ) ) { // WP Overnight Sidekick plugin
				$this->updater = new WPO_Updater( $item_name, $file, $license_slug, $version, $author );
			} else { // bundled updater
				$updater_helper_file = $this->get_plugin_path() . '/updater/update-helper.php';

				if ( ! class_exists( 'WPO_Update_Helper' ) && file_exists( $updater_helper_file ) ) {
					include_once $updater_helper_file;
				}

				if ( class_exists( 'WPO_Update_Helper' ) ) {
					$this->updater = new WPO_Update_Helper( $item_name, $file, $license_slug, $version, $author );
				}
			}

			// if no license is entered, show notice in plugin settings page
			if( is_callable( array( $this->updater, 'license_is_active' ) ) && ! $this->updater->license_is_active() ) {
				add_action( 'woocommerce_settings_general', array( $this, 'no_active_license_message' ), -10 );
			}
		}

		/**
		 * Show a notice on settings page when the license has not been activated
		 * @return void
		 */
		public function no_active_license_message() {
			if( class_exists('WPO_Updater') ) {
				$activation_url = esc_url_raw( network_admin_url( 'admin.php?page=wpo-license-page' ) );
			} else {
				$activation_url = esc_url_raw( network_admin_url( 'plugins.php?s=WooCommerce+Order+Proposal#woocommerce-order-proposal-manage-license' ) );
			}
			?>
			<div class="notice notice-warning inline">
				<p>
					<?php
						printf(
							/* translators: 1. plugin name, 2. click here */
							__( 'Your license of %1$s has not been activated on this site, %2$s to enter your license key.', 'woocommerce-order-proposal' ),
							'<strong>'.__( 'WooCommerce Order Proposal', 'woocommerce-order-proposal' ).'</strong>',
							'<a href="'.$activation_url.'">'.__( 'click here', 'woocommerce-order-proposal' ).'</a>'
						);
					?>
				</p>
			</div>
			<?php
		}
		
		/**
		 * Declares WooCommerce HPOS compatibility.
		 *
		 * @return void
		 */
		public function woocommerce_hpos_compatible() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}
	
		/**
		 * Gets the absolute plugin path without a trailing slash, e.g.
		 * /path/to/wp-content/plugin-directory
		 *
		 * @return string plugin path
		 */
		public function get_plugin_path() {
			if ( isset( $this->plugin_path ) ) {
				return $this->plugin_path;
			}

			return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Gets the absolute url
		 *
		 * @return string plugin url
		 */
		public function get_plugin_url() {
			if ( isset( $this->plugin_url ) ) {
				return $this->plugin_url;
			}

			return $this->plugin_url = plugin_dir_url( __FILE__ );
		}

		public function get_order_proposal_time( $order_id ) {
			return wc_order_proposal_get_date($order_id);
		}

		/**
		 * Add scripts to the wordpress
		 * WP action: admin_enqueue_scripts
		 */
		public function register_admin_scripts() {
			wp_enqueue_style( 'wc_order_proposal-css', $this->get_plugin_url()  . 'assets/css/admin.css' );
		}

		/**
		 * Add frontend scripts to the wordpress
		 * WP action: wp_enqueue_script
		 */
		public function register_frontend_scripts() {
			wp_enqueue_script( 'wc_order_proposal-frontend-js', $this->get_plugin_url()  . 'assets/js/frontend.js' , array( 'jquery' ) );
			wp_localize_script(
				'wc_order_proposal-frontend-js',
				'wpo_wcop',
				array( 'decline_proposal' => __( 'Are you sure you want to decline this proposal?', 'woocommerce-order-proposal' ) )
			);
		}


		// If the global value is not set do so
		private function init_global_order_proposal_time() {
			$default_time = wc_order_proposal_get_default_order_time();

			if ( empty($default_time) ) {
				update_option( WC_Order_Proposal::ORDER_PROPOSAL_TIME, WC_Order_Proposal::ORDER_PROPOSAL_DEFAULT_TIME );
			}
		}


		/**
		 * Order Proposal Backend Status
		 * WP filter: init
		 */
		public function wc_order_proposal_status() {
			register_post_status( 'wc-order-proposal', array(
				'label'                     => _x( 'Proposal', 'Order status', 'woocommerce-order-proposal' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: proposal count */
				'label_count'               => _n_noop( 'Order Proposal <span class="count">(%s)</span>', 'Order Proposal <span class="count">(%s)</span>', 'woocommerce-order-proposal' )
			) );

			register_post_status( 'wc-order-proposalreq', array(
				'label'                     => _x( 'Proposal Requested', 'Order status', 'woocommerce-order-proposal' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: proposal requests count */
				'label_count'               => _n_noop( 'Order Proposal Requests <span class="count">(%s)</span>', 'Order Proposal Requests <span class="count">(%s)</span>', 'woocommerce-order-proposal' )
			) );
		}

		/**
		 * Order Proposal Backend Status
		 * WP filter: wc_order_statuses
		 */
		public function wc_order_proposal_add_order_statuses($order_statuses) {
			$new_order_statuses = array();
			// Add new order status after processing
			foreach ($order_statuses as $key => $status) {
				$new_order_statuses[$key] = $status;

				if ('wc-pending' === $key) {
					
					# Show only if gateway is enabled
					if ( class_exists('WC_Gateway_Order_Proposal') && isset(WC()->payment_gateways) ) {
						$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

						if ( array_key_exists('orderproposal', $available_gateways) && isset( $available_gateways['orderproposal']->enabled ) && 'yes' === $available_gateways['orderproposal']->enabled ) {
							$new_order_statuses['wc-order-proposalreq'] = _x( 'Proposal Requested', 'Order status', 'woocommerce-order-proposal' );
						}
					} else {
						# Something is off always show it
						$new_order_statuses['wc-order-proposalreq'] = _x( 'Proposal Requested', 'Order status', 'woocommerce-order-proposal' );
					}
					
					$new_order_statuses['wc-order-proposal'] = _x( 'Proposal', 'Order status', 'woocommerce-order-proposal' );
				}

			}

			return $new_order_statuses;
		}

		/**
		 * Add Order Proposal Email to resend emails
		 */
		public function wc_order_quote_woocommerce_resend_email( $email_array, $order_id ) {
			if ( empty( $order_id ) ) {
				return $email_array;
			}

			if ( wc_order_proposal_order_has_proposal( $order_id ) ) {

				$new_email_array = array();
				// Add new order status after processing
				foreach ( $email_array as $key => $status ) {
					$new_email_array[] = $status;

					if ( $status === 'new_order' ) {
						$new_email_array[] = 'customer_order_proposal';
					}

				}

				$email_array = $new_email_array;
			}

			return $email_array;
		}

		/**
		 * Order Proposal Email hooks
		 * WP action: woocommerce_email_classes
		 */
		public function wc_order_proposal_change_woocommerce_email( $email_class ) {
			$email_class['WC_Email_Order_Proposal']               = include('woocommerce/class-wc-email-order-proposal.php');
			$email_class['WC_Email_Order_Proposal_Declined']      = include('woocommerce/class-wc-email-order-proposal-declined.php');
			$email_class['WC_Email_Order_Proposal_Gateway']       = include('woocommerce/class-wc-email-order-proposal-gateway.php');
			$email_class['WC_Email_Admin_Order_Proposal_Gateway'] = include('woocommerce/class-wc-email-order-proposal-admin.php');
			$email_class['WC_Email_Order_Confirmation']           = include('woocommerce/class-wc-email-order-confirmation.php');

			return $email_class;
		}

		/**
		 * Order Proposal Email actions
		 * WP filter: woocommerce_email_actions
		 */
		public function add_wc_order_proposal_email_actions($actions){
			$actions[] = 'woocommerce_order_status_pending_to_order-proposal';
			$actions[] = 'woocommerce_order_status_draft_to_order-proposal';
			$actions[] = 'woocommerce_order_status_order-proposal_to_processing';
			$actions[] = 'woocommerce_order_status_order-proposal_to_cancelled';
			$actions[] = 'woocommerce_order_status_order-proposal_to_on-hold';

			$actions[] = 'woocommerce_order_status_pending_to_order-proposalreq';
			$actions[] = 'woocommerce_order_status_order-proposalreq_to_order-proposal';

			return $actions;
		}

		/**
		 * Order Proposal Extra Email hooks
		 * WP filter: woocommerce_email
		 */
		public function add_wc_order_proposal_email($wc_emails) {
			$emails = $wc_emails->emails;
			
			// Set the hooks for the email classes as well
			add_action( 'woocommerce_order_status_order-proposal_to_processing_notification', array( $emails['WC_Email_Customer_Processing_Order'], 'trigger' ), 10, 2 );
			add_action( 'woocommerce_order_status_order-proposal_to_cancelled_notification', array( $emails['WC_Email_Cancelled_Order'], 'trigger' ), 10, 2  );
			add_action( 'woocommerce_order_status_order-proposal_to_on-hold_notification', array( $emails['WC_Email_Customer_On_Hold_Order'], 'trigger' ), 10, 2 );

			// Admin
			add_action( 'woocommerce_order_status_order-proposal_to_processing_notification', array( $emails['WC_Email_New_Order'], 'trigger' ), 10, 2 );
			add_action( 'woocommerce_order_status_order-proposal_to_on-hold_notification', array( $emails['WC_Email_New_Order'], 'trigger' ), 10, 2 );
		}

		/**
		 * Order Proposal Editable Status
		 * WP filter: wc_order_is_editable
		 */
		public function is_editable($editable, $order) {
			if( !$editable && in_array($order->get_status(), ['order-proposal', 'order-proposalreq']) ) {
				return true;
			}

			return $editable;
		}


		/**
		 * Order Proposal remove proposal after expiration date
		 * WP action: add_task_remove_old_proposal
		 */
		public function wc_order_proposal_schedule_event_remove_old_proposal() {
			if ( ! wc_order_proposal_cancel_expired() ) {
				return;
			}

			$order_ids = wc_get_orders( array(
				'type'    => 'shop_order',
				'status'  => array( 'wc-order-proposal', 'wc-order-proposalreq' ),
				'orderby' => 'date',
				'order'   => 'DESC',
				'return'  => 'ids',
			) );

			foreach ( $order_ids as $order_id ) {

				$order = wc_get_order( $order_id );

				if ( $order && wc_order_proposal_order_has_proposal( $order_id ) ) {
					// We give an extra 24 hours
					$time  = strtotime( wc_order_proposal_get_date( $order_id ) );
					$today = current_time( 'timestamp' ) - 24 * 60 * 60;

					if ( $today > $time ) {
						do_action( 'wc_order_proposal_remove_old_proposal', $order );

						$order->add_order_note( __( 'Proposal validation date reached.', 'woocommerce-order-proposal' ) );
						$order->update_status( 'wc-cancelled' );
					}
				}
			}
		}

	}
}

/**
 * Returns the One True Instance of Order Proposal
 *
 * @return WC_Order_Proposal
 */
function wc_order_proposal() {
	return WC_Order_Proposal::instance();
}
wc_order_proposal();