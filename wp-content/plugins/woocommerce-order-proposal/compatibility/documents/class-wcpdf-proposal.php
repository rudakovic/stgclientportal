<?php

use WPO\WC\PDF_Invoices\Documents as Documents;
use WPO\WC\PDF_Invoices\Updraft_Semaphore_3_0 as Semaphore;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( 'PDF_Proposal' ) ) :

/**
 * Invoice Document
 * 
 * @class       \WPO\WC\PDF_Invoices\Documents\Invoice
 * @version     2.0
 * @category    Class
 * @author      Ewout Fernhout
 */

class PDF_Proposal extends Documents\Order_Document_Methods {

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var string
	 */
	public $icon;

	/**
	 * @var string
	 */
	public $lock_name;

	/**
	 * @var array
	 */
	public $lock_context;

	/**
	 * @var int
	 */
	public $lock_time;

	/**
	 * @var int
	 */
	public $lock_retries;

	/**
	 * @var array
	 */
	public $lock_loggers;

	/**
	 * Init/load the order object.
	 *
	 * @param  int|object|WC_Order $order Order to init.
	 */
	public function __construct( $order = 0 ) {
		// set properties
		$this->type  = 'proposal';
		$this->title = __( 'Proposal', 'woocommerce-order-proposal' );
		$this->icon  = wc_order_proposal()->get_plugin_url() . "/assets/images/proposal.svg";

		// semaphore
		$this->lock_name    = "wpo_wcpdf_{$this->slug}_semaphore_lock";
		$this->lock_context = array( 'source' => "wpo-wcpdf-semaphore" );
		$this->lock_time    = apply_filters( "wpo_wcpdf_{$this->type}_semaphore_lock_time", 60 );
		$this->lock_retries = apply_filters( "wpo_wcpdf_{$this->type}_semaphore_lock_retries", 0 );
		$this->lock_loggers = apply_filters( "wpo_wcpdf_{$this->type}_semaphore_lock_loggers", isset( WPO_WCPDF()->settings->debug_settings['semaphore_logs'] ) ? array( wc_get_logger() ) : array() );

		// Call parent constructor
		parent::__construct( $order );
	}

	public function init() {
		$this->set_date( current_time( 'timestamp', true ) );
		$this->initiate_number();
	}

	public function get_end_date_title() {
		return apply_filters( "wpo_wcpdf_{$this->slug}_end_date_title", __( 'End Date:', 'woocommerce-order-proposal' ), $this );
	}
	
	public function end_date_title() {
		echo $this->get_end_date_title();
	}
	
	public function get_proposal_number() {
		if ( $proposal_number = $this->get_number( 'proposal' ) ) {
			return $proposal_number->get_formatted();
		} else {
			return $this->order_number();
		}
	}

