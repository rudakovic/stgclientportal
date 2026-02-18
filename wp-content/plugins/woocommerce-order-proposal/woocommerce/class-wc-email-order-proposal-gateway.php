<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Email_Customer_Order_Proposal_Gateway' ) ) {

	/**
	* Customer Processing Order Email
	*
	* An email sent to the customer when a new order is received/paid for.
	*
	* @class 		WC_Email_Customer_Order_Proposal_Gateway
	* @version		2.0.0
	* @package		WooCommerce/Classes/Emails
	* @author 		WooThemes
	* @extends 		WC_Email
	*/
	class WC_Email_Customer_Order_Proposal_Gateway extends WC_Email {

		/**
		* Constructor
		*/
		function __construct() {

			$this->id 				= 'order_proposal_gateway';
			$this->title 			= __( 'Customer Proposal Payment Gateway', 'woocommerce-order-proposal' );
			$this->description		= __( 'This is an order notification sent to customers containing their order proposal when they used the Order Proposal Gateway.', 'woocommerce-order-proposal' );

			$this->customer_email   = true;

			$this->template_base    = wc_order_proposal()->get_plugin_path() . "/templates/";
			$this->template_html 	= 'emails/customer-order-proposal-gateway.php';
			$this->template_plain 	= 'emails/plain/customer-order-proposal-gateway.php';

			$this->placeholders   = array(
				'{site_title}'      => $this->get_blogname(),
				'{order_date}'      => '',
				'{order_number}'    => '',
				'{proposal_number}' => '',
			);

			// Triggers for this email
			#add_action( 'woocommerce_order_status_pending_to_order-proposal_notification', array( $this, 'trigger' ), 10, 2  );
			#add_action( 'woocommerce_order_status_draft_to_order-proposal_notification', array( $this, 'trigger' ), 10, 2  );

			// Call parent constructor
			parent::__construct();

			// Other settings
			// $this->recipient = $this->get_option( 'recipient' );

			// if ( ! $this->recipient )
			// 	$this->recipient = get_option( 'admin_email' );
		}

		/**
		 * Get email subject.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_subject() {
			return __( 'Your {site_title} order proposal from {order_date}', 'woocommerce-order-proposal' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Order Proposal', 'woocommerce-order-proposal' );
		}

		/**
		* Trigger function.
		*
		* @access public
		* @return void
		*/
		function trigger( $order_id, $order = false ) {

			if (method_exists($this, 'setup_locale')) {
				$this->setup_locale();
			}

			if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( is_a( $order, 'WC_Order' ) ) {
				$this->object = $order;
				
				$this->recipient = $this->object->get_billing_email();

				$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
				$this->placeholders['{order_number}'] = $this->object->get_order_number();

				$settings = get_option( 'wpo_wcpdf_documents_settings_proposal' );
				if( isset( $settings['attach_to_email_ids'] ) && in_array( $this->id, array_keys( $settings['attach_to_email_ids'] ) ) && function_exists( 'wcpdf_get_document' ) ) {
					$document = wcpdf_get_document( 'proposal', $this->object, true );
					if( ! empty( $document ) ) {
						$this->placeholders['{proposal_number}'] = $document->get_proposal_number();
					}
				}
			}

			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}

			$header = $this->get_headers();

			$bcc = $this->get_option( 'bcc' );
			
			if ( !empty($bcc) ) {
				$header .= "BCC: " .  $bcc . "\r\n";
			}

			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $header, $this->get_attachments() );

			if (method_exists($this, 'restore_locale')) {
				$this->restore_locale();
			}
		}

		/**
		* get_content_html function.
		*
		* @access public
		* @return string
		*/
		function get_content_html() {

			ob_start();

			wc_get_template(
				$this->template_html,
				array(
					'order' 		=> $this->object,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => false,
					'email'         => $this,
				),
				"",
				$this->template_base
			);

			return ob_get_clean();
		}

		/**
		* get_content_plain function.
		*
		* @access public
		* @return string
		*/
		function get_content_plain() {

			ob_start();

			wc_get_template(
				$this->template_plain,
				array(
					'order'         => $this->object,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => true,
					'email'         => $this,
				),
				"",
				$this->template_base
			);

			return ob_get_clean();
		}

		/**
		* Initialise Settings Form Fields
		*
		* @access public
		* @return void
		*/
		function init_form_fields() {
			
			$this->form_fields = array(
				'enabled' => array(
					'title'           => __( 'Enable/Disable', 'woocommerce-order-proposal' ),
					'type'            => 'checkbox',
					'label'           => __( 'Enable this email notification', 'woocommerce-order-proposal' ),
					'default'         => 'yes'
				),
				'bcc' => array(
					'title'           => __( 'BCC Recipient(s)', 'woocommerce-order-proposal' ),
					'type'      => 'text',
					'description'     => __( 'Enter BCC recipients (comma separated) for this email.', 'woocommerce-order-proposal' ),
					'placeholder'     => '',
					'default'         => ''
				),
				'subject' => array(
					'title'           => __( 'Email Subject', 'woocommerce-order-proposal' ),
					'type'            => 'text',
					'desc_tip'        => true,
					/* translators: %s: list of placeholders */
					'description'     => sprintf( __( 'Available placeholders: %s', 'woocommerce' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
					'placeholder'     => $this->get_default_subject(),
					'default'         => ''
				),
				'heading' => array(
					'title'           => __( 'Email Heading', 'woocommerce-order-proposal' ),
					'type'            => 'text',
					/* translators: %s: list of placeholders */
					'description'     => sprintf( __( 'Available placeholders: %s', 'woocommerce' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
					'placeholder'     => $this->get_default_heading(),
					'default'         => ''
				),
				'email_type' => array(
					'title'           => __( 'Email type', 'woocommerce-order-proposal' ),
					'type'            => 'select',
					'description'     => __( 'Choose which format of email to send.', 'woocommerce-order-proposal' ),
					'default'         => 'html',
					'class'           => 'email_type wc-enhanced-select',
					'options'         => $this->get_email_type_options()
				)
			);
		}
	}
}

return new WC_Email_Customer_Order_Proposal_Gateway();