<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Sliced_Invoices_Stripe_Admin
 */
class Sliced_Invoices_Stripe_Admin {
	
	/** @var  object  Instance of this class */
	protected static $instance = null;
	
	/** @var string Unique prefix for this gateway */
	protected $prefix = 'sliced-stripe';
	
	/** @var string Unique slug for this gateway */
	protected $slug   = 'sliced_stripe';
	
	/**
	 * Gets the instance of this class, or constructs one if it doesn't exist.
	 */
	public static function get_instance() {
		
		if ( null === self::$instance ) {
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
	 * @version 2.1.0
	 * @since   2.1.0
	 */
	public function __construct() {
		
		add_action( 'admin_head', array( $this, 'admin_inline_css' ) );
		add_action( 'admin_init', array( $this, 'admin_notices' ) );
		add_filter( 'sliced_payment_option_fields', array( $this, 'add_options_fields') );
		add_filter( 'sliced_translate_option_fields', array( $this, 'add_translate_options' ) );
		
	}
	
	/**
	 * Adds the options for this gateway to the admin payment settings.
	 *
	 * @version 2.1.0
	 * @since   1.0.0
	 */
	public function add_options_fields( $options ) {
		
		$options['fields'][] = array(
			'name'        => __( 'Enable', 'sliced-invoices-stripe' ),
			'type'        => 'checkbox',
			'id'          => 'stripe_enabled',
			'before_row'  => array( $this, 'settings_group_before' ),
		);
		$options['fields'][] = array(
			'name'        => __( 'Stripe Currency', 'sliced-invoices-stripe' ),
			'desc'        => __( '3 letter code - <a href="https://support.stripe.com/questions/which-currencies-does-stripe-support" target="_blank">Full list of accepted currencies here</a>', 'sliced-invoices-stripe' ),
			'default'     => 'USD',
			'type'        => 'text',
			'id'          => 'stripe_currency',
		);
		$options['fields'][] = array(
			'name'        => __( 'Country Code', 'sliced-invoices-stripe' ),
			'desc'        => __( 'Your 2-digit country code', 'sliced-invoices-stripe' ),
			'default'     => 'US',
			'type'        => 'text',
			'id'          => 'stripe_apple_pay_country',
		);
		$options['fields'][] = array(
			'name'        => __( 'LIVE Publishable Key', 'sliced-invoices-stripe' ),
			'desc'        => __( 'The Publishable Key from your live Stripe account, found under "Developers" and then "API Keys".', 'sliced-invoices-stripe' ),
			'type'        => 'text',
			'id'          => 'stripe_publishable',
		);
		$options['fields'][] = array(
			'name'        => __( 'LIVE Secret Key', 'sliced-invoices-stripe' ),
			'desc'        => __( 'The Secret Key from your live Stripe account, found under "Developers" and then "API Keys".', 'sliced-invoices-stripe' ),
			'type'        => 'text',
			'id'          => 'stripe_secret',
		);
		$options['fields'][] = array(
			'name'        => __( 'TEST Publishable Key', 'sliced-invoices-stripe' ),
			'desc'        => __( 'The Publishable Key from your test mode Stripe account, found under "Developers" and then "API Keys".', 'sliced-invoices-stripe' ),
			'type'        => 'text',
			'id'          => 'stripe_publishable_test',
		);
		$options['fields'][] = array(
			'name'        => __( 'TEST Secret Key', 'sliced-invoices-stripe' ),
			'desc'        => __( 'The Secret Key from your test mode Stripe account, found under "Developers" and then "API Keys".', 'sliced-invoices-stripe' ),
			'type'        => 'text',
			'id'          => 'stripe_secret_test',
		);
		$options['fields'][] = array(
			'name'    => __( 'Gateway Mode', 'sliced-invoices-stripe' ),
			'desc'    => __( 'Set to Test Mode for testing purposes.<br />Set to Live to accept payments from clients.', 'sliced-invoices-stripe' ),
			'default' => 'test',
			'type'    => 'select',
			'id'      => 'stripe_mode',
			'options' => array(
				'test' => 'Test',
				'live' => 'Live',
			),
		);
		// @TODO: idea for future version...
		// $options['fields'][] = array(
			// 'name'        => __( 'Checkout Type', 'sliced-invoices-stripe' ),
			// 'desc'        => __( '', 'sliced-invoices-stripe' ),
			// 'default'     => 'hosted',
			// 'type'        => 'select',
			// 'id'          => 'stripe_checkout_type',
			// 'options'     => array(
				// 'hosted'      => __( 'Hosted by Stripe', 'sliced-invoices-stripe' ),
				// 'onsite'      => __( 'On-Site (requires your site to have a SSL certificate)', 'sliced-invoices-stripe' ),
			// )
		// );
		$options['fields'][] = array(
			'name'        => __( 'Optional Settings', 'sliced-invoices' ),
			'id'          => 'stripe_title_optional_settings',
			'type'        => 'title',
		);
		$options['fields'][] = array(
			'name'        => __( 'Show and require "Name on Card" field', 'sliced-invoices-stripe' ),
			'type'        => 'checkbox',
			'id'          => 'stripe_require_name',
		);
		$options['fields'][] = array(
			'name'        => __( 'Include Stripe JS sitewide', 'sliced-invoices-stripe' ),
			'after_field' => '<p class="cmb2-metabox-description" style="clear:both;">' . __( 'Normally Sliced Invoices loads the Stripe JS library only on the invoice payment page, but if you need to load it for your entire site enable this option.', 'sliced-invoices-stripe' ) . '</p>',
			'type'        => 'checkbox',
			'id'          => 'stripe_js_sitewide',
		);
		$options['fields'][] = array(
			'name'        => __( 'Additional Payment Providers', 'sliced-invoices' ),
			'id'          => 'stripe_title_payment_providers',
			'type'        => 'title',
		);
		$options['fields'][] = array(
			'name'        => __( 'Enable Apple Pay', 'sliced-invoices-stripe' ),
			'after_field' => '<p class="cmb2-metabox-description" style="clear:both;">' . __( 'Apple Pay must be enabled in your Stripe account, and your domain verified.', 'sliced-invoices-stripe' ) . '<br />' . sprintf( __( 'For further help, see the <a href="%s" target="_blank">Stripe Apple Pay documentation</a>', 'sliced-invoices-stripe' ), 'https://stripe.com/docs/stripe-js/elements/payment-request-button#verifying-your-domain-with-apple-pay' ) . '</p>',
			'type'        => 'checkbox',
			'id'          => 'stripe_apple_pay',
		);
		$options['fields'][] = array(
			'name'        => __( 'Enable Payment Request Button', 'sliced-invoices-stripe' ),
			'after_field' => '<p class="cmb2-metabox-description" style="clear:both;">' . __( 'For your customers who use Google Pay, Microsoft Pay, and the Payment Request API.', 'sliced-invoices-stripe' ) . '</p>',
			'type'        => 'checkbox',
			'id'          => 'stripe_payment_request',
		);
		$options['fields'][] = array(
			'name'        => __( 'Enable Alipay via Stripe', 'sliced-invoices-stripe' ),
			'after_field' => '<p class="cmb2-metabox-description" style="clear:both;">' . __( 'Alipay must be enabled in your Stripe account. Only supports payments in AUD, CAD, EUR, GBP, HKD, JPY, NZD, SGD, or USD. Users in Denmark, Norway, Sweden, or Switzerland must use EUR.', 'sliced-invoices-stripe' ) . '</p>',
			'type'        => 'checkbox',
			'id'          => 'stripe_alipay',
		);
		$options['fields'][] = array(
			'name'        => __( 'Enable Bancontact via Stripe', 'sliced-invoices-stripe' ),
			'after_field' => '<p class="cmb2-metabox-description" style="clear:both;">' . __( 'Bancontact must be enabled in your Stripe account. Only supports payments in EUR.', 'sliced-invoices-stripe' ) . '</p>',
			'type'        => 'checkbox',
			'id'          => 'stripe_bancontact',
		);
		$options['fields'][] = array(
			'name'        => __( 'Enable Giropay via Stripe', 'sliced-invoices-stripe' ),
			'after_field' => '<p class="cmb2-metabox-description" style="clear:both;">' . __( 'Giropay must be enabled in your Stripe account. Only supports payments in EUR.', 'sliced-invoices-stripe' ) . '</p>',
			'type'        => 'checkbox',
			'id'          => 'stripe_giropay',
		);
		$options['fields'][] = array(
			'name'        => __( 'Enable iDEAL via Stripe', 'sliced-invoices-stripe' ),
			'after_field' => '<p class="cmb2-metabox-description" style="clear:both;">' . __( 'iDEAL must be enabled in your Stripe account. Only supports payments in EUR.', 'sliced-invoices-stripe' ) . '</p>',
			'type'        => 'checkbox',
			'id'          => 'stripe_ideal',
		);
		$options['fields'][] = array(
			'name'        => __( 'Enable P24 via Stripe', 'sliced-invoices-stripe' ),
			'after_field' => '<p class="cmb2-metabox-description" style="clear:both;">' . __( 'Przelewy24 must be enabled in your Stripe account. Only supports payments in EUR or PLN.', 'sliced-invoices-stripe' ) . '</p>',
			'type'        => 'checkbox',
			'id'          => 'stripe_p24',
			'after_row'   => array( $this, 'settings_group_after' ),
		);
		
		return $options;
	}
	
	/**
	 * Adds the options for this gateway in the translate settings tab.
	 * Deprecated. For compatibility with Easy Translate Extension < v2.0.0. Will be removed soon.
	 *
	 * @version 2.1.0
	 * @since   1.1.3
	 */
	public function add_translate_options( $options ) {
		
		if (
			class_exists( 'Sliced_Translate' )
			&& defined( 'SI_TRANSLATE_VERSION' )
			&& version_compare( SI_TRANSLATE_VERSION, '2.0.0', '<' )
		) {
			$options['fields'][] = array(
				'name'       => __( 'Stripe Gateway', 'sliced-invoices-stripe' ),
				'id'         => 'translate_stripe_title',
				'type'       => 'title',
			);
			$options['fields'][] = array(
				'name'       => __( 'Pay with Stripe', 'sliced-invoices-stripe' ),
				'type'       => 'text',
				'id'         => 'gateway-stripe-label',
				'attributes' => array(
					'class'      => 'i18n-multilingual regular-text',
				),
			);
		}
		
		return $options;
	}
	
	/**
	 * Adds inline css to admin area.
	 *
	 * @since 1.5.0
	 */
	public function admin_inline_css() {
	
		global $pagenow;
		
		if ( $pagenow === 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] === 'sliced_invoices_settings' ) {
			?>
			<style type="text/css">
				#<?php echo $this->prefix; ?>-settings-wrapper {
				}
				#<?php echo $this->prefix; ?>-settings-header {
					background: #f8f8f8 none repeat scroll 0 0;
					border: 1px solid #e5e5e5;
					border-radius: 3px;
					margin: 10px 20px;
					padding: 15px 25px 15px 12px;
				}
				#<?php echo $this->prefix; ?>-settings-header th {
					cursor: pointer;
					padding-bottom: 10px;
				}
				#<?php echo $this->prefix; ?>-settings-header .row-toggle {
					text-align: right;
				}
				#<?php echo $this->prefix; ?>-settings-header .row-title {
					padding: 0 20px 0 20px;
				}
				#<?php echo $this->prefix; ?>-settings > td {
					padding-left: 40px;
				}
			</style>
			<?php
		}
	}
	
	/**
	 * Admin notices for various things.
	 *
	 * @since   1.4.0
	 */
	public function admin_notices() {
		
		// check just in case we're on < Sliced Invoices v3.5.0
		if ( class_exists( 'Sliced_Admin_Notices' ) ) {
		
			// placeholder for future notices
			
		}
		
	}
	
	/**
	 * Begins wrapper (collapsable) around gateway settings.
	 *
	 * @since 1.4.1
	 */
	public function settings_group_before() {
		#region settings_group_before
		?>
		<table class="widefat" id="<?php echo $this->prefix; ?>-settings-wrapper">
			<tr id="<?php echo $this->prefix; ?>-settings-header">
				<th class="row-title"><h4><?php _e( 'Stripe Gateway', 'sliced-invoices-stripe' ); ?></h4></th>
				<th class="row-toggle"><span class="dashicons dashicons-arrow-down" id="<?php echo $this->prefix; ?>-settings-toggle"></span></th>
			</tr>
			<tr id="<?php echo $this->prefix; ?>-settings" style="display:none;">
				<td colspan="2">
		<?php
		#endregion settings_group_before
	}
	
	/**
	 * Ends wrapper (collapsable) around gateway settings.
	 *
	 * @since 1.4.1
	 */
	public function settings_group_after() {
		#region settings_group_after
		?>
				</td>
			</tr>
		</table>
		<script type="text/javascript">
			jQuery( '#<?php echo $this->prefix; ?>-settings-header' ).click( function(){
				var settingsElem = jQuery( '#<?php echo $this->prefix; ?>-settings' );
				var toggleElem   = jQuery( '#<?php echo $this->prefix; ?>-settings-toggle' );
				if ( jQuery( settingsElem ).is(':visible') ) {
					jQuery( settingsElem ).slideUp();
					jQuery( toggleElem ).removeClass( 'dashicons-arrow-up').addClass( 'dashicons-arrow-down' );
				} else {
					jQuery( settingsElem ).slideDown();
					jQuery( toggleElem ).removeClass( 'dashicons-arrow-down').addClass( 'dashicons-arrow-up' );
				}
			});
		</script>
		<?php
		#endregion settings_group_after
	}
	
}