	public function init_settings() {
		// Register settings.
		$page = $option_group = $option_name = 'wpo_wcpdf_documents_settings_proposal';

		$settings_fields = array(
			array(
				'type'			=> 'section',
				'id'			=> $this->type,
				'title'			=> '',
				'callback'		=> 'section',
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'enabled',
				'title'			=> __( 'Enable', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> $this->type,
				'default'		=> '1',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'enabled',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'attach_to_email_ids',
				'title'			=> __( 'Attach to:', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'multiple_checkboxes',
				'section'		=> $this->type,
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'attach_to_email_ids',
					'fields' 		=> $this->get_wc_emails(),
					'description'	=> !is_writable( WPO_WCPDF()->main->get_tmp_path( 'attachments' ) ) ? '<span class="wpo-warning">' . sprintf( __( 'It looks like the temp folder (<code>%s</code>) is not writable, check the permissions for this folder! Without having write access to this folder, the plugin will not be able to email invoices.', 'woocommerce-pdf-invoices-packing-slips' ), WPO_WCPDF()->main->get_tmp_path( 'attachments' ) ).'</span>':'',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_shipping_address',
				'title'			=> __( 'Display shipping address', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> $this->type,
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'display_shipping_address',
					'description'		=> __( 'Display shipping address (in addition to the default billing address) if different from billing address', 'woocommerce-order-proposal' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_email',
				'title'			=> __( 'Display email address', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> $this->type,
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'display_email',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_phone',
				'title'			=> __( 'Display phone number', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> $this->type,
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'display_phone',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_proposal_enddate',
				'title'			=> __( 'Display proposal end date', 'woocommerce-order-proposal' ),
				'callback'		=> 'checkbox',
				'section'		=> $this->type,
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'display_proposal_enddate',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'always_show_proposal',
				'title'			=> __( 'Always show Proposal PDF', 'woocommerce-order-proposal' ),
				'callback'		=> 'checkbox',
				'section'		=> $this->type,
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'always_show_proposal',
					'description'		=> __( 'Proposal PDF is usually only shown when an Order was a proposal before. Enable this to show the PDF even on non Proposal orders.', 'woocommerce-order-proposal' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_number',
				'title'			=> __( 'Display proposal number', 'woocommerce-order-proposal' ),
				'callback'		=> 'select',
				'section'		=> $this->type,
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'display_number',
					'options' 		=> array(
						''					=> __( 'No' , 'woocommerce-order-proposal' ),
						'proposal_number'	=> __( 'Proposal Number' , 'woocommerce-order-proposal' ),
						'order_number'		=> __( 'Order Number' , 'woocommerce-order-proposal' ),
					),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'next_proposal_number',
				'title'			=> __( 'Next proposal number', 'woocommerce-order-proposal' ),
				'callback'		=> 'next_number_edit',
				'section'		=> $this->type,
				'args'			=> array(
					'store'			=> 'proposal_number',
					'size'			=> '10',
					'description'	=> __( 'This is the number that will be used for the next document. By default, numbering starts from 1 and increases for every new document. Note that if you override this and set it lower than the current/highest number, this could create duplicate numbers!', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'number_format',
				'title'			=> __( 'Number format', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'multiple_text_input',
				'section'		=> $this->type,
				'args'			=> array(
					'option_name'			=> $option_name,
					'id'					=> 'number_format',
					'fields'				=> array(
						'prefix'			=> array(
							'placeholder'	=> __( 'Prefix' , 'woocommerce-pdf-invoices-packing-slips' ),
							'size'			=> 20,
							'description'	=> __( 'to use the proposal year and/or month, use [proposal_year] or [proposal_month] respectively' , 'woocommerce-order-proposal' ),
						),
						'suffix'			=> array(
							'placeholder'	=> __( 'Suffix' , 'woocommerce-pdf-invoices-packing-slips' ),
							'size'			=> 20,
							'description'	=> '',
						),
						'padding'			=> array(
							'placeholder'	=> __( 'Padding' , 'woocommerce-pdf-invoices-packing-slips' ),
							'size'			=> 20,
							'type'			=> 'number',
							'description'	=> __( 'enter the number of digits here - enter "6" to display 42 as 000042' , 'woocommerce-pdf-invoices-packing-slips' ),
						),
					),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'my_account_buttons',
				'title'			=> __( 'Allow My Account download', 'woocommerce-order-proposal' ),
				'callback'		=> 'select',
				'section'		=> $this->type,
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'my_account_buttons',
					'options' 		=> array(
						'custom'	=> __( 'Only for specific order statuses (define below)' , 'woocommerce-pdf-invoices-packing-slips' ),
						'always'	=> __( 'Always' , 'woocommerce-pdf-invoices-packing-slips' ),
						'never'		=> __( 'Never' , 'woocommerce-pdf-invoices-packing-slips' ),
					),
					'custom'		=> array(
						'type'		=> 'multiple_checkboxes',
						'args'		=> array(
							'option_name'	=> $option_name,
							'id'			=> 'my_account_restrict',
							'fields'		=> $this->get_wc_order_status_list(),
						),
					),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'paymentgateways',
				'title'			=> __( 'Show payment gateways in PDF as payment options if prepayment is enabled', 'woocommerce-order-proposal' ),
				'callback'		=> 'checkbox',
				'section'		=> $this->type,
				'default'		=> '1',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'paymentgateways',
				)
			),
			array(
				'type'		=> 'setting',
				'id'		=> 'custom_footer',
				'title'		=> __( 'Footer Override: terms & conditions, policies, etc.', 'woocommerce-order-proposal' ),
				'callback'	=> 'textarea',
				'section'	=> $this->type,
				'args'		=> array(
					'option_name'	=> $option_name,
					'id'			=> 'custom_footer',
					'width'			=> '72',
					'height'		=> '4',
					'translatable'	=> true,
				)
			)
		);

		// allow plugins to alter settings fields
		$settings_fields = apply_filters( 'wpo_wcpdf_settings_fields_documents_proposal', $settings_fields, $page, $option_group, $option_name );
		WPO_WCPDF()->settings->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
	}

	/** 
	 * Method to return possible payment gateways
	 */
	public function get_payment_gateway_titles(): array {
		$titles = array();
		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

		foreach ( $available_gateways as $gateway ) {
			if ( 'orderproposal' === $gateway->id ) {
				continue;
			}

			if ( $gateway->method_title ) {
				$titles[] = $gateway->method_title;
			}
		}

		return apply_filters( 'wpo_wcop_payment_gateway_titles', $titles );
	}

	public function get_proposal_payment_options_text() {
		return apply_filters( 'wpo_wcop_payment_options_text', __( 'You have the following payment options:', 'woocommerce-order-proposal' ), $this );
	}

}

endif; // class_exists

return new PDF_Proposal();
