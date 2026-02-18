<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Sliced_Secure
 */
class Sliced_Secure {
	
	/** @var  object  Instance of this class */
	protected static $instance = null;
	
	/** @var  array  Deprecated. For compatibility with Easy Translate Extension < v2.0.0. Will be removed soon. */
	public static $translate = array();
	
	/**
	 * Gets the instance of this class, or constructs one if it doesn't exist.
	 */
	public static function get_instance() {
		
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Construct the class.
	 *
	 * Populates our current settings, validates settings, and hooks into all the
	 * appropriate filters/actions we will need.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	public function __construct() {
		
		load_plugin_textdomain(
			'sliced-invoices-secure',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
		
		if ( ! $this->validate_settings() ) {
			return;
		}
		
		$this->load_translations();
		
		Sliced_Invoices_Secure_Admin::get_instance();
		
		add_filter( 'sliced_get_the_link', array( $this, 'add_secure_link_to_item' ), 10, 2 );
		add_action( 'user_register', array( $this, 'store_users_hash' ),10, 1 );
		add_action( 'sliced_before_invoice_display', array( $this, 'check_secure_link' ) );
		add_action( 'sliced_before_quote_display', array( $this, 'check_secure_link' ) );
		add_action( 'sliced_before_request_pdf', array( $this, 'check_secure_link' ) );
		
	}
	
	
	/**
	 * Trigger error message.
	 *
	 * @since 1.2.0
	 */
	public function access_denied() {
		add_action( 'sliced_invoice_before_body', array( $this, 'error_message' ) );
		add_action( 'sliced_quote_before_body', array( $this, 'error_message' ) );
		define( 'SLICED_SECURE_ACCESS_DENIED', true );
	}
	
	
	/**
	 * Add security to end of our links.
	 *
	 * @since 1.0.0
	 */
	public function add_secure_link_to_item( $output, $id ) {
		//DG note: here $id is the $post->ID
		$user_id    = sliced_get_client_id( $id );
		$secure     = md5( get_user_meta( $user_id, '_sliced_secure_hash', true ) );
		$link       = add_query_arg( array(
			'verify' => $user_id,
			'secure' => $secure,
		), $output );
		return $link;
	}
	
	
	/**
	 * Checks the secure link, continues if all checks are good, throws error message if checks fail.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	public function check_secure_link() {
		
		global $wp_query;
		
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return; // this is an ajax call, allow it
		}
		
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return; // this is a cron task, allow it
		}
		
		if ( defined( 'SLICED_SECURE_INTERNAL_REQUEST' ) && SLICED_SECURE_INTERNAL_REQUEST ) {
			return; // this is an internal request by Sliced Invoices, allow it
		}
		
		if ( current_user_can( 'administrator' ) ) {
			return; // admins can see all
		}
		
		$payments = get_option( 'sliced_payments' );
		$payment_page = (int) $payments['payment_page'];
		if ( isset( $wp_query->post->ID ) && $wp_query->post->ID === $payment_page ) {
			// it's the payment processing page, don't block it
			return;
		}
		
		if( ! isset( $_GET['verify'] ) || ! isset( $_GET['secure'] ) ) {
			// it's a non-secure link, check if user is logged in
			if ( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
				if ( $current_user->ID === sliced_get_client_id() ) {
					// allow logged in users to view their own quotes/invoices
					return;
				} else {
					// just cuz you're logged in, doesn't mean you can see other people's stuff
					return $this->access_denied();
				}
			} else {
				// not logged in, not secure link, you shall not pass
				return $this->access_denied();
			}
		}
		
		/**
		 * past this point, access will only be allowed if secure hash is present and matches
		 */
		$user_id     = intval( $_GET['verify'] );
		$secure      = $_GET['secure'];
		$stored_hash = get_user_meta( $user_id, '_sliced_secure_hash', true );
		
		// Check that the quote/invoice belongs to the $user_id we're about to verify
		if ( $user_id !== sliced_get_client_id() ) {
			return $this->access_denied();
		}
		
		// The original crypt() hash is wrapped within an md5 hash. 
		// This checks that the crypt() hash on the link matches the users in the database.
		if ( md5( $stored_hash ) !== $secure ) {
			return $this->access_denied();
		}
		
		/**
		 * If we made it here, we're good!
		 */
		return;
	}
	
	
	/**
	 * The error message if checks fail.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	public function error_message() {
		#region error_message
		?>
		<body class="body payment">
			<div class="container sliced-wrap">
				
				<div class="row sliced-header">
					<div class="col-md-6 col-md-offset-3 sliced-business">
						<a target="_blank" href="<?php echo esc_url( sliced_get_business_website() ); ?>">
							<?php echo sliced_get_business_logo() ? '<img class="logo" src="' . esc_url( sliced_get_business_logo() ) . '">' : '<h1>' . esc_html( sliced_get_business_name() ) . '</h1>' ?>
						</a>
					</div>
				</div><!-- END row -->
				
				<div class="row sliced-upper">
					<div class="col-md-6 col-md-offset-3 sliced-message error">
						<h3><?php echo Sliced_Secure::$translate['secure-access-denied-label']; ?></h3>
						<p><?php echo Sliced_Secure::$translate['secure-access-denied-text']; ?></p>
					</div>
				</div><!-- END row -->
				
				<div class="row sliced-footer">
					<div class="col-md-12">
						<div class="footer-text"><?php echo sliced_get_business_footer(); ?></div>
					</div>
				</div><!-- END row -->
			
			</div>
		
		</body>
		
		</html>
		
		<?php
		die;
		#endregion error_message
	}
	
	
	/**
	 * Load translations or use defaults.
	 * Deprecated. For compatibility with Easy Translate Extension < v2.0.0. Will be removed soon.
	 *
	 * @since 1.3.0
	 */
	public function load_translations() {
		Sliced_Secure::$translate = array(
			'secure-access-denied-label' => __( 'Access Denied', 'sliced-invoices-secure' ),
			'secure-access-denied-text'  => __( 'This item has been secured and is only viewable by following the link that was sent to you via email.', 'sliced-invoices-secure' ),
		);
		if (
			class_exists( 'Sliced_Translate' )
			&& defined( 'SI_TRANSLATE_VERSION' )
			&& version_compare( SI_TRANSLATE_VERSION, '2.0.0', '<' )
		) {
			$translate = get_option( 'sliced_translate' );
			foreach ( Sliced_Secure::$translate as $key => $value ) {
				if ( isset( $translate[ $key ] ) ) Sliced_Secure::$translate[ $key ] = $translate[ $key ];
			}
		}
	}
	
	
	/**
	 * Output requirements not met notice.
	 *
	 * @since   1.2.2
	 */
	public function requirements_not_met_notice() {
		echo '<div id="message" class="error">';
		echo '<p>' . sprintf( __( 'Sliced Invoices Secure extension cannot find the required <a href="%s">Sliced Invoices plugin</a>. Please make sure the core Sliced Invoices plugin is <a href="%s">installed and activated</a>.', 'sliced-invoices' ), 'https://wordpress.org/plugins/sliced-invoices/', admin_url( 'plugins.php' ) ) . '</p>';
		echo '</div>';
	}
	
	
	/**
	 * Store users hash in database.
	 *
	 * @since 1.0.0
	 */
	public static function store_users_hash( $user_id ) {
		$user       = get_userdata( $user_id );
		$password   = self::the_password( $user );
		$hash       = self::the_hash( $password );
		update_user_meta( $user_id, '_sliced_secure_hash', $hash );
	}
	
	
	/**
	 * Create the hash from the 'password'.
	 *
	 * @since 1.0.0
	 */
	private static function the_hash( $password ) {
		$unique_salt = substr( sha1( mt_rand() ), 0,22 );
		return crypt( $password, '$2a$10$'. $unique_salt );
	}
	
	
	/**
	 * Create the 'password'.
	 *
	 * @since 1.0.0
	 */
	private static function the_password( $user ) {
		return 'sliced_invoices_' . $user->user_login . $user->user_registered . '_sliced_invoices';
	}
	
	
	/**
	 * Validate settings, make sure all requirements met, etc.
	 *
	 * @since   1.2.2
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
