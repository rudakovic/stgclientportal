<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Sliced_Stripe
 *
 * @package Sliced_Stripe
 */
class Sliced_Stripe {
	
	/** @var object Instance of this class */
	protected static $instance = null;
	
	/** @var string Unique prefix for this gateway */
	protected $prefix = 'sliced-stripe';
	
	/** @var string Unique slug for this gateway */
	protected $slug   = 'sliced_stripe';
	
	/** @var array  Settings for this gateway */
	public $settings = array();
	
	
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
	 */
	public function __construct() {
		
		load_plugin_textdomain(
			'sliced-invoices-stripe',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
		
		$this->settings = $this->get_settings();
		
		if ( ! $this->validate_settings() ) {
			return;
		}
		
		Sliced_Invoices_Stripe_Admin::get_instance();
		
		add_action( 'script_loader_tag', array( $this, 'add_defer_attribute' ) );
		add_action( 'sliced_payment_head', array( $this, 'enqueue_payment_scripts') );
		add_action( 'sliced_do_payment', array( $this, 'payment_form') );
		add_action( 'sliced_do_payment', array( $this, 'payment_return'), 10 );
		add_action( 'wp_ajax_sliced_stripe_charge_payment', array( $this, 'ajax_charge_payment' ) );
		add_action( 'wp_ajax_nopriv_sliced_stripe_charge_payment', array( $this, 'ajax_charge_payment' ) );
		add_action( 'wp_ajax_sliced_stripe_confirm_payment', array( $this, 'ajax_confirm_payment' ) );
		add_action( 'wp_ajax_nopriv_sliced_stripe_confirm_payment', array( $this, 'ajax_confirm_payment' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'sliced_get_accepted_payment_methods', array( $this, 'register_payment_method') );
		add_filter( 'sliced_get_invoice_payment_methods', array( $this, 'check_payment_method' ) );
		
	}
	
	
	/******************************************************************************
	 * Boilerplate, helpers, and miscellany functions                             *
	 ******************************************************************************/
	
	/**
	 * Fix for cloudflare caching scripts, etc.
	 *
	 * @since   1.3.0
	 */
	public function add_defer_attribute( $tag ) {
		
		if ( is_page( $this->settings['payment_page'] ) ) {
		
			// array of scripts not to defer
			$scripts_to_defer = array( 'stripe', 'jquery' );
			$attributes_to_remove = array( "async='async'", 'async="async"', "defer='defer'", 'defer="defer"' );
			
			foreach ( $scripts_to_defer as $defer_script ) {
				if ( strpos( $tag, $defer_script ) !== false ) {
					if ( strpos( $tag, 'data-cfasync="false"' ) === false ) {
						$tag = str_replace( ' src', ' data-cfasync="false" src', $tag );
					}
					$tag = str_replace( $attributes_to_remove, '', $tag );
					return $tag;
				}
			}
			
		}
		
		return $tag;
	}
	
	/**
	 * Ajax handler required by Stripe Payment Request Button.
	 *
	 * Stripe-specific function.
	 *
	 * @since   2.0.0
	 */
	public function ajax_charge_payment() {
		
		$this->load_vendor_library();
		
		$json_str = file_get_contents('php://input');
		$json_obj = json_decode($json_str);
		
		$id            = intval( $json_obj->sliced_payment_invoice_id );
		$currency      = sliced_get_invoice_currency( $id );
		$currency_code = $currency && $currency !== 'default' ? $currency : $this->settings['currency'];
		$currency_exp  = Sliced_Shared::currency_exponent( $currency_code );
		
		$charge = null;
		try {
			if ( isset( $json_obj->token_id ) ) {
				
				// get customer ID if they already exist in stripe
				$customer_id = $this->retrieve_customer( $id );
				
				if ( $customer_id === false ) {
					// customer doesn't exist... create it
					$customer_id = $this->create_customer( $id, $json_obj->token_id );
				}
				
				// charge
				$charge = \Stripe\Charge::create(
					apply_filters( 'sliced_stripe_charge_create_args',
						array(
							'amount'      => intval( $json_obj->sliced_payment_amount ),
							'currency'    => strtolower( $currency_code ),
							'customer'    => $customer_id,
							'metadata'    => array(
								'sliced_invoice_id' => $id,
							),
							'source'      => $json_obj->token_id,
							'description' => sprintf( __( 'Payment for %s', 'sliced-invoices-stripe' ), sliced_get_invoice_prefix( $id ) . sliced_get_invoice_number( $id ) . sliced_get_invoice_suffix( $id ) ),
						)
					)
				);
			}
			if ( $charge->status === 'succeeded' ) {
				$this->charge( $charge, $id );
				wp_send_json( array(
					'success' => true,
					'payment_id' => isset( $charge['id'] ) ? $charge['id'] : '',
				) );
			} else {
				wp_send_json( array( 'error' => 'Payment failed' ), 500 );
			}
		} catch ( \Stripe\Exception\Base $e ) {
			wp_send_json( array( 'error' => $e->getMessage() ), 500 );
		} catch (\Stripe\Error\Base $e) {
			wp_send_json( array( 'error' => $e->getMessage() ), 500 );
		} catch (Exception $e) {
			// Something else happened, completely unrelated to Stripe
			wp_send_json( array( 'error' => $e->getMessage() ), 500 );
		}
		
		wp_die();
	}
	
	/**
	 * Ajax handler required by Stripe Payment Intents stuff.
	 *
	 * Stripe-specific function.
	 *
	 * @version 2.1.0
	 * @since   2.0.0
	 */
	public function ajax_confirm_payment() {
		
		$this->load_vendor_library();
		
		$json_str = file_get_contents('php://input');
		$json_obj = json_decode($json_str);
		
		$id            = intval( $json_obj->sliced_payment_invoice_id );
		$currency      = sliced_get_invoice_currency( $id );
		$currency_code = $currency && $currency !== 'default' ? $currency : $this->settings['currency'];
		$currency_exp  = Sliced_Shared::currency_exponent( $currency_code );
		
		$subscription_status = get_post_meta( $id, '_sliced_subscription_status', true );
		$is_subscription     = $subscription_status === 'pending' ? true : false;
		
		$intent = null;
		try {
			if ( isset( $json_obj->payment_method_id ) ) {
				# Create the PaymentIntent
				if ( $is_subscription ) {
					if ( isset( $json_obj->second_pass ) ) {
					
						$json_obj->payment_method = $json_obj->payment_method_id;
						
						$this->create_subscription( $json_obj, $id );
						wp_send_json( array(
							'success' => true,
							'payment_id' => isset( $json_obj->charges ) ? $json_obj->charges->data[0]->id : '',
						) );
						wp_die();
						
					} else {
						$intent = \Stripe\SetupIntent::create( 
							apply_filters( 'sliced_stripe_setupintent_create_args',
								array(
									'payment_method'      => $json_obj->payment_method_id,
									'description'         => addslashes( sprintf( __( 'Subscription for Invoice %s', 'sliced-invoices-stripe' ), sliced_get_invoice_prefix( $id ) . sliced_get_invoice_number( $id ) . sliced_get_invoice_suffix( $id ) ) ),
									'confirm'             => true,
								)
							)
						);
					}
				} else {
					// get customer ID if they already exist in stripe
					$customer_id = $this->retrieve_customer( $id );
					if ( $customer_id === false ) {
						// customer doesn't exist... create it
						$customer_id = $this->create_customer( $id, $json_obj->payment_method_id );
					}
					$intent = \Stripe\PaymentIntent::create( 
						apply_filters( 'sliced_stripe_paymentintent_create_args',
							array(
								'payment_method'      => $json_obj->payment_method_id,
								'amount'              => $json_obj->amount,
								'currency'            => $currency_code,
								'customer'            => $customer_id,
								'description'         => addslashes( sprintf( __( 'Payment for Invoice %s', 'sliced-invoices-stripe' ), sliced_get_invoice_prefix( $id ) . sliced_get_invoice_number( $id ) . sliced_get_invoice_suffix( $id ) ) ),
								'confirmation_method' => 'manual',
								'confirm'             => true,
							)
						)
					);
				}
			}
			if ( isset( $json_obj->payment_intent_id ) ) {
				if ( $is_subscription ) {
					$intent = \Stripe\SetupIntent::retrieve(
						$json_obj->payment_intent_id
					);
					$intent->confirm();
				} else {
					$intent = \Stripe\PaymentIntent::retrieve(
						$json_obj->payment_intent_id
					);
					$intent->confirm();
				}
			}
			// Generate Payment Response
			# Note that if your API version is before 2019-02-11, 'requires_action'
			# appears as 'requires_source_action'.
			if ( $intent->status === 'requires_action' && $intent->next_action->type === 'use_stripe_sdk' ) {
				# Tell the client to handle the action
				if ( $is_subscription ) {
					wp_send_json( array(
						'requires_action' => true,
						'setup_intent_client_secret' => $intent->client_secret,
					) );
				} else {
					wp_send_json( array(
						'requires_action' => true,
						'payment_intent_client_secret' => $intent->client_secret,
					) );
				}
			} elseif ( $intent->status === 'succeeded' ) {
				# The payment didn’t need any additional actions and completed!
				# Handle post-payment fulfillment
				if ( $is_subscription ) {
					$this->create_subscription( $intent, $id );
				} else {
					$this->charge( $intent, $id );
				}
				wp_send_json( array(
					'success' => true,
					'payment_id' => isset( $intent->charges ) ? $intent->charges->data[0]->id : '',
				) );
			} else {
				# Invalid status
				wp_send_json( array(
					'error' => 'Invalid PaymentIntent status'
				), 500 );
			}
		} catch ( \Stripe\Exception\Base $e ) {
			wp_send_json( array( 'error' => $e->getMessage() ), 500 );
		} catch (\Stripe\Error\Base $e) {
			wp_send_json( array( 'error' => $e->getMessage() ), 500 );
		} catch (Exception $e) {
			// Something else happened, completely unrelated to Stripe
			wp_send_json( array( 'error' => $e->getMessage() ), 500 );
		}
		
		wp_die();
	}
	
	/**
	 * Cancel Stripe Subscription.
	 *
	 * Stripe-specific function.
	 *
	 * @since   1.3.0
	 */
	public function cancel_subscription( $id, $gateway_subscr_id ) {
		
		$this->load_vendor_library();
		
		try {
			$sub = \Stripe\Subscription::retrieve( $gateway_subscr_id );
			$sub->cancel();
			return array(
				'status'  => 'success',
				'message' => sprintf( __( 'Subscription %s cancelled', 'sliced-invoices-stripe' ), $gateway_subscr_id ),
			);
		} catch (Exception $e) {
			// Something happened, return error message
			$message = $e->getMessage();
			return array(
				'status'  => 'error',
				'message' => sprintf( __( 'Gateway says: %s', 'sliced-invoices-stripe' ), $message ),
			);
		}
		
	}
	
	/**
	 * Make sure this invoice can be paid by this gateway.
	 *
	 * @since   1.7.1
	 */
	public function check_payment_method( $pay_array ) {
		
		$remove = false;
		
		if ( 
			empty( $this->settings['enabled'] ) ||
			empty( $this->settings['secret'] ) ||
			empty( $this->settings['publishable'] ) ||
			empty( $this->settings['currency'] )
		) {
			$remove = true;
		}
		
		if ( $remove ) {
			if( ! empty( $pay_array[0] ) ) {
				$index = false;
				foreach( $pay_array[0] as $key => $value ) {
					if ( $value === 'stripe' ) { $index = $key; }
				}
				if ( $index ) {
					unset( $pay_array[0][$index] );
				}
			}
		}
		
		return $pay_array;
	}
	
	/**
	 * Create stripe customer record.
	 *
	 * Stripe-specific function.
	 *
	 * @since   1.3.0
	 */
	public function create_customer( $id, $payment_source ) {
		
		$this->load_vendor_library();
		
		$client_id      = get_post_meta( $id, '_sliced_client', true );
		$client_email   = sliced_get_client_email( $id );
		
		$args = array(
			'description' => __( sprintf( 'Customer for %s', $client_email ), 'sliced-invoices-stripe' ),
			'email'       => $client_email,
			'metadata'    => array(
				'sliced_invoice_id' => $id,
			),
		);
		if ( substr( $payment_source, 0, 3 ) === 'pm_' ) {
			$args['payment_method'] = $payment_source;
		} elseif ( substr( $payment_source, 0, 4 ) === 'src_' ) {
			$args['source'] = $payment_source;
		}
		$args = apply_filters( 'sliced_stripe_customer_create_args', $args );
		
		$customer = \Stripe\Customer::create( $args );
		
		update_user_meta( $client_id, '_sliced_stripe_id', $customer['id'] );
		
		return $customer['id'];
	}
	
	/**
	 * Create Stripe Subscription using their API.
	 *
	 * Stripe-specific function.
	 *
	 * @since   1.3.0
	 */
	public function create_subscription( $payment_source, $invoice_id ) {
		
		$this->load_vendor_library();
		
		$id = $invoice_id;
		
		$currency      = sliced_get_invoice_currency( $id );
		$currency_code = $currency && $currency !== 'default' ? $currency : $this->settings['currency'];
		$currency_exp  = Sliced_Shared::currency_exponent( $currency_code );
		
		// get customer ID if they already exist in stripe
		$customer_id = $this->retrieve_customer( $id );
		
		if ( $customer_id === false ) {
			// customer doesn't exist... create it
			$customer_id = $this->create_customer( $id, $payment_source->payment_method );
		} else {
			// attach this payment method to the customer
			$payment_method = \Stripe\PaymentMethod::retrieve( $payment_source->payment_method );
			$payment_method->attach( array( 'customer' => $customer_id ) );
		}
		
		// create plan
		$plan = \Stripe\Plan::create(
			apply_filters( 'sliced_stripe_plan_create_args',
				array(
					'id'                => 'sliced_invoice_'.$id.'-'.uniqid(),
					'amount'            => sliced_get_invoice_total_raw( $id ) * ( pow( 10, $currency_exp ) ),
					'currency'          => $currency_code,
					'interval'          => $this->get_billing_period( get_post_meta( $id, '_sliced_subscription_interval_type', true ) ),
					'interval_count'    => get_post_meta( $id, '_sliced_subscription_interval_number', true ),
					'metadata'          => array(
						'sliced_invoice_id' => $id,
					),
					'product'           => array(
						'name'              => sprintf( __( 'Invoice %s', 'sliced-invoices-stripe' ), sliced_get_invoice_prefix( $id ) . sliced_get_invoice_number( $id ) . sliced_get_invoice_suffix( $id ) ),
					),
				)
			)
		);
		
		$plan_id = $plan['id'];
		update_post_meta( $id, '_sliced_subscription_stripe_plan', $plan_id );
		
		$trial_amount = get_post_meta( $id, '_sliced_subscription_trial_amount', true );
		if ( $trial_amount > 0 ) {
			\Stripe\InvoiceItem::create(
				apply_filters( 'sliced_stripe_trial_invoiceitem_create_args',
					array(
						'customer' => $customer_id,
						'amount' => $trial_amount * ( pow( 10, $currency_exp ) ),
						'currency' => $currency_code,
						'description' => sprintf( __( 'One-time trial fee for Invoice %s', 'sliced-invoices-stripe' ), sliced_get_invoice_prefix( $id ) . sliced_get_invoice_number( $id ) . sliced_get_invoice_suffix( $id ) ),
					)
				)
			);
			$invoice = \Stripe\Invoice::create(
				apply_filters( 'sliced_stripe_trial_invoice_create_args',
					array(
						'customer' => $customer_id,
						'collection_method' => 'charge_automatically',
					)
				)
			);
			$invoice->finalizeInvoice();
		}
		
		// subscribe customer to plan
		$charge = \Stripe\Subscription::create(
			apply_filters( 'sliced_stripe_subscription_create_args',
				array(
					'customer' => $customer_id,
					'default_payment_method' => $payment_source->payment_method,
					'items'     => array(
						array(
							'plan' => $plan_id,
						),
					),
					'metadata' => array(
						'sliced_invoice_id' => $id,
					),
					'trial_period_days' => $this->get_trial_days( $id ),
					'expand'   => array(
						'latest_invoice.payment_intent',
					),
				)
			)
		);
		
		// activate subscription
		if ( class_exists( 'Sliced_Subscriptions' ) ) {
			Sliced_Subscriptions::activate_subscription_invoice( 
				$id, 
				'Stripe', // must match class name
				$charge['id'],
				print_r( $charge, true )
			);
		}
		
		if ( $charge['id'] ) {
			// record payment data
			$payments = get_post_meta( $invoice_id, '_sliced_payment', true );
			if ( ! is_array( $payments ) ) {
				$payments = array();
			}
			$payments[] = array(
				'gateway'    => 'stripe',
				'date'       => date("Y-m-d H:i:s"),
				'amount'     => sliced_get_invoice_total_raw( $invoice_id ),
				'currency'   => $currency_code,
				'payment_id' => $charge['id'],
				'status'     => 'success',
				'extra_data' => array( 
					'response'  => base64_encode( serialize( $payment_source ) ),
					'clientip'  => Sliced_Shared::get_ip(),
				),
			);
			update_post_meta( $invoice_id, '_sliced_payment', $payments );
			do_action( 'sliced_payment_made', $invoice_id, 'Stripe', 'success' );
		}
		
	}
	
	/**
	 * Add front end scripts & styles on the invoice payment page.
	 *
	 * @version  2.1.1
	 * @since    1.0.0
	 */
	public function enqueue_payment_scripts() {
		
		if ( apply_filters( 'sliced_invoices_stripe_enqueue_stripejs', true ) ) {
			wp_deregister_script( 'stripe' );
			wp_register_script( 'stripe', 'https://js.stripe.com/v3/', array( 'jquery' ) );
			wp_print_scripts( 'stripe' );
		}
		
		if ( apply_filters( 'sliced_invoices_stripe_enqueue_fontawesome', true ) ) {
			wp_register_style( 'fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css' );
			wp_print_styles( 'fontawesome' );
		}
		
		$inline = '<style type="text/css">';
		$inline .= ".stripe .btn {
				display: inline-block;
				width: 100%;
				text-align: center;
				border: none;
				padding: 15px;
				margin: 15px 0;
				font-size: 20px;
				background: rgb(66,173,230); /* Old browsers */
				background: -moz-linear-gradient(top,  rgba(66,173,230,1) 0%, rgba(47,125,194,1) 100%); /* FF3.6-15 */
				background: -webkit-linear-gradient(top,  rgba(66,173,230,1) 0%,rgba(47,125,194,1) 100%); /* Chrome10-25,Safari5.1-6 */
				background: linear-gradient(to bottom,  rgba(66,173,230,1) 0%,rgba(47,125,194,1) 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#42ade6', endColorstr='#2f7dc2',GradientType=0 ); /* IE6-9 */
			}
			#payment-request-button-wrapper > p {
				padding: 10px 0;
				text-align: center;
			}
			#stripe-element-card {
				padding: 14px 12px;
				font-size: 14px;
				font-weight: normal;
				line-height: 1;
				color: #555;
				border: 1px solid #ccc;
				border-radius: 4px;
				-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);
				box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);
				-webkit-transition: border-color ease-in-out .15s, -webkit-box-shadow ease-in-out .15s;
				-o-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
				transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
			}
			#stripe-element-card-errors {
				color: red;
			}
			#sliced-stripe-apple-pay-button {
				display: none;
				background-color: black;
				background-image: -webkit-named-image(apple-pay-logo-white);
				background-size: 100% 100%;
				background-origin: content-box;
				background-repeat: no-repeat;
				width: 100%;
				height: 44px;
				padding: 10px 0;
				border: none;
				border-radius: 10px;
			}
			.sliced-stripe-methods {
				list-style: none;
				margin: 0 0 1.5em;
				padding: 0;
			}
			.sliced-stripe-method-header {
				background-color: #f0f0f0;
				border-bottom: 1px solid #e8e8e8;
				padding: 16px;
				width: 100%;
			}
			.sliced-stripe-method-header img {
				float: right;
				max-height: 36px;
				width: auto;
			}
			.sliced-stripe-method-header label {
				cursor: pointer;
				margin-top: 5px;
			}
			.sliced-stripe-method-inner {
				clear: both;
				border: 1px solid #f0f0f0;
			}
			.sliced-stripe-method-inner .form-group {
				padding-left: 15px;
				padding-right: 15px;
			}
			.sliced-stripe-method-inner .form-group:first-child {
				padding-top: 15px;
			}
			.sliced-stripe-method-inner .form-group:last-child {
				padding-bottom: 15px;
			}
			.form-control:focus {
				border-color: #ccc;
				-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);
				box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);
			}
		";
		$inline .= '</style>';
		
		echo apply_filters( 'sliced_invoices_stripe_inline_css', $inline );
	}
	
	/**
	 * Add front end scripts & styles sitewide.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {
		
		if ( $this->settings['js_sitewide'] ) {
			wp_enqueue_script( 'stripe', 'https://js.stripe.com/v3/', array( 'jquery' ) );
		}
		
	}
	
	/**
	 * Helper to convert billing period value into the specific value needed by Stripe.
	 *
	 * Stripe-specific function.
	 *
	 * @since   1.3.0
	 */
	public function get_billing_period( $input ) {
		switch ( $input ) {
			case 'days':
				$output = 'day';
				break;
			case 'months':
				$output = 'month';
				break;
			case 'years':
				$output = 'year';
				break;
			default:
				$output = 'month';
		}
		return $output;
	}
	
	/**
	 * Gets all settings for this gateway.
	 *
	 * @version 2.1.0
	 * @since   2.0.0
	 */
	public function get_settings() {
		$payments = get_option( 'sliced_payments' );
		$settings = array(
			'enabled'         => isset( $payments['stripe_enabled'] ) && $payments['stripe_enabled'] === 'on' ? true : false,
			'mode'            => isset( $payments['stripe_mode'] ) ? $payments['stripe_mode'] : 'live', // default to 'live' for backwards compatibility
			'currency'        => isset( $payments['stripe_currency'] ) ? $payments['stripe_currency'] : false,
			'country'         => isset( $payments['stripe_apple_pay_country'] ) ? $payments['stripe_apple_pay_country'] : false,
			'payment_page'    => isset( $payments['payment_page'] ) ? intval( $payments['payment_page'] ) : 0,
			'cancel_page'     => isset( $payments['payment_page'] ) ? intval( $payments['payment_page'] ) : 0,
			// @TODO: idea for future version
			// 'checkout_type'   => isset( $payments['stripe_checkout_type'] ) ? $payments['stripe_checkout_type'] : 'hosted',
			'require_name'    => isset( $payments['stripe_require_name'] ) ? $payments['stripe_require_name'] : false,
			'js_sitewide'     => isset( $payments['stripe_js_sitewide'] ) ? $payments['stripe_js_sitewide'] : false,
			'apple_pay'       => isset( $payments['stripe_apple_pay'] ) ? $payments['stripe_apple_pay'] : false,
			'payment_request' => isset( $payments['stripe_payment_request'] ) ? $payments['stripe_payment_request'] : false,
			'alipay'          => isset( $payments['stripe_alipay'] ) ? $payments['stripe_alipay'] : false,
			'bancontact'      => isset( $payments['stripe_bancontact'] ) ? $payments['stripe_bancontact'] : false,
			'giropay'         => isset( $payments['stripe_giropay'] ) ? $payments['stripe_giropay'] : false,
			'ideal'           => isset( $payments['stripe_ideal'] ) ? $payments['stripe_ideal'] : false,
			'p24'             => isset( $payments['stripe_p24'] ) ? $payments['stripe_p24'] : false,
		);
		if ( $settings['mode'] === 'live' ) {
			$settings['secret']      = isset( $payments['stripe_secret'] ) ? $payments['stripe_secret'] : false;
			$settings['publishable'] = isset( $payments['stripe_publishable'] ) ? $payments['stripe_publishable'] : false;
		} else {
			$settings['secret']      = isset( $payments['stripe_secret_test'] ) ? $payments['stripe_secret_test'] : false;
			$settings['publishable'] = isset( $payments['stripe_publishable_test'] ) ? $payments['stripe_publishable_test'] : false;
		}
		return $settings;
	}
	
	/**
	 * Helper to convert trial period value into the specific value needed by Stripe.
	 *
	 * Stripe-specific function.
	 *
	 * @since   1.3.0
	 */
	public function get_trial_days( $id ) {
		if ( get_post_meta( $id, '_sliced_subscription_trial', true ) != '1' ) {
			return 0;
		}
		$trial_billing_period = get_post_meta( $id, '_sliced_subscription_trial_interval_type', true );
		$trial_billing_frequency = (int) get_post_meta( $id, '_sliced_subscription_trial_interval_number', true );
		$trial_total_billing_cycles = (int) get_post_meta( $id, '_sliced_subscription_trial_cycles_count', true );
		$days = 0;
		switch ( $trial_billing_period ) {
			case 'days':
				$days = $trial_billing_frequency;
				break;
			case 'months':
				$days = $trial_billing_frequency * 30;
				break;
			case 'years':
				$days = $trial_billing_frequency * 365;
				break;
		}
		if ( $trial_total_billing_cycles > 1 ) {
			$days = $days * $trial_total_billing_cycles;
		}
		return $days;
	}
	
	/**
	 * Load vendor library when called, or throw error if in incompatible version is already loaded.
	 *
	 * @version 2.1.2
	 * @since   1.7.1
	 */
	public function load_vendor_library() {
		
		if ( class_exists( '\Stripe\Stripe' ) ) {
			
			if ( version_compare( \Stripe\Stripe::VERSION, '7.123.0', '<' ) ) {
				
				if ( class_exists( 'Sliced_Admin_Notices' ) ) {
					
					if ( ! Sliced_Admin_Notices::has_notice( 'sliced_stripe_old_vendor_library' ) ) {
						$notice_args = array(
							'class' => 'notice-error',
							'content' => '<p>' . __( 'Sliced Invoices Stripe Gateway recently detected an outdated version of the Stripe library being loaded on your site, possibly from one of your other plugins. This blocked Sliced Invoices from loading a more recent version.  As a result, Stripe payments may not work properly.  Please check your plugins to make sure they are updated, and/or deactivate any older Stripe gateways you are no longer using.', 'sliced-invoices-stripe' ) . '</p>',
							'dismissable' => true,
							'dismiss_permanent' => '1',
						);
						Sliced_Admin_Notices::add_custom_notice( 'sliced_stripe_old_vendor_library', $notice_args );
					}
					
				}
				
			}
			
		} else {
		
			require_once SLICED_INVOICES_STRIPE_PATH . 'vendor/stripe-php/init.php' ;
			
			Sliced_Admin_Notices::remove_notice( 'sliced_stripe_old_vendor_library' );
			
		}
		
		\Stripe\Stripe::setApiKey( $this->settings['secret'] );
		\Stripe\Stripe::setApiVersion( '2019-09-09' );
		
		do_action( 'sliced_invoices_stripe_vendor_library_loaded' );
	}
	
	/**
	 * Process Webhooks (for subscription invoice payments)
	 *
	 * Stripe-specific function.
	 *
	 * @since   1.3.0
	 */
	public function process_webhook() {
		
		$this->load_vendor_library();
		
		// Retrieve the request's body and parse it as JSON
		$input = @file_get_contents("php://input");
		$event_json = json_decode($input);
		
		try {
			
			// Verify the event by fetching it from Stripe
			$event = \Stripe\Event::retrieve($event_json->id);
			
			// Do something with $event
			switch( $event->type ) {
			
				case 'customer.subscription.deleted':
					
					$args = array(
						'post_type'     =>  'sliced_invoice',
						'meta_key'      =>  '_sliced_subscription_gateway_subscr_id',
						'meta_value'    =>  $event->data->object->id,
					);
					$query      = get_posts( $args );
					$id         = $query[0]->ID;
					
					if ( class_exists( 'Sliced_Subscriptions' ) ) {
						Sliced_Subscriptions::cancel_subscription_invoice( $id, $event );
					}
			
					break;
					
				case 'invoice.payment_succeeded':
				
					// 2017-02-14 api version
					$gateway_subscr_id = $event->data->object->id;
					// catch for 2017-06-05 api version + newer
					if ( substr( $gateway_subscr_id, 0, 4 ) !== 'sub_' ) {
						$gateway_subscr_id = $event->data->object->subscription;
					}
					
					$args = array(
						'post_type'     =>  'sliced_invoice',
						'meta_key'      =>  '_sliced_subscription_gateway_subscr_id',
						'meta_value'    =>  $gateway_subscr_id,
					);
					$query      = get_posts( $args );
					$id         = $query[0]->ID;
					
					if ( class_exists( 'Sliced_Subscriptions' ) ) {
						Sliced_Subscriptions::create_receipt_invoice( $id, $event );
					}
			
					break;
					
				// to-do: add in case 'invoice.payment_succeeded', some solution for this:
				// http://stackoverflow.com/questions/25130263/set-end-date-when-setting-up-stripe-subscription
			}
			
		} catch (Exception $e) {
			// Something happened, return error message
			$message = $e->getMessage();
			echo $message;
		}
		
	}
	
	/**
	 * Adds this gateway to the list of registered payment methods.
	 */
	public function register_payment_method( $pay_array ) {
		
		if ( ! empty( $this->settings['enabled'] ) ) {
			$pay_array['stripe'] = 'Stripe';
		}
		return $pay_array;
		
	}
	
	/**
	 * Outputs "Requirements Not Met" notice.
	 *
	 * @since   1.7.5
	 */
	public function requirements_not_met_notice() {
		echo '<div id="message" class="error">';
		echo '<p>' . sprintf( __( 'Sliced Invoices Stripe Gateway cannot find the required <a href="%s">Sliced Invoices plugin</a>. Please make sure the core Sliced Invoices plugin is <a href="%s">installed and activated</a>.', 'sliced-invoices-stripe' ), 'https://wordpress.org/plugins/sliced-invoices/', admin_url( 'plugins.php' ) ) . '</p>';
		echo '</div>';
	}
	
	/**
	 * Try to find an existing stripe customer.
	 *
	 * Stripe-specific function.
	 */
	public function retrieve_customer( $id ) {
		
		$client_id      = get_post_meta( $id, '_sliced_client', true );
		//$client_email   = sliced_get_client_email( $id );
		
		// check if customers stripe id exists in the database
		$stripe_id = get_user_meta( $client_id, '_sliced_stripe_id', true );
		
		// check if stripe id is valid
		$customer_id = false;
		if( $stripe_id && $stripe_id != '' ) {
		
			try {
				$stripe_customer = \Stripe\Customer::retrieve($stripe_id);
				if ( $stripe_customer && ! property_exists($stripe_customer, 'deleted') ) {
					$customer_id = $stripe_id;
				}
			} catch (Exception $e) {
				// Something happened, return $customer_id (false)
				return $customer_id;
			}
		} 
		/* else {
			// get all customers and create array of emails and ID's
			$stripe_customers = \Stripe\Customer::all( array( "limit" => 100 ) ); // 100 is max
			if( $stripe_customers['data'] ) {
				foreach ($stripe_customers['data'] as $key => $value) {
					if( $value['email'] != '' ) { // ignore empty emails
						$cust_emails[$value['email']] = $value['id'];
					}
				}
			}
			// Try to match the client email with a stripe customer email and then get their stripe id
			if( array_key_exists( $client_email, $cust_emails ) ) {
				$customer_id = $cust_emails[$client_email];
			}
			
		}
		*/
		
		return $customer_id;
	}
	
	/**
	 * Validate settings, make sure all requirements met, etc.
	 *
	 * @version 2.1.0
	 * @since   1.7.5
	 */
	public function validate_settings() {
		
		if ( ! class_exists( 'Sliced_Invoices' ) ) {
			
			// Add a dashboard notice.
			add_action( 'admin_notices', array( $this, 'requirements_not_met_notice' ) );
			
			return false;
		}
		
		return true;
	}
	
	
	/******************************************************************************
	 * Core functions required for every Sliced Invoices gateway                  *
	 ******************************************************************************/
	
	/**
	 * Charge a payment.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed     $payment_source The payment method/source/intent to charge.
	 * @param int       $invoice_id     The invoice ID the payment is being made
	 *                                  against.
	 * @param int|float $amount         Optional. If not provided, we'll get it
	 *                                  from the invoice.
	 * @param string    $currency       Optional. The currency of $amount. If not
	 *                                  provided, we'll get it from the invoice.
	 *
	 * @return array.
	 */
	public function charge( $payment_source, $invoice_id, $amount = null, $currency = null ) {
		
		if ( ! $invoice_id ) {
			return false;
		}
		
		if ( ! $amount ) {
			$amount = sliced_get_invoice_total_due_raw( $invoice_id );
		}
		
		if ( ! $currency ) {
			$currency      = sliced_get_invoice_currency( $invoice_id );
			$currency_code = $currency && $currency !== 'default' ? $currency : $this->settings['currency'];
			$currency_exp  = Sliced_Shared::currency_exponent( $currency_code );
		}
		
		// begin
		$success = false;
		$result  = array(
			'status' => 'failed',
			'message' => '',
			'payment_id' => false,
		);
		
		if ( $payment_source instanceof Stripe\PaymentIntent ) {
			
			// it's a completed PaymentIntent from our onsite checkout form
			$success = true;
			$payment_id = isset( $payment_source->charges->data[0]->id ) ? $payment_source->charges->data[0]->id : '';
			$amount = Sliced_Shared::get_formatted_number( $payment_source->amount / ( pow( 10, $currency_exp ) ) );
			$result['status'] = 'success';
			$result['payment_id'] = $payment_id;
			
		} elseif ( $payment_source instanceof Stripe\Charge ) {
		
			// it's a completed Charge from our onsite checkout form
			$success = true;
			$payment_id = isset( $payment_source->id ) ? $payment_source->id : '';
			$amount = Sliced_Shared::get_formatted_number( $payment_source->amount / ( pow( 10, $currency_exp ) ) );
			$result['status'] = 'success';
			$result['payment_id'] = $payment_id;
			
		} elseif ( is_array( $payment_source ) && isset( $payment_source['token'] ) ) {
		
			// it's a token to be charged
			$this->load_vendor_library();
			try {
			
				// get customer ID if they already exist in stripe
				$customer_id = $this->retrieve_customer( $invoice_id );
				
				if ( $customer_id === false ) {
					// customer doesn't exist... create it
					$customer_id = $this->create_customer( $invoice_id, $payment_source['token'] );
				}
				
				// charge
				$charge = \Stripe\Charge::create(
					apply_filters( 'sliced_stripe_charge_create_args',
						array(
							'amount'      => $amount * ( pow( 10, $currency_exp ) ),
							'currency'    => strtolower( $currency_code ),
							'customer'    => $customer_id,
							'metadata'    => array(
								'sliced_invoice_id' => $invoice_id,
							),
							'source'      => $payment_source['token'],
							'description' => addslashes( sprintf( __( 'Payment for Invoice %s', 'sliced-invoices-stripe' ), sliced_get_invoice_prefix( $invoice_id ) . sliced_get_invoice_number( $invoice_id ) . sliced_get_invoice_suffix( $invoice_id ) ) ),
						)
					)
				);
				if ( $charge->status === 'succeeded' ) {
					$success = true;
					$payment_id = isset( $charge->id ) ? $charge->id : '';
					$amount = Sliced_Shared::get_formatted_number( $charge->amount / ( pow( 10, $currency_exp ) ) );				
					$result['status'] = 'success';
					$result['payment_id'] = $payment_id;
				} else {
					$result['message'] = 'Payment failed';
				}
			} catch ( \Stripe\Exception\Base $e ) {
				$result['message'] = $e->getMessage();
			} catch (\Stripe\Error\Base $e) {
				$result['message'] = $e->getMessage();
			} catch (Exception $e) {
				$result['message'] = $e->getMessage();
			}
			
		}
		
		if ( $success ) {
			// record payment data
			$payments = get_post_meta( $invoice_id, '_sliced_payment', true );
			if ( ! is_array( $payments ) ) {
				$payments = array();
			}
			$payments[] = array(
				'gateway'    => 'stripe',
				'date'       => date("Y-m-d H:i:s"),
				'amount'     => $amount,
				'currency'   => $currency_code,
				'payment_id' => $payment_id,
				'status'     => $success ? 'success' : 'failed',
				'extra_data' => array( 
					'response'  => base64_encode( serialize( $payment_source ) ),
					'clientip'  => Sliced_Shared::get_ip(),
				),
			);
			update_post_meta( $invoice_id, '_sliced_payment', $payments );
		}
			
		do_action( 'sliced_payment_made', $invoice_id, 'Stripe', $success ? 'success' : 'failed' );
		
		return $result;
	}
	
	/**
	 * Displays the payment form.
	 *
	 * This is the form users see before making payment. For AJAX-y payment methods
	 * users will see their final confirmation before leaving this page. For other
	 * payment methods that require a redirect, users will see their final
	 * confirmation later in payment_return().
	 *
	 * In other words, this is the first half of your payment page.
	 * (i.e. https://example.com/payment).
	 *
	 * @version 2.1.0
	 * @since   1.0.0
	 */
	public function payment_form() {
		#region payment_form
		
		// do some checks
		if ( empty( $_POST ) ) {
			return;
		}
		
		if ( ! isset( $_POST['start-payment'] ) ) {
			// we haven't POSTED from the invoice page, so bail
			return;
		}
		
		if ( $_POST['sliced_gateway'] !== 'stripe' ) {				
			// client is paying with some other gateway, bail
			return;
		}
		
		$id = intval( $_POST['sliced_payment_invoice_id'] );
		
		// check the nonce
		if( ! isset( $_POST['sliced_payment_nonce'] ) || ! wp_verify_nonce( $_POST['sliced_payment_nonce'], 'sliced_invoices_payment' ) ) {
			sliced_print_message( $id, __( 'There was an error with the form submission, please try again.', 'sliced-invoices-stripe' ), 'error' );
			return;
		}
		
		// if we made here, it looks like we're ready to begin a Stripe payment.
		// let's begin...
		$currency            = sliced_get_invoice_currency( $id );
		$currency_code       = $currency && $currency !== 'default' ? $currency : $this->settings['currency'];
		$currency_exp        = Sliced_Shared::currency_exponent( $currency_code );
		$payments_url        = esc_url( get_permalink( $this->settings['payment_page'] ) );
		$subscription_status = get_post_meta( $id, '_sliced_subscription_status', true );
		$totals              = Sliced_Shared::get_totals( $id );
		
		$stripe_sources = array( 'card' => __( 'Credit Card', 'sliced-invoices-stripe' ) );
		if ( $this->settings['alipay'] && in_array( $currency_code, array( 'AUD', 'CAD', 'EUR', 'GBP', 'HKD', 'JPY', 'NZD', 'SGD', 'USD' ) ) && $subscription_status !== 'pending' ) {
			$stripe_sources['alipay'] = __( 'Alipay', 'sliced-invoices-stripe' );
		}
		if ( $this->settings['bancontact'] && $currency_code === 'EUR' && $subscription_status !== 'pending' ) {
			$stripe_sources['bancontact'] = __( 'Bancontact', 'sliced-invoices-stripe' );
		}
		if ( $this->settings['giropay'] && $currency_code === 'EUR' && $subscription_status !== 'pending' ) {
			$stripe_sources['giropay'] = __( 'Giropay', 'sliced-invoices-stripe' );
		}
		if ( $this->settings['ideal'] && $currency_code === 'EUR' && $subscription_status !== 'pending' ) {
			$stripe_sources['ideal'] = __( 'iDEAL payment', 'sliced-invoices-stripe' );
		}
		if ( $this->settings['p24'] && ( $currency_code === 'EUR' || $currency_code === 'PLN' ) && $subscription_status !== 'pending' ) {
			$stripe_sources['p24'] = __( 'Przelewy24', 'sliced-invoices-stripe' );
		}
		
		?>
		<div class="sliced_payment_form stripe">
		
		<div id="payment-message" class="sliced-message message">
			<span><?php printf( __( '%s Number', 'sliced-invoices-stripe' ), sliced_get_invoice_label() ); ?>:</span> <?php esc_html_e( sliced_get_invoice_prefix() ); ?><?php esc_html_e( sliced_get_invoice_number() ); ?><br />
			<span><?php printf( __( '%s Amount', 'sliced-invoices-stripe' ), sliced_get_invoice_label() ); ?>:</span> <?php echo Sliced_Shared::get_formatted_currency( $totals['total'], $id ); ?>
			<?php do_action( 'sliced_payment_message' ); ?>
		</div>
		
		<div id="payment-success-message" class="sliced-message success" style="display: none;">
			<span class="dashicons dashicons-yes"></span>
			<h2><?php _e( 'Success', 'sliced-invoices-stripe' ); ?></h2>
			<p><?php
			if ( $subscription_status === 'pending' ) {
				_e( 'Subscription has been activated!', 'sliced-invoices-stripe' );
			} else {
				_e( 'Payment ID: %s', 'sliced-invoices-stripe' );
			}
			?></p>
			<p>
			<?php printf( __( '<a href="%1s">Click here to return to %s</a>', 'sliced-invoices-stripe' ), apply_filters( 'sliced_get_the_link', get_permalink($id), $id ), sliced_get_invoice_label() ); ?>
			</p>
		</div>
		
		<form action="" method="POST" id="payment-form" autocomplete="on">
			
			<span class="sliced-message error" style="display:none;"></span>
			
			<?php do_action( 'sliced_payment_inline_before_form_fields' ); ?>
			
			<div id="payment-request-button-wrapper" style="display: none;">
				<div id="payment-request-button"></div>
				<p class="small"><?php _e( 'or enter your details below', 'sliced-invoices-stripe' ); ?></p>
			</div>
			
			<ul class="sliced-stripe-methods">
			
				<?php foreach ( $stripe_sources as $type => $label ): ?>
				
					<li class="sliced-stripe-method" data-type="<?php echo $type; ?>">
					
						<?php if ( count( $stripe_sources ) > 1 ): ?>
						<div class="sliced-stripe-method-header">
							<input type="radio" name="payment_type" id="payment-<?php echo $type; ?>" value="<?php echo $type; ?>" <?php echo ( $type === 'card' ? 'checked' : '' ); ?>> <label for="payment-<?php echo $type; ?>"><?php echo $label; ?></label>
							<?php if ( $type === 'card' ): ?>
							<img src="<?php echo SLICED_INVOICES_STRIPE_URL . 'assets/images/accept-cards.png'; ?>" alt="Credit Card">
							<?php endif; ?>
							<?php if ( $type === 'alipay' ): ?>
							<img src="<?php echo SLICED_INVOICES_STRIPE_URL . 'assets/images/accept-alipay.png'; ?>" alt="Alipay">
							<?php endif; ?>
							<?php if ( $type === 'bancontact' ): ?>
							<img src="<?php echo SLICED_INVOICES_STRIPE_URL . 'assets/images/accept-bancontact.png'; ?>" alt="Bancontact">
							<?php endif; ?>
							<?php if ( $type === 'giropay' ): ?>
							<img src="<?php echo SLICED_INVOICES_STRIPE_URL . 'assets/images/accept-giropay.png'; ?>" alt="Giropay">
							<?php endif; ?>
							<?php if ( $type === 'ideal' ): ?>
							<img src="<?php echo SLICED_INVOICES_STRIPE_URL . 'assets/images/accept-ideal.png'; ?>" alt="iDEAL payment">
							<?php endif; ?>
							<?php if ( $type === 'p24' ): ?>
							<img src="<?php echo SLICED_INVOICES_STRIPE_URL . 'assets/images/accept-p24.png'; ?>" alt="Przelewy24">
							<?php endif; ?>
						</div>
						
						<div class="sliced-stripe-method-inner" <?php echo ( $type !== 'card' ? 'style="display:none;"' : '' ); ?>>
						<?php endif; ?>
							
							<?php if ( $type === 'card' ): ?>
							<div class="form-group">
								<label><?php _e( 'Card Details', 'sliced-invoices-stripe' ); ?></label>
								<div id="stripe-element-card"></div>
								<div id="stripe-element-card-errors" role="alert"></div>
							</div>
							
							<?php if ( $this->settings['require_name'] ): ?>
							<div class="form-group">
								<label><?php _e( 'Name on Card', 'sliced-invoices-stripe' ); ?></label>
								<div class="input-group">
									<div class="input-group-addon" style="background-color: #fff; color: #dcdfe6; padding-right: 9px;"><i class="fa fa-lock fa-fw"></i></div>
									<input id="name" type="text" size="4" data-stripe="name" class="form-control" placeholder="Name on Card" autocomplete="off" required style="border-left: none; padding-left: 0;" />
								</div>
							</div>
							<?php endif; ?>
								
							<?php endif; ?>
							
							<?php if ( $type === 'ideal' ): ?>
							<div class="form-group">
								<label><?php _e( 'Bank Details', 'sliced-invoices-stripe' ); ?></label>
								<div id="stripe-element-ideal-bank" class="form-control" style="padding: 5px 0;"></div>
								<div id="stripe-element-ideal-bank-errors" role="alert"></div>
							</div>
							<?php endif; ?>
							
						<?php if ( count( $stripe_sources ) > 1 ): ?>
						</div>
						<?php endif; ?>
					</li>
				
				<?php endforeach; ?>
				
			</ul>
			
			<div class="form-group">
				<input type="hidden" name="sliced_payment_invoice_id" value="<?php echo $id; ?>" />
				<button type="submit" class="btn btn-success btn-lg">
					<span class="button-text"><?php
					if ( $subscription_status === 'pending' ) {
						_e( 'Confirm subscription', 'sliced-invoices-stripe' );
					} else {
						printf( __( 'Pay %s Now', 'sliced-invoices-stripe' ), sliced_get_invoice_total_due( $id ) );
					}
					?></span>
					<span class="button-continue" style="display: none;"><?php _e( 'Continue', 'sliced-invoices-stripe' ); ?></span>
					<img class="button-spinner" src="<?php echo SLICED_INVOICES_STRIPE_URL . 'assets/images/ajax-loader.gif'; ?>" alt="" style="display: none;">
				</button>
				<?php do_action( 'sliced_payment_inline_after_submit_button' ); ?>
			</div>
			
			<?php do_action( 'sliced_payment_inline_after_form_fields' ); ?>
			
		</form>
		
		<div class="gateway-image" id="sliced_gateway_image">
			<?php echo apply_filters( 'sliced_stripe_gateway_image', '' ); ?>
		</div>
		
		</div>
		
		<script type="text/javascript">
			
			jQuery(function($) {
			
				var $form = $( '#payment-form' );
				
				var stripe = Stripe( '<?php echo $this->settings['publishable'] ?>' );
				var elements = stripe.elements(<?php echo apply_filters( 'sliced_invoices_stripe_js_elements_options', '' ); ?>);
				
				var card = elements.create( 'card', <?php echo apply_filters( 'sliced_invoices_stripe_js_card_options', "{
					'style': {
						'base': {
							'fontFamily': \"'Open Sans','Arial','Helvetica',sans-serif\",
							'fontSize': '14px',
							'color': '#333',
							'::placeholder': {
								'color': '#a0a0a0',
							}
						},
						'invalid': {
							'color': 'red',
						},
					}
				}" ); ?>);
				card.mount( '#stripe-element-card' );
				
				<?php
				/* apple pay & payment request button */
				if ( ( $this->settings['apple_pay'] || $this->settings['payment_request'] ) && $subscription_status !== 'pending' ):
				?>
				
				var paymentRequest = stripe.paymentRequest(<?php echo apply_filters( 'sliced_invoices_stripe_js_paymentrequest_args', "{
					country: '" . $this->settings['country'] . "',
					currency: '" . strtolower( $currency_code ) . "',
					total: {
						label: '" . addslashes( sprintf( __( 'Payment for Invoice %s', 'sliced-invoices-stripe' ), sliced_get_invoice_prefix( $id ) . sliced_get_invoice_number( $id ) . sliced_get_invoice_suffix( $id ) ) ) . "',
						amount: " . sliced_get_invoice_total_due_raw( $id ) * ( pow( 10, $currency_exp ) ) . "
					}
				}" ); ?>);
				
				var prButton = elements.create( 'paymentRequestButton', {
					paymentRequest: paymentRequest,
				});
				
				// Check the availability of the Payment Request API first.
				paymentRequest.canMakePayment().then(function(result) {
					<?php if ( ! $this->settings['apple_pay'] ): ?>
					if ( result.applePay ) { return; }
					<?php endif; ?>
					if ( result ) {
						prButton.mount('#payment-request-button');
						$( '#payment-request-button-wrapper' ).fadeIn();
					}
				});
				
				paymentRequest.on('token', function(ev) {
					$('.error').hide();
				  
					// Send the token to your server to charge it!
					$.ajax( '<?php echo admin_url( 'admin-ajax.php' ); ?>' + '?action=sliced_stripe_charge_payment', {
						type: 'POST',
						data: JSON.stringify({
							token_id: ev.token.id,
							sliced_payment_amount: '<?php echo sliced_get_invoice_total_due_raw( $id ) * ( pow( 10, $currency_exp ) ); ?>',
							sliced_payment_invoice_id: '<?php echo $id; ?>'
						}),
						dataType: 'json'
					}).then( function( response ) {
						var $successP = $( '#payment-success-message p' ).first();
						var successMessage = $successP.text();
						successMessage = successMessage.replace( '%s', response.payment_id );
						$successP.text( successMessage );
						$( '#payment-form, #payment-message' ).fadeOut( 200, function() {
							$( '#payment-success-message' ).fadeIn();
						});
						<?php echo apply_filters( 'sliced_invoices_stripe_js_after_payment_success', '' ); ?>
						ev.complete( 'success' );
					}, function( response ) {
						$( '.error' ).show();
						$form.find( '.sliced-message' ).text( response.responseJSON.error );
						ev.complete( 'fail' );
					});
				});
				<?php endif; ?>
				
				<?php 
				/* Alipay */
				if ( isset( $stripe_sources['alipay'] ) ):
				?>function slicedBeginAlipay() {
					var args = <?php echo apply_filters( 'sliced_invoices_stripe_js_alipay_args', "{
						type: 'alipay',
						amount: " . sliced_get_invoice_total_due_raw( $id ) * ( pow( 10, $currency_exp ) ) . ",
						currency: '" . strtolower( $currency_code ) . "',
						redirect: {
							return_url: '" . add_query_arg( array(
								'sliced_stripe_alipay' => '1',
								'sliced_payment_amount' => sliced_get_invoice_total_due_raw( $id ),
								'sliced_payment_invoice_id' => $id,
							  ), $payments_url ) . "',
						}
					}\n" ); ?>;
					stripe.createSource( args ).then( function( result ) {
						stripeResponseHandler( result );
					});
				}
				<?php endif; ?>
				
				<?php 
				/* Bancontact */
				if ( isset( $stripe_sources['bancontact'] ) ):
				?>function slicedBeginBancontact() {
					var args = <?php echo apply_filters( 'sliced_invoices_stripe_js_bancontact_args', "{
						type: 'bancontact',
						amount: " . sliced_get_invoice_total_due_raw( $id ) * ( pow( 10, $currency_exp ) ) . ",
						currency: 'eur',
						owner: {
							name: '" . addslashes( sliced_get_client_business( $id ) ) . "'
						},
						redirect: {
							return_url: '" . add_query_arg( array(
								'sliced_stripe_bancontact' => '1',
								'sliced_payment_amount' => sliced_get_invoice_total_due_raw( $id ),
								'sliced_payment_invoice_id' => $id,
							  ), $payments_url ) . "',
						},
						statement_descriptor: '" . addslashes( sprintf( __( 'Payment for %s', 'sliced-invoices-stripe' ), sliced_get_invoice_prefix( $id ) . sliced_get_invoice_number( $id ) . sliced_get_invoice_suffix( $id ) ) ) . "'
					}\n" ); ?>;
					stripe.createSource( args ).then( function( result ) {
						stripeResponseHandler( result );
					});
				}
				<?php endif; ?>
				
				<?php
				/* Giropay */
				if ( isset( $stripe_sources['giropay'] ) ):
				?>function slicedBeginGiropay() {
					var args = <?php echo apply_filters( 'sliced_invoices_stripe_js_giropay_args', "{
						type: 'giropay',
						amount: " . sliced_get_invoice_total_due_raw( $id ) * ( pow( 10, $currency_exp ) ) . ",
						currency: 'eur',
						owner: {
							name: '" . addslashes( sliced_get_client_business( $id ) ) . "'
						},
						redirect: {
							return_url: '" . add_query_arg( array(
								'sliced_stripe_giropay' => '1',
								'sliced_payment_amount' => sliced_get_invoice_total_due_raw( $id ),
								'sliced_payment_invoice_id' => $id,
							  ), $payments_url ) . "',
						},
						statement_descriptor: '" . addslashes( sprintf( __( 'Payment for %s', 'sliced-invoices-stripe' ), sliced_get_invoice_prefix( $id ) . sliced_get_invoice_number( $id ) . sliced_get_invoice_suffix( $id ) ) ) . "'
					}\n" ); ?>;
					stripe.createSource( args ).then( function( result ) {
						stripeResponseHandler( result );
					});
				}
				<?php endif; ?>
				
				<?php
				/* iDEAL */
				if ( isset( $stripe_sources['ideal'] ) ):
				?>
				var idealBank = elements.create( 'idealBank', <?php echo apply_filters( 'sliced_invoices_stripe_js_idealbank_options', "{
					'style': {
						'base': {
							'padding': '10px 12px',
							'fontFamily': \"'Open Sans','Arial','Helvetica',sans-serif\",
							'fontSize': '14px',
							'color': '#333',
							'::placeholder': {
								'color': '#a0a0a0',
							}
						},
						'invalid': {
							'color': 'red',
						},
					}
				}" ); ?>);
				idealBank.mount( '#stripe-element-ideal-bank' );
				
				function slicedBeginIdeal() {
					var args = <?php echo apply_filters( 'sliced_invoices_stripe_js_ideal_args', "{
						type: 'ideal',
						amount: " . sliced_get_invoice_total_due_raw( $id ) * ( pow( 10, $currency_exp ) ) . ",
						currency: 'eur',
						redirect: {
							return_url: '" . add_query_arg( array(
								'sliced_stripe_ideal' => '1',
								'sliced_payment_amount' => sliced_get_invoice_total_due_raw( $id ),
								'sliced_payment_invoice_id' => $id,
							  ), $payments_url ) . "',
						},
						statement_descriptor: '" . addslashes( sprintf( __( 'Payment for %s', 'sliced-invoices-stripe' ), sliced_get_invoice_prefix( $id ) . sliced_get_invoice_number( $id ) . sliced_get_invoice_suffix( $id ) ) ) . "'
					}\n" ); ?>;
					stripe.createSource( idealBank, args ).then( function( result ) {
						stripeResponseHandler( result );
					});
				}
				<?php endif; ?>
				
				<?php
				/* P24 */
				if ( isset( $stripe_sources['p24'] ) ):
				?>function slicedBeginP24() {
					var args = <?php echo apply_filters( 'sliced_invoices_stripe_js_p24_args', "{
						type: 'p24',
						amount: " . sliced_get_invoice_total_due_raw( $id ) * ( pow( 10, $currency_exp ) ) . ",
						currency: '" . strtolower( $currency_code ) . "',
						owner: {
							name: '" . sliced_get_client_business( $id ) . "',
							email: '" . sliced_get_client_email( $id ) . "'
						},
						redirect: {
							return_url: '" . add_query_arg( array(
								'sliced_stripe_p24' => '1',
								'sliced_payment_amount' => sliced_get_invoice_total_due_raw( $id ),
								'sliced_payment_invoice_id' => $id,
							  ), $payments_url ) . "',
						},
						statement_descriptor: '" . addslashes( sprintf( __( 'Payment for %s', 'sliced-invoices-stripe' ), sliced_get_invoice_prefix( $id ) . sliced_get_invoice_number( $id ) . sliced_get_invoice_suffix( $id ) ) ) . "'
					}\n" ); ?>;
					stripe.createSource( args ).then( function( result ) {
						stripeResponseHandler( result );
					});
				}
				<?php endif; ?>
				
				<?php /* card -- sychronous charge supporting SCA/3DS */ ?>
				var extraDetails = {};
				function slicedBeginCard() {
					<?php if ( $this->settings['require_name'] ): ?>
					extraDetails = {
						'billing_details': {
							'name': $('#name').val()
						}
					}
					<?php endif; ?>
					<?php echo apply_filters( 'sliced_invoices_stripe_js_extra_details', '' ); ?>
					
					stripe.createPaymentMethod( 'card', card, extraDetails )
						.then( function( result ) {
							if ( result.error ) {
								// Show error in payment form
								$( '.error' ).show();
								$form.find( '.sliced-message' ).text( result.error.message );
								enableSubmitButton();
							} else {
								// Otherwise send paymentMethod.id to your server
								$.ajax( '<?php echo admin_url( 'admin-ajax.php' ); ?>' + '?action=sliced_stripe_confirm_payment', {
									type: 'POST',
									data: JSON.stringify({										
										amount: <?php echo sliced_get_invoice_total_due_raw( $id ) * ( pow( 10, $currency_exp ) ); ?>,
										currency: '<?php echo strtolower( $currency_code ); ?>',
										payment_method_id: result.paymentMethod.id,
										sliced_payment_invoice_id: '<?php echo $id; ?>'
									}),
									dataType: 'json'
								}).then( function( result ) {
									handleServerResponse( result );
								}, function( result ) {
									handleServerResponse( result );
								});
							}
						}
					);
				}
				
				card.addEventListener('change', function(event) {
					var displayError = document.getElementById( 'stripe-element-card-errors' );
					if ( event.error ) {
						displayError.textContent = event.error.message;
					} else {
						displayError.textContent = '';
					}
				});
				
				window.handleServerResponse = function( response ) {
					if ( typeof response.responseJSON !== "undefined" ) {
						response = response.responseJSON;
					}
					if ( response.error ) {
						// Show error from server on payment form
						var errorMessage;
						if ( typeof response.error === 'function' ) {
							errorMessage = response.error();
						} else {
							errorMessage = response.error;
						}
						console.log( errorMessage );
						if ( typeof errorMessage.responseText !== 'undefined' ) {
							errorMessage = errorMessage.responseText;
						}
						$( '.error' ).show();
						$form.find( '.sliced-message' ).text( errorMessage );
						enableSubmitButton();
					} else if (response.requires_action) {
						// Use Stripe.js to handle required card action
						if ( typeof response.setup_intent_client_secret !== "undefined" ) {
							stripe.confirmCardSetup(
								response.setup_intent_client_secret,
								{
									payment_method: {
										card: card,
										billing_details: ( typeof extraDetails.billing_details !== "undefined" ? { name: extraDetails.billing_details.name, } : {} ),
									},
								}
							).then( function( result ) {
								if ( result.error ) {
									// Show error in payment form
									$( '.error' ).show();
									$form.find( '.sliced-message' ).text( result.error );
									enableSubmitButton();
								} else {
									// The setup has succeeded.
									// The PaymentIntent can be confirmed again on the server
									$.ajax( '<?php echo admin_url( 'admin-ajax.php' ); ?>' + '?action=sliced_stripe_confirm_payment', {
										type: 'POST',
										data: JSON.stringify( {
											amount: <?php echo sliced_get_invoice_total_due_raw( $id ) * ( pow( 10, $currency_exp ) ); ?>,
											currency: '<?php echo strtolower( $currency_code ); ?>',
											payment_method_id: result.setupIntent.payment_method,
											sliced_payment_invoice_id: '<?php echo $id; ?>',
											second_pass: true
										} ),
										dataType: 'json'
									}).then( function( result ) {
										handleServerResponse( result );
									}, function( result ) {
										handleServerResponse( result );
									});
								}
							});
						} else {
							stripe.handleCardAction( response.payment_intent_client_secret )
								.then( function( result ) {
									if ( result.error ) {
										// Show error in payment form
										$( '.error' ).show();
										$form.find( '.sliced-message' ).text( result.error );
										enableSubmitButton();
									} else {
										// The card action has been handled
										// The PaymentIntent can be confirmed again on the server
										$.ajax( '<?php echo admin_url( 'admin-ajax.php' ); ?>' + '?action=sliced_stripe_confirm_payment', {
											type: 'POST',
											data: JSON.stringify( {
												payment_intent_id: result.paymentIntent.id,
												sliced_payment_invoice_id: '<?php echo $id; ?>'
											} ),
											dataType: 'json'
										}).then( function( result ) {
											handleServerResponse( result );
										}, function( result ) {
											handleServerResponse( result );
										});
									}
								});
						}
					} else {
						// Show success message
						enableSubmitButton();
						var $successP = $( '#payment-success-message p' ).first();
						var successMessage = $successP.text();
						successMessage = successMessage.replace( '%s', response.payment_id );
						$successP.text( successMessage );
						$( '#payment-form, #payment-message' ).fadeOut( 200, function() {
							$( '#payment-success-message' ).fadeIn();
						});
						<?php echo apply_filters( 'sliced_invoices_stripe_js_after_payment_success', '' ); ?>
					}
				}
				
				window.stripeResponseHandler = function( response ) {
					var $form = $('#payment-form');
					
					if ( response.error ) {
						// Show the errors on the form
						$('.error').show();
						$form.find('.sliced-message').text(response.error.message);
						enableSubmitButton();
					} else {
						// response contains id and card, which contains additional card details
						var token = response.source.id;
						// Insert the token into the form so it gets submitted to the server
						$form.append( $('<input type="hidden" name="stripeToken" />').val(token) );
						// do we need to redirect?
						if ( typeof ( response.source.redirect ) !== "undefined" ) {
							window.location.href = response.source.redirect.url;
						} else {
							// otherwise, submit
							$form.get(0).submit();
						}
					}
				};
				
				$('input[name=payment_type]').change( function(){
					var paymentType = $(this).val();
					var $cardFields = $( '#cardnumber, #cc-csc, #exp-month, #exp-year, #name, #address_zip' )
					$( '.sliced-stripe-method' ).each( function(){
						if ( $(this).data( 'type' ) === paymentType ) {
							$(this).children( '.sliced-stripe-method-inner' ).slideDown();
						} else {
							$(this).children( '.sliced-stripe-method-inner' ).slideUp();
						}
					});
					if ( paymentType === 'card' ) {
						$( $cardFields ).attr( 'required', true );
						$form.find( '.button-text' ).show();
						$form.find( '.button-continue' ).hide();
					} else {
						$( $cardFields ).attr( 'required', false );
						$form.find( '.button-text' ).hide();
						$form.find( '.button-continue' ).show();
					}
				});
				
				
				$form.submit(function(event) {
					event.preventDefault();
					
					disableSubmitButton();
					$( '.error' ).hide();
					
					var paymentType = $('input[name=payment_type]:checked').val();
					if ( paymentType === 'alipay' ) {
						slicedBeginAlipay();
					} else if ( paymentType === 'bancontact' ) {
						slicedBeginBancontact();
					} else if ( paymentType === 'giropay' ) {
						slicedBeginGiropay();
					} else if ( paymentType === 'ideal' ) {
						slicedBeginIdeal();
					} else if ( paymentType === 'p24' ) {
						slicedBeginP24();
					} else {
						slicedBeginCard();
					}
					
					return false;
				});
				
				function disableSubmitButton() {
					$form.find( '[type="submit"]' ).prop( 'disabled', true );
					$form.find( '.button-text' ).hide();
					$form.find( '.button-spinner' ).show();
				}
				
				function enableSubmitButton() {
					$form.find( '[type="submit"]' ).prop( 'disabled', false );
					$form.find( '.button-spinner' ).hide();
					$form.find( '.button-text' ).show();
				}
				
			});
		</script>
		
		<?php
		#endregion payment_form
	}
	
	/**
	 * Handles any returning data and maybe displays a confirmation message.
	 *
	 * Looks for webhooks directed at the payment page and sends them to
	 * process_webhook(), or for tokens from payment_form() that need to go to
	 * charge(). If the latter, a confirmation message (success or failure)
	 * will be shown to the user.
	 *
	 * In other words, this is the second half of your payment page.
	 * (i.e. https://example.com/payment).
	 *
	 * @since 1.0.0
	 */
	public function payment_return() {
		
		// is this a Stripe webhook?
		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && substr( $_SERVER['HTTP_USER_AGENT'], 0, 6 ) === 'Stripe' ) {
			$this->process_webhook();
			return;
		}
		
		// are we processing a Stripe token from payment_form()?
		$amount = false;
		$id     = false;
		$token  = false;
		$type   = false;
		
		if (
			( isset( $_GET['sliced_stripe_alipay'] ) && isset( $_GET['source'] ) ) ||
			( isset( $_GET['sliced_stripe_bancontact'] ) && isset( $_GET['source'] ) ) ||
			( isset( $_GET['sliced_stripe_giropay'] ) && isset( $_GET['source'] ) ) ||
			( isset( $_GET['sliced_stripe_ideal'] ) && isset( $_GET['source'] ) ) ||
			( isset( $_GET['sliced_stripe_p24'] ) && isset( $_GET['source'] ) )
		) {
			// alipay, bancontact, giropay, ideal, or p24 payment
			$amount = floatval( $_GET['sliced_payment_amount'] );
			$id     = intval( $_GET['sliced_payment_invoice_id'] );
			$token  = esc_html( $_GET['source'] );
			$type   = 'other';
		} elseif ( isset( $_POST['stripeToken'] ) ) {
			// card payment
			$amount = floatval( $_GET['sliced_payment_amount'] );
			$id     = intval( $_POST['sliced_payment_invoice_id'] );
			$token  = esc_html( $_POST['stripeToken'] );
			$type   = 'card';
		}
		
		if ( $token ) {
			$charge = $this->charge( 
				array(
					'token' => $token,
					'type' => $type,
				),
				$id,
				$amount
			);
		} else {
			return;
		}
		
		if ( $charge['status'] === 'success' ) {
			$status  = 'success';
			$message = '<h2>' . __( 'Success', 'sliced-invoices-stripe' ) .'</h2>';
			$message .= '<p>';
			$message .= sprintf( __( 'Payment ID: %s', 'sliced-invoices-stripe' ), $charge['payment_id'] );
			$message .= '</p>';
		} else {
			$status = 'failed';
			$message = '<h2>' . __( 'Error', 'sliced-invoices-stripe' ) .'</h2>';
			$message .= '<p>' . $charge['message'] . '</p>';
		}
		
		$message .= '<p>';
		$message .= sprintf( __( '<a href="%1s">Click here to return to %s</a>', 'sliced-invoices-stripe' ), apply_filters( 'sliced_get_the_link', get_permalink($id), $id ), sliced_get_invoice_label() );
		$message .= '</p>';
		sliced_print_message( $id, $message, $charge['status'] );
		
	}
	
	/**
	 * Refund a payment.
	 *
	 * TODO: idea for future version. For now, refunds can be done through Stripe dashboard.
	 *
	 * @since ?
	 *
	 * @param mixed     $payment_source The payment method/source/intent to charge.
	 * @param int       $invoice_id     The invoice ID the payment is being made
	 *                                  against.
	 * @param int|float $amount         Optional. If not provided, we'll get it
	 *                                  from the invoice.
	 * @param string    $currency       Optional. The currency of $amount. If not
	 *                                  provided, we'll get it from the invoice.
	 *
	 * @return array.
	 */
	public function refund( $payment_source, $invoice_id, $amount = null, $currency = null ) {
		// @TODO: idea for future version
	}
	
}
