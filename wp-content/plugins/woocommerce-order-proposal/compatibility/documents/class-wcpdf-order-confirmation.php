<?php

use WPO\WC\PDF_Invoices\Documents as Documents;
use WPO\WC\PDF_Invoices\Updraft_Semaphore_3_0 as Semaphore;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( 'PDF_Order_Confirmation' ) ) :

class PDF_Order_Confirmation extends Documents\Order_Document_Methods {

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
		$this->type  = 'order-confirmation';
		$this->title = __( 'Order Confirmation', 'woocommerce-order-proposal' );
		$this->icon  = wc_order_proposal()->get_plugin_url() . "/assets/images/confirmation.svg";

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

	public function get_confirmation_number() {
		if ( $confirmation_number = $this->get_number('order-confirmation') ) {
			return $confirmation_number->get_formatted();
		} else {
			return $this->order_number();
		}
	}

	/**
	 * Initialise settings
	 */
	public function init_settings() {
		// Register settings.
		$page = $option_group = $option_name = 'wpo_wcpdf_documents_settings_order-confirmation';

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
				'id'			=> 'display_number',
				'title'			=> __( 'Display order confirmation number', 'woocommerce-order-proposal' ),
				'callback'		=> 'select',
				'section'		=> $this->type,
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'display_number',
					'options' => array(
						''                          => __( 'No', 'woocommerce-order-proposal' ),
						'order_confirmation_number' => __( 'Order Confirmation Number', 'woocommerce-order-proposal' ),
						'order_number'              => __( 'Order Number', 'woocommerce-order-proposal' ),
					),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'next_confirmation_number',
				'title'			=> __( 'Next confirmation number', 'woocommerce-order-proposal' ),
				'callback'		=> 'next_number_edit',
				'section'		=> $this->type,
				'args'			=> array(
					'store'			=> 'order_confirmation_number',
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
							'description'	=> __( 'to use the order confirmation year and/or month, use [order_confirmation_year] or [order_confirmation_month] respectively' , 'woocommerce-order-proposal' ),
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
				'type'                  => 'setting',
				'id'                    => 'my_account_buttons',
				'title'                 => __( 'Allow My Account download', 'woocommerce-order-proposal' ),
				'callback'              => 'select',
				'section'               => $this->type,
				'args'                  => array(
					'option_name'   => $option_name,
					'id'                    => 'my_account_buttons',
					'options'               => array(
						'custom'        => __( 'Only for specific order statuses (define below)' , 'woocommerce-pdf-invoices-packing-slips' ),
						'always'        => __( 'Always' , 'woocommerce-pdf-invoices-packing-slips' ),
						'never'         => __( 'Never' , 'woocommerce-pdf-invoices-packing-slips' ),
					),  
					'custom'                => array(
						'type'          => 'multiple_checkboxes',
						'args'          => array(
							'option_name'   => $option_name,
							'id'                    => 'my_account_restrict',
							'fields'                => $this->get_wc_order_status_list(),
						),  
					),  
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
			),

		);

		// allow plugins to alter settings fields
		$settings_fields = apply_filters( 'wpo_wcpdf_settings_fields_documents_order_confirmation', $settings_fields, $page, $option_group, $option_name );
		WPO_WCPDF()->settings->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
	}

}

endif; // class_exists

return new PDF_Order_Confirmation();
