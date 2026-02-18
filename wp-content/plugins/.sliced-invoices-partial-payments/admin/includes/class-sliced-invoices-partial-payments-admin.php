<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Sliced_Invoices_Partial_Payments_Admin
 */
class Sliced_Invoices_Partial_Payments_Admin {
	
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
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	public function __construct() {
		
		if ( is_admin() && sliced_get_the_type() ) {
			add_action( 'admin_head', array( $this, 'admin_inline_css' ) );
		}
		
		add_action( 'admin_init', array( $this, 'admin_notices' ) );
		add_action( 'cmb2_admin_init', array( $this, 'invoice_side_metabox' ), 20 );
		add_action( 'cmb2_admin_init', array( $this, 'quote_side_metabox' ), 20 );
		add_filter( 'sliced_reporting_colors', array( $this, 'add_status_color' ) );
		
	}
	
	
	/**
	 * Add a color for display in reports
	 *
	 * @since 1.0.0
	 */
	public function add_status_color( $colors ) {
		$colors['partially-paid'] = 'rgba(155, 204, 153, 1)';
		return $colors;
	}
	
	
	/**
	 * Add inline css to admin area.
	 *
	 * @since 1.0.0
	 */
	public function admin_inline_css() {
		#region admin_inline_css
		?>
		<style type="text/css">
			/* partial payments */
			#cmb2-metabox-_sliced_partial_payments_invoice .cmb2-id--sliced-partial-payments-minimum-extend-number,
			#cmb2-metabox-_sliced_partial_payments_quote .cmb2-id--sliced-partial-payments-minimum-extend-number {
				width: 25%;
				float: left;
				margin: 0 10px 0 0;
			}
			#cmb2-metabox-_sliced_partial_payments_invoice .cmb2-id--sliced-partial-payments-minimum-extend-type,
			#cmb2-metabox-_sliced_partial_payments_quote .cmb2-id--sliced-partial-payments-minimum-extend-type {
				width: 45%;
				float: left;
				margin: 0;
			}
			#cmb2-metabox-_sliced_partial_payments_invoice .cmb2-id--sliced-partial-payments-minimum-extend-number input,
			#cmb2-metabox-_sliced_partial_payments_invoice .cmb2-id--sliced-partial-payments-minimum-extend-type select,
			#cmb2-metabox-_sliced_partial_payments_quote .cmb2-id--sliced-partial-payments-minimum-extend-number input,
			#cmb2-metabox-_sliced_partial_payments_quote .cmb2-id--sliced-partial-payments-minimum-extend-type select {
				line-height: 31px;
				height: 31px;
				margin-top: 1px;
			}
			#cmb2-metabox-_sliced_partial_payments_invoice .cmb2-id--sliced-partial-payments-minimum-extend-description,
			#cmb2-metabox-_sliced_partial_payments_quote .cmb2-id--sliced-partial-payments-minimum-extend-description {
				clear: both;
				margin-bottom: 25px;
			}
			#cmb2-metabox-_sliced_partial_payments_invoice .cmb2-id--sliced-partial-payments-other-enable .cmb2-metabox-description,
			#cmb2-metabox-_sliced_partial_payments_invoice .cmb2-id--sliced-partial-payments-other-extend .cmb2-metabox-description,
			#cmb2-metabox-_sliced_partial_payments_quote .cmb2-id--sliced-partial-payments-other-enable .cmb2-metabox-description,
			#cmb2-metabox-_sliced_partial_payments_quote .cmb2-id--sliced-partial-payments-other-extend .cmb2-metabox-description {
				max-width: 85%;
			}
			.sliced .subsubsub .partially-paid a {
				border-color: #60AD5D;
				color: #60AD5D;
			}
		</style>
		<?php
		#endregion admin_inline_css
	}
	
	
	/**
	 * Admin notices for various things...
	 *
	 * @since   1.0.0
	 */
	public function admin_notices() {
		
		// check just in case we're on < Sliced Invoices v3.5.0
		if ( class_exists( 'Sliced_Admin_Notices' ) ) {
		
			// Check core version
			if ( defined('SLICED_VERSION') && version_compare( SLICED_VERSION, '3.7.0', '<' ) ) {
				if ( ! Sliced_Admin_Notices::has_notice( 'partial_payments_core_update_needed' ) ) {
					$notice_args = array(
						'class'       => 'notice-error',
						'content'     => '<p>'
							. sprintf( __( 'Sliced Invoices Partial Payments has detected your Sliced Invoices plugin is out of date and not fully compatible with this version of Partial Payments. Please go to your %sPlugins page%s and update it now.', 'sliced-invoices-partial-payments' ), '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">', '</a>' )
							. '<br />' . sprintf( __( '<strong>You have:</strong> Sliced Invoices version %s.', 'sliced-invoices-partial-payments' ), SLICED_VERSION )
							. '<br />' . __( '<strong>Required:</strong> Sliced Invoices version 3.7.0 or newer', 'sliced-invoices-partial-payments' )
							. '</p>',
						'dismissable' => false,
					);
					Sliced_Admin_Notices::add_custom_notice( 'partial_payments_core_update_needed', $notice_args );
				}
			} else {
				Sliced_Admin_Notices::remove_notice( 'partial_payments_core_update_needed' );
			}
			
			// Check 2Checkout Gateway version
			if ( defined('SI_2CHECKOUT_VERSION') && version_compare( SI_2CHECKOUT_VERSION, '1.2.0', '<' ) ) {
				if ( ! Sliced_Admin_Notices::has_notice( 'partial_payments_2checkout_update_needed' ) ) {
					$notice_args = array(
						'class'       => 'notice-error',
						'content'     => '<p>'
							. sprintf( __( 'Sliced Invoices Partial Payments has detected your 2Checkout gateway is out of date and not fully compatible with this version of Partial Payments. Please go to your %sPlugins page%s and update it now.', 'sliced-invoices-partial-payments' ), '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">', '</a>' )
							. '<br />' . sprintf( __( '<strong>You have:</strong> Sliced Invoices 2Checkout version %s.', 'sliced-invoices-partial-payments' ), SI_2CHECKOUT_VERSION )
							. '<br />' . __( '<strong>Required:</strong> Sliced Invoices 2Checkout version 1.2.0 or newer', 'sliced-invoices-partial-payments' )
							. '</p>',
						'dismissable' => false,
					);
					Sliced_Admin_Notices::add_custom_notice( 'partial_payments_2checkout_update_needed', $notice_args );
				}
			} else {
				Sliced_Admin_Notices::remove_notice( 'partial_payments_2checkout_update_needed' );
			}
			
			// Check Braintree Gateway version
			if ( defined('SI_BRAINTREE_VERSION') && version_compare( SI_BRAINTREE_VERSION, '1.5.0', '<' ) ) {
				if ( ! Sliced_Admin_Notices::has_notice( 'partial_payments_braintree_update_needed' ) ) {
					$notice_args = array(
						'class'       => 'notice-error',
						'content'     => '<p>'
							. sprintf( __( 'Sliced Invoices Partial Payments has detected your Braintree gateway is out of date and not fully compatible with this version of Partial Payments. Please go to your %sPlugins page%s and update it now.', 'sliced-invoices-partial-payments' ), '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">', '</a>' )
							. '<br />' . sprintf( __( '<strong>You have:</strong> Sliced Invoices Braintree version %s.', 'sliced-invoices-partial-payments' ), SI_BRAINTREE_VERSION )
							. '<br />' . __( '<strong>Required:</strong> Sliced Invoices Braintree version 1.5.0 or newer', 'sliced-invoices-partial-payments' )
							. '</p>',
						'dismissable' => false,
					);
					Sliced_Admin_Notices::add_custom_notice( 'partial_payments_braintree_update_needed', $notice_args );
				}
			} else {
				Sliced_Admin_Notices::remove_notice( 'partial_payments_braintree_update_needed' );
			}
		
			// Check Stripe Gateway version
			if ( defined('SI_STRIPE_VERSION') && version_compare( SI_STRIPE_VERSION, '1.7.0', '<' ) ) {
				if ( ! Sliced_Admin_Notices::has_notice( 'partial_payments_stripe_update_needed' ) ) {
					$notice_args = array(
						'class'       => 'notice-error',
						'content'     => '<p>'
							. sprintf( __( 'Sliced Invoices Partial Payments has detected your Stripe gateway is out of date and not fully compatible with this version of Partial Payments. Please go to your %sPlugins page%s and update it now.', 'sliced-invoices-partial-payments' ), '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">', '</a>' )
							. '<br />' . sprintf( __( '<strong>You have:</strong> Sliced Invoices Stripe version %s.', 'sliced-invoices-partial-payments' ), SI_STRIPE_VERSION )
							. '<br />' . __( '<strong>Required:</strong> Sliced Invoices Stripe version 1.7.0 or newer', 'sliced-invoices-partial-payments' )
							. '</p>',
						'dismissable' => false,
					);
					Sliced_Admin_Notices::add_custom_notice( 'partial_payments_stripe_update_needed', $notice_args );
				}
			} else {
				Sliced_Admin_Notices::remove_notice( 'partial_payments_stripe_update_needed' );
			}
			
		}
		
	}
	
	
	/**
	 * Adds the meta box container (invoices).
	 *
	 * @since 1.0.0
	 */
	public function invoice_side_metabox() {
		
		$info = new_cmb2_box( array(
			'id'           => '_sliced_partial_payments_invoice',
			'title'        => __( 'Partial Payments Settings', 'sliced-invoices-partial-payments' ),
			'object_types' => array( 'sliced_invoice' ), // Post type
			'context'      => 'side',
			'priority'     => 'default'
		) );
		
		$is_subscription = false;
		if ( class_exists( 'Sliced_Subscriptions' ) ) {
			$ss = Sliced_Subscriptions::get_instance();
			$id = sliced_get_the_id();
			$is_subscription = $ss->is_subscription_invoice( $id );
		}
		if ( $is_subscription ) {
			$info->add_field( array(
				'name'  => '',
				'desc'  => '',
				'id'    => '_sliced_partial_payments_minimum',
				'type'  => 'title',
				'before_row' => '<p><span class="dashicons dashicons-info"></span> <em>' . __( 'Partial Payments are not allowed for Subscription Invoices', 'sliced-invoices-partial-payments' ) . '</em></p>',
			) );
			return;
		}
		
		$info->add_field( array(
			'name'  => __( 'Minimum Payment', 'sliced-invoices-partial-payments' ),
			'desc'  => '',
			'id'    => '_sliced_partial_payments_minimum',
			'type'  => 'title',
		) );
		$info->add_field( array(
			'name'       => '',
			'desc'       => __( 'Show "Minimum Payment" option', 'sliced-invoices-partial-payments' ),
			'id'         => '_sliced_partial_payments_minimum_enable',
			'type'       => 'checkbox',
		) );
		$info->add_field( array(
			'name'       => __( 'Minimum Payment Amount (required)', 'sliced-invoices-partial-payments' ),
			'desc'       => '',
			'id'         => '_sliced_partial_payments_minimum_amount',
			'type'       => 'text',
		) );
		$info->add_field( array(
			'name'       => '',
			'desc'       => '',
			'id'         => '_sliced_partial_payments_minimum_extend_number',
			'type'       => 'text',
			'attributes' => array(
				'maxlength'     => '6',
				'type'          => 'number',
				'step'          => '1',
			),
			'before_row' => '<label for="_sliced_partial_payments_minimum_extend_number">'.__( 'Extend Due Date (optional)', 'sliced-invoices-partial-payments' ).'</label>',
		) );
		$info->add_field( array(
			'name'       => '',
			'desc'       => '',
			'id'         => '_sliced_partial_payments_minimum_extend_type',
			'type'       => 'select',
			'options'    => array(
				'days'      => __( 'Day(s)', 'sliced-invoices-partial-payments' ),
				'months'    => __( 'Month(s)', 'sliced-invoices-partial-payments' ),
				'years'     => __( 'Year(s)', 'sliced-invoices-partial-payments' ),
			),
			'after_row'  => '<p class="cmb2-metabox-description cmb2-id--sliced-partial-payments-minimum-extend-description">'.__( 'If client pays Minimum Payment Amount, automatically extend the invoice due date.  To skip, leave blank.', 'sliced-invoices-partial-payments' ).'</p>',
		) );
		$info->add_field( array(
			'name'  => __( 'Other Payment Amount', 'sliced-invoices-partial-payments' ),
			'desc'  => '',
			'id'    => '_sliced_partial_payments_other',
			'type'  => 'title',
		) );
		$info->add_field( array(
			'name'       => '',
			'desc'       => __( 'Show "Other Payment Amount" option', 'sliced-invoices-partial-payments' ),
			'id'         => '_sliced_partial_payments_other_enable',
			'type'       => 'checkbox',
		) );
		$info->add_field( array(
			'name'       => __( 'Minimum Other Payment Amount (optional)', 'sliced-invoices-partial-payments' ),
			'desc'       => __( 'This is the smallest payment amount you will accept.  It can be less than the "Minimum Payment Amount".', 'sliced-invoices-partial-payments' ),
			'id'         => '_sliced_partial_payments_other_minimum_amount',
			'type'       => 'text',
		) );
		/*
		// something for a future version?  for now let's just have any payment amount >= minimum payment extend the due date (if set)
		$info->add_field( array(
			'name'       => __( 'Extend Due Date (optional)', 'sliced-invoices-partial-payments' ),
			'desc'       => __( 'If "Minimum Payment" is enabled, and client pays more than the "Minimum Payment Amount", extend the due date as set above.', 'sliced-invoices-partial-payments' ),
			'id'         => '_sliced_partial_payments_other_extend',
			'type'       => 'checkbox',
		) );
		*/
	}
	
	
	/**
	 * Adds the meta box container (quotes).
	 *
	 * @since 1.0.0
	 */
	public function quote_side_metabox() {
		
		$info = new_cmb2_box( array(
			'id'           => '_sliced_partial_payments_quote',
			'title'        => __( 'Partial Payments Settings', 'sliced-invoices-partial-payments' ),
			'object_types' => array( 'sliced_quote' ), // Post type
			'context'      => 'side',
			'priority'     => 'default',
			'closed'       => true,
		) );
		$info->add_field( array(
			'name'  => __( 'Minimum Payment', 'sliced-invoices-partial-payments' ),
			'desc'  => '',
			'id'    => '_sliced_partial_payments_minimum',
			'type'  => 'title',
			'before_row' => '<p><span class="dashicons dashicons-info"></span> <em>' . sprintf( __( 'These settings will not be shown on the %1$s, but they will automatically carry over to the future %2$s when this %1$s is accepted/converted.', 'sliced-invoices-partial-payments' ), sliced_get_quote_label(), sliced_get_invoice_label() ) . '</em></p>',
		) );
		$info->add_field( array(
			'name'       => '',
			'desc'       => __( 'Show "Minimum Payment" option', 'sliced-invoices-partial-payments' ),
			'id'         => '_sliced_partial_payments_minimum_enable',
			'type'       => 'checkbox',
		) );
		$info->add_field( array(
			'name'       => __( 'Minimum Payment Amount (required)', 'sliced-invoices-partial-payments' ),
			'desc'       => '',
			'id'         => '_sliced_partial_payments_minimum_amount',
			'type'       => 'text',
		) );
		$info->add_field( array(
			'name'       => '',
			'desc'       => '',
			'id'         => '_sliced_partial_payments_minimum_extend_number',
			'type'       => 'text',
			'attributes' => array(
				'maxlength'     => '6',
				'type'          => 'number',
				'step'          => '1',
			),
			'before_row' => '<label for="_sliced_partial_payments_minimum_extend_number">'.__( 'Extend Due Date (optional)', 'sliced-invoices-partial-payments' ).'</label>',
		) );
		$info->add_field( array(
			'name'       => '',
			'desc'       => '',
			'id'         => '_sliced_partial_payments_minimum_extend_type',
			'type'       => 'select',
			'options'    => array(
				'days'      => __( 'Day(s)', 'sliced-invoices-partial-payments' ),
				'months'    => __( 'Month(s)', 'sliced-invoices-partial-payments' ),
				'years'     => __( 'Year(s)', 'sliced-invoices-partial-payments' ),
			),
			'after_row'  => '<p class="cmb2-metabox-description cmb2-id--sliced-partial-payments-minimum-extend-description">'.__( 'If client pays Minimum Payment Amount, automatically extend the invoice due date.  To skip, leave blank.', 'sliced-invoices-partial-payments' ).'</p>',
		) );
		$info->add_field( array(
			'name'  => __( 'Other Payment Amount', 'sliced-invoices-partial-payments' ),
			'desc'  => '',
			'id'    => '_sliced_partial_payments_other',
			'type'  => 'title',
		) );
		$info->add_field( array(
			'name'       => '',
			'desc'       => __( 'Show "Other Payment Amount" option', 'sliced-invoices-partial-payments' ),
			'id'         => '_sliced_partial_payments_other_enable',
			'type'       => 'checkbox',
		) );
		$info->add_field( array(
			'name'       => __( 'Minimum Other Payment Amount (optional)', 'sliced-invoices-partial-payments' ),
			'desc'       => __( 'This is the smallest payment amount you will accept.  It can be less than the "Minimum Payment Amount".', 'sliced-invoices-partial-payments' ),
			'id'         => '_sliced_partial_payments_other_minimum_amount',
			'type'       => 'text',
		) );
	}
	
}
