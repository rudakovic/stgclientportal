<?php

class WC_Order_Proposal_WC_PDF_Invoices_Hooks {

	/**
	 * Constructor
	 */
	public function __construct() {

		add_filter( 'wpo_wcpdf_listing_actions', array( $this, 'order_proposal_wpo_wcpdf_listing_actions' ), 10, 2 );
		add_filter( 'wpo_wcpdf_meta_box_actions', array( $this, 'order_proposal_wpo_wcpdf_meta_box_actions' ), 10, 2 );
		add_filter( 'wpo_wcpdf_myaccount_actions', array( $this, 'order_proposal_my_account_pdf' ), 20, 2 );
		add_filter( 'wpo_wcpdf_template_file', array( $this, 'order_proposal_wcpdf_template_file' ), 10, 2 );

		add_filter( 'wpo_wcpdf_document_classes', array( $this, 'wcpdf_document_classes' ), 20, 1 );

		add_action( 'wc_order_proposal_install', array( $this, 'order_proposal_install' ), 10 );
		// reload text domain for language
		add_action( 'wpo_wcpdf_reload_text_domains', array( $this, 'reload_text_domain' ), 10, 1 );
		// custom footer
		add_filter( "wpo_wcpdf_footer_settings_text", array( $this, 'custom_footer' ), 10, 2);

		// custom invoice numbers
		add_action( 'wpo_wcpdf_meta_box_end', array( $this, 'wpo_wcpdf_meta_box_end_proposal' ), 10, 2 );
		add_action( 'wpo_wcpdf_meta_box_end', array( $this, 'wpo_wcpdf_meta_box_end_confirmation' ), 11, 2 );
		add_action( 'wpo_wcpdf_on_save_invoice_order_data', array( $this, 'wpo_wcpdf_on_save_invoice_order_data_proposal' ), 10, 3 );
	}

	/**
	 * Add proposal metabox fields (number, date, notes)
	 * WP action: wpo_wcpdf_meta_box_end
	 */
	public function wpo_wcpdf_meta_box_end_proposal( $order, $class = null ) {
		// old version
		if ( is_null( $class ) ) {
			return;
		}

		$proposal = wcpdf_get_document( 'proposal', $order );

		if ( $proposal ) {
			// data
			if ( $proposal->exists() && ! empty( $proposal->settings['display_number'] ) ) {
				$data = array(
					'number' => array(
						'label' => __( 'Proposal Number:', 'woocommerce-order-proposal' ),
					),
					'date'   => array(
						'label' => __( 'Proposal Date:', 'woocommerce-order-proposal' ),
					),
					'notes'  => array(
						'label' => __( 'Notes (printed in the proposal):', 'woocommerce-order-proposal' ),
					),
				);
				// no number, do allow date & notes
			} else {
				$data = array(
					'date'  => array(
						'label' => __( 'Proposal Date:', 'woocommerce-order-proposal' ),
					),
					'notes' => array(
						'label' => __( 'Notes (printed in the proposal):', 'woocommerce-order-proposal' ),
					),
				);
			}

			$class->output_number_date_edit_fields( $proposal, $data );
		}
	}

	/**
	 * Add custom confirmation number
	 * WP action: wpo_wcpdf_meta_box_end
	 */
	public function wpo_wcpdf_meta_box_end_confirmation( $order, $class = null ) {
		// old version
		if ( is_null( $class ) ) {
			return;
		}

		$confirmation = wcpdf_get_document( 'order-confirmation', $order );

		if ( $confirmation && $confirmation->exists() && ! empty( $confirmation->settings['display_number'] ) ) {
			$data = array(
				'number' => array(
					'label' => __( 'Order Confirmation Number:', 'woocommerce-order-proposal' ),
				),
				'date'   => array(
					'label' => __( 'Order Confirmation Date:', 'woocommerce-order-proposal' ),
				),
			);

			$class->output_number_date_edit_fields( $confirmation, $data );
		}
	}
	
	/**
	 * Relaod Text Domain
	 * WP action: wpo_wcpdf_on_save_invoice_order_data
	 */
	public function wpo_wcpdf_on_save_invoice_order_data_proposal( $form_data, $order, $class = null ) {

		if ( is_null( $class ) ) { // old version
			return;
		}

		if ( $proposal = wcpdf_get_document( 'proposal', $order ) ) {
			$document_data = $class->process_order_document_form_data( $form_data, $proposal->slug );
			$proposal->set_data( $document_data, $order );
			$proposal->save();
		}
	}

	/**
	 * Relaod Text Domain
	 * WP action: wpo_wcpdf_reload_text_domains
	 */
	public function reload_text_domain($locale) {

		unload_textdomain( 'woocommerce-order-proposal' );
		wc_order_proposal()->load_plugin_textdomain();
	}

