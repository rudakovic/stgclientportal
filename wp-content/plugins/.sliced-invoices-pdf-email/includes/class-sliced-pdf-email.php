<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Sliced_Pdf_Email
 */
class Sliced_Pdf_Email {
	
	/** @var  object  Instance of this class */
	protected static $instance = null;
	
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
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	public function __construct() {
		
		load_plugin_textdomain(
			'sliced-invoices-pdf-email',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		); 
		
		if ( ! $this->validate_settings() ) {
			return;
		}
		
		Sliced_Pdf::get_instance();
		Sliced_Emails::get_instance();
		Sliced_Invoices_Pdf_Admin::get_instance();
		
		add_action( 'sliced_head', array( $this, 'sliced_pdf_enqueue_styles' ), 999 );
		add_filter( 'sliced_invoices_request_data', array( $this, 'request_data' ), 10, 2 );
		
	}
	
	
	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	public function sliced_pdf_enqueue_styles() {
		
		$css = '';
		$pdf_options = get_option( 'sliced_pdf' );
		
		// do we need to add an extra_font clause?
		if ( $pdf_options ) {
			if ( ! empty ( $pdf_options['extra_font_id'] ) || ! empty( $pdf_options['extra_font_ext_id'] ) ) {
				$css .= "@media only print { body{ font-family: extra_font; } }\n";
			}
		}
		
		// custom font size
		if ( $pdf_options ) {
			if ( ! empty ( $pdf_options['page_font_size'] ) ) {
				$css .= "@media only print { body{ font-size: " . $pdf_options['page_font_size'] . "; } }\n";
			}
		}
		
		// add the users custom PDF css last, if any
		$type = sliced_get_the_type();
		$options = get_option( "sliced_{$type}s" );
		$custom_css = isset( $options['pdf_css'] ) ? html_entity_decode( $options['pdf_css'] ) : false;
		if ( $custom_css ) {
			// wrap in a print only query
			$css .= "@media only print { " . $custom_css . "}\n";
		}
		
		$css = apply_filters( 'sliced_pdf_custom_css', $css );
		
		?>
		<link rel='stylesheet' id='print-css' href='<?php echo plugins_url( 'sliced-invoices-pdf-email' ) . '/public/css/print.css'; ?>?ver=<?php echo SLICED_INVOICES_PDF_VERSION; ?>' type='text/css' media='print' />
		<style id='print-inline-css' type='text/css'>
			<?php echo $css; ?>
		</style>
		<?php
		
	}
	
	
	/**
	 * Output requirements not met notice.
	 *
	 * @since   1.6.2
	 */
	public function requirements_not_met_notice() {
		echo '<div id="message" class="error">';
		echo '<p>' . sprintf( __( 'Sliced Invoices PDF extension cannot find the required <a href="%s">Sliced Invoices plugin</a>. Please make sure the core Sliced Invoices plugin is <a href="%s">installed and activated</a>.', 'sliced-invoices-pdf-email' ), 'https://wordpress.org/plugins/sliced-invoices/', admin_url( 'plugins.php' ) ) . '</p>';
		echo '</div>';
	}
	
	
	/**
	 * Validate settings, make sure all requirements met, etc.
	 *
	 * @version 1.7.1
	 * @since   1.6.2
	 */
	public function validate_settings() {
		
		if ( ! class_exists( 'Sliced_Invoices' ) ) {
			
			// Add a dashboard notice.
			add_action( 'admin_notices', array( $this, 'requirements_not_met_notice' ) );
			
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Defines the function used to interact with the cURL library.
	 *
	 * @since   1.7.0
	 * @author  David Grant
	 */
	private static function curl( $url ) {
		
		if ( ! function_exists( 'curl_init' ) ) {
			return false;
		}
		
		$curl = curl_init( $url );
		
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_HEADER, 0 );
		curl_setopt( $curl, CURLOPT_USERAGENT, 'Sliced Invoices/'.SLICED_VERSION.' (via cURL)' );
		curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 10 );
		curl_setopt( $curl, CURLOPT_TIMEOUT, 20 );
		curl_setopt( $curl, CURLOPT_TIMEOUT_MS, 20000 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
		
		do_action( 'sliced_pre_curl_exec', $curl );
		
		$response = curl_exec( $curl );
		
		if ( 0 !== curl_errno( $curl ) || 200 !== curl_getinfo( $curl, CURLINFO_HTTP_CODE ) ) {
			$response = null;
		}
		curl_close( $curl );
		
		return $response;
	}
	
	
	/**
	 * Retrieves the response from the specified URL using one of PHP's outbound
	 * request facilities.
	 *
	 * This compensates for deficiencies in WP's wp_remote_get() function that the
	 * WordPress team refuses to fix.
	 *
	 * Original idea from Tom McFarlin (https://tommcfarlin.com/wp_remote_get/)
	 * Yes, the blog post is dated 2013.  Yes, the problems he describes are still
	 * present in WordPress as of 2020. :-/
	 *
	 * Includes my own modifications based on 4+ years of success using this
	 * approach.
	 *
	 * @since   1.7.0
	 * @author  David Grant
	 */
	public static function request_data( $response, $url ) {
		
		$response = null;
		
		// First, we try to use wp_remote_get
		$response = wp_remote_get(
			$url, 
			array(
				'sslverify' => false,
				'timeout'   => 10,
			)
		);
		
		if ( ! $response || is_wp_error( $response ) ) {
			
			// If that doesn't work, then we'll try file_get_contents
			$response = @file_get_contents( $url );
			
			if ( false == $response ) {
				
				// And if that doesn't work, then we'll try curl
				$response = self::curl( $url );
				
			}
		
		}
		
		// If the response is an array, it's coming from wp_remote_get,
		// so we just want to capture to the body index for json_decode.
		if ( is_array( $response ) ) {
			$response = $response['body'];
		}
		
		return $response;
	}
	
}
