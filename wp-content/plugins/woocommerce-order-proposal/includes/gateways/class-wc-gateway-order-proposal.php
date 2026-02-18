<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Order Proposal Payment Gateway.
 *
 * Provides a Order Proposal Payment Gateway. Based on code by Mike Pepper.
 *
 * @class       WC_Gateway_Order_Proposal
 * @extends     WC_Payment_Gateway
 * @version     2.1.0
 * @package     WooCommerce/Classes/Payment
 * @author      WooThemes
 */

class WC_Gateway_Order_Proposal extends WC_Payment_Gateway {

	/**
	 * Array of locales
	 *
	 * @var array
	 */
	public $locale;

	/**
	 * Gateway instructions that will be added to the thank you page and emails.
	 *
	 * @var string
	 */
	public $instructions;

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {

		$this->id                 = 'orderproposal';
		$this->icon               = apply_filters( 'woocommerce_orderproposal_icon', '' );
		$this->has_fields         = false;
		$this->method_title       = __( 'Order Proposal', 'woocommerce-order-proposal' );
		$this->method_description = __( 'Allows payments by Order Proposal', 'woocommerce-order-proposal' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option( 'instructions' );

		// Order Proposal account fields shown on the thanks page and in emails
		// Actions
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Order Proposal', 'woocommerce-order-proposal' ),
				'default' => 'no',
			),
			'title' => array(
				'title'       => __( 'Title', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => __( 'Order Proposal', 'woocommerce-order-proposal' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see during checkout.', 'woocommerce' ),
				'default'     => __( 'This will create an Order Proposal.', 'woocommerce-order-proposal' ),
				'desc_tip'    => true,
			),
			'instructions' => array(
				'title'       => __( 'Instructions', 'woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Instructions that will be added to the thank you page and emails.', 'woocommerce-order-proposal' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'emailproposal' => array( 
				'title'	   => __( 'Order Proposal Email', 'woocommerce-order-proposal' ), 
				'description'     => __( 'Send Order Proposal Email instead of Order Email', 'woocommerce-order-proposal' ),
				'desc_tip'    => true,
				'type'     => 'checkbox',
				'default'  => 'no',
			), 
		);

	}

	/**
	 * Output for the order received page.
	 *
	 * @param int $order_id
	 */
	public function thankyou_page( $order_id ) {
		if ( $this->instructions ) {
			echo wpautop( wptexturize( wp_kses_post( $this->instructions ) ) );
		}
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id
	 * @return array
	 */
	public function process_payment( $order_id ) {

		// Disable order proposal mail
		$emails = WC_Emails::instance()->emails;

		do_action('woocommerce_order_proposal_gateway_start', $this->get_option( 'emailproposal' ));

		add_action( 'woocommerce_order_status_pending_to_order-proposalreq_notification', array( $emails['WC_Email_Admin_Order_Proposal_Gateway'], 'trigger' ), 10, 2 );

		if ($this->get_option( 'emailproposal' ) != 'no') {
			// Triggers Order Proposal Email
			add_action( 'woocommerce_order_status_pending_to_order-proposalreq_notification', array( $emails['WC_Email_Order_Proposal_Gateway'], 'trigger' ), 10, 2 );
		} else {
			add_action( 'woocommerce_order_status_pending_to_order-proposalreq_notification', array( $emails['WC_Email_Customer_Processing_Order'], 'trigger' ), 10, 2 );
		}

		$order = wc_get_order( $order_id );

		// Set order status to order-proposal
		$status = apply_filters( 'wc-order-proposal-gateway-status', 'wc-order-proposalreq', $order_id, $this );
		$status_text = apply_filters( 'wc-order-proposal-gateway-status-text', __( 'Order Proposal Requested', 'woocommerce-order-proposal' ), $order_id, $this );
		$order->update_status( $status, $status_text );

		// Set Order Proposal Time first so we get the default time
		$time = wc_order_proposal_get_date($order_id);
		$order->update_meta_data( WC_Order_Proposal::ORDER_PROPOSAL_TIME, $time );

		// Mark order as order-proposal
		$order->update_meta_data( WC_Order_Proposal::ORDER_PROPOSAL_USED, true );

		// Reduce stock levels
		if ( ! wc_order_proposal_no_reduce_stock() ) {
			wc_reduce_stock_levels( $order_id );
		}

		// Unset payment-method so user can pick one if he wants to pay
		$order->set_payment_method('');
		// keep title to display
		$order->set_payment_method_title( $this->get_title() );
		$order->save_meta_data();
		$order->save();

		// Remove cart
		WC()->cart->empty_cart();

		// Return thankyou redirect
		return array(
			'result'    => 'success',
			'redirect'  => $this->get_return_url( $order ),
		);
	}

	/**
	 * Check If The Gateway Is Available For Use.
	 *
	 * @return bool
	 */
	public function is_available() {
		# Do not show on order pay page in my account
		if ( is_wc_endpoint_url( 'order-pay' ) ) {
			return false;
		}

		return parent::is_available();
	}
}