	/**
	 * Installs order proposal PDF default options
	 * WP action: wc_order_proposal_install
	 */
	public function order_proposal_install() {
		// set default settings
		$settings_defaults = array(
			'wpo_wcpdf_documents_settings_proposal' => array(
				'enabled' => 1,
				'attach_to_email_ids' => array('customer_order_proposal' => 1),
			),
			'wpo_wcpdf_documents_settings_order-confirmation' => array(
				'enabled' => 1,
			)
		);
		
		foreach ($settings_defaults as $option => $defaults) {
			update_option( $option, $defaults );
		}
	}

	public function wcpdf_document_classes($documents) {

		$documents['PDF_Proposal'] = include( 'documents/class-wcpdf-proposal.php' );
		$documents['PDF_Order_Confirmation'] = include( 'documents/class-wcpdf-order-confirmation.php' );

		return $documents;

	}

	public function wcpdf_get_template_type() {
		$pdf_template_type = 'simple'; // default

		if ( function_exists( 'WPO_WCPDF' ) && function_exists( 'WPO_WCPDF_Templates' ) ) {
			$premium_templates = array(
				'business'       => 'Business',
				'modern'         => 'Modern',
				'simple_premium' => 'Simple Premium',
			);
			
			$template_name = basename( WPO_WCPDF()->settings->get_template_path() );
	
			if ( in_array( $template_name, $premium_templates ) ) {
				$pdf_template_type = array_search( $template_name, $premium_templates );
			}
		}

		return $pdf_template_type;
	}

	public function order_proposal_wcpdf_template_file( $template, $template_type ) {
		if ( ( $template_type == 'order-confirmation' ) || ( $template_type == 'proposal' ) ) {
			if ( ! file_exists( $template ) ) {
				$pdf_template_type = $this->wcpdf_get_template_type();
				$local_template    = wc_order_proposal()->get_plugin_path() . '/templates/pdf/' . $pdf_template_type . '/' . $template_type . '.php';

				if ( file_exists( $local_template ) ) {
					$template = $local_template;
				}
			}
		}

		return $template;
	}
	
	/**
	 * Add the order proposal pdf file
	 * WP action: wpo_wcpdf_listing_actions
	 */
	public function order_proposal_wpo_wcpdf_listing_actions( $listing_actions, $order ) { 

		$order_id = $order->get_id();

		if ( !wc_order_proposal_order_has_proposal($order_id) && isset($listing_actions['proposal']) ) {
			unset($listing_actions['proposal']);
		}

		return $listing_actions;
	}

	/**
	 * Add the order proposal pdf file to the order meta box
	 * WP action: wpo_wcpdf_meta_box_actions
	 */
	public function order_proposal_wpo_wcpdf_meta_box_actions( $meta_actions, $order_id ) {
		$proposal = wcpdf_get_document( 'proposal', null );

		if ( ! wc_order_proposal_order_has_proposal( $order_id ) && empty( $proposal->settings['always_show_proposal'] ) ) {
			if ( isset( $meta_actions['proposal'] ) ) {
				unset( $meta_actions['proposal'] );
			}
		}

		return $meta_actions;
	}

	/**
	 * Add proposal to my account page
	 * WP action: wpo_wcpdf_myaccount_actions
	 */
	public function order_proposal_my_account_pdf($actions, $order) {
		foreach (array('proposal', 'order-confirmation') as $document_name ) {
			$document = wcpdf_get_document( $document_name, $order );
			if ( $document && $document->is_enabled() ) { 
				$pdf_url = wp_nonce_url( admin_url( 'admin-ajax.php?action=generate_wpo_wcpdf&document_type=' .$document_name . '&order_ids=' . $order->get_id() . '&my-account'), 'generate_wpo_wcpdf' );

				$allowed = false;
				
				// check my account button settings
				$button_setting = $document->get_setting('my_account_buttons', 'available');
				switch ($button_setting) {
					case 'always':
						$allowed = true;
						break;
					case 'never':
						$allowed = false;
						break;
					case 'custom':
						$allowed_statuses = $button_setting = $document->get_setting('my_account_restrict', array());
						if ( !empty( $allowed_statuses ) && in_array( $order->get_status(), array_keys( $allowed_statuses ) ) ) { 
							$allowed = true;
						} else {
							$allowed = false;
						}   
						break;
				}   

				// Check if invoice has been created already or if status allows download (filter your own array of allowed statuses)
				if ( $allowed || in_array($order->get_status(), apply_filters( 'wpo_wcpdf_myaccount_allowed_order_statuses', array() ) ) ) { 
					$actions[$document_name] = array(
						'url'  => $pdf_url,
						'name' => $document->get_title(),
					);  
				}   
			}  
		}

		return apply_filters( 'wpo_wcpdf_myaccount_proposal_actions', $actions, $order );
	}

	/**
	 * Maybe add custom footer
	 * WP filter: wpo_wcpdf_footer_settings_text
	 */
	public function custom_footer($text, $document) {

		if (in_array($document->slug, ['proposal', 'order_confirmation']) ) {
			if ( !empty( $document->settings['custom_footer']['default'] ) ) {
				return wptexturize( trim( $document->settings['custom_footer']['default'] ) );
			}
		}

		return $text;
	}

}

new WC_Order_Proposal_WC_PDF_Invoices_Hooks();