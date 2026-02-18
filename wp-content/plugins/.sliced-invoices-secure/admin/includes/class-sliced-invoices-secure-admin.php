<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Sliced_Invoices_Secure_Admin
 */
class Sliced_Invoices_Secure_Admin {
	
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
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	public function __construct() {
		
		add_filter( 'get_sample_permalink_html', array( $this, 'add_secure_link_to_admin_permalink' ), 10, 5 );
		add_filter( 'sliced_translate_option_fields', array( $this, 'add_translate_options' ) );
		
	}
	
	
	/**
	 * Add security to end of the permalink in the single edit screen.
	 *
	 * @since 1.0.0
	 */
	public function add_secure_link_to_admin_permalink( $return, $id, $new_title, $new_slug, $post ) {
		if ( sliced_get_the_type() ) {
			$user_id    = sliced_get_client_id();
			$secure     = md5( get_user_meta( $user_id, '_sliced_secure_hash', true ) );
			$link       = add_query_arg( array(
				'verify' => $user_id,
				'secure' => $secure,
			), get_permalink( $id ) );
			$append     = sprintf( __( '<strong>Secure Link:</strong> <a href="%1s">%2s</a>', 'sliced-invoices-secure' ), $link, $link );
			$return     = $return . '<br>' . $append;
		}
		return $return;
	}
	
	
	/**
	 * Add the options for this extension into the translate settings tab.
	 * Deprecated. For compatibility with Easy Translate Extension < v2.0.0. Will be removed soon.
	 *
	 * @version 1.3.0
	 * @since   1.1.5
	 */
	public function add_translate_options( $options ) {
		
		if (
			class_exists( 'Sliced_Translate' )
			&& defined( 'SI_TRANSLATE_VERSION' )
			&& version_compare( SI_TRANSLATE_VERSION, '2.0.0', '<' )
		) {
			
			// add fields to end of options array
			$options['fields'][] = array(
				'name'      => __( 'Secure Invoices', 'sliced-invoices-translate' ),
				'id'        => 'translate_secure_title',
				'type'      => 'title',
			);
			$options['fields'][] = array(
				'name'      => __( 'Access Denied', 'sliced-invoices-translate' ),
				'default'   => Sliced_Secure::$translate['secure-access-denied-label'],
				'type'      => 'text',
				'id'        => 'secure-access-denied-label',
				'attributes' => array(
					'class'      => 'i18n-multilingual regular-text',
				),
			);
			$options['fields'][] = array(
				'name'      => __( 'Access Denied Message', 'sliced-invoices-translate' ),
				'desc'      => __( 'Default: "This item has been secured and is only viewable by following the link that was sent to you via email."  <br />Basic HTML allowed.', 'sliced-invoices-translate' ),
				'default'   => Sliced_Secure::$translate['secure-access-denied-text'],
				'type'      => 'textarea_small',
				'id'        => 'secure-access-denied-text',
				'attributes' => array(
					'class'      => 'i18n-multilingual regular-text',
				),
			);
			
		}
		
		return $options;
	}
	
}
