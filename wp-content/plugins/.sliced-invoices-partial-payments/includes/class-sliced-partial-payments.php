<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Sliced_Partial_Payments
 */
class Sliced_Partial_Payments {
	
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
	 * @version 1.1.2
	 * @since   1.0.0
	 */
	public function __construct() {
		
		load_plugin_textdomain(
			'sliced-invoices-partial-payments',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		); 
		
		if ( ! $this->validate_settings() ) {
			return;
		}
		
		Sliced_Invoices_Partial_Payments_Admin::get_instance();
		
		add_action( 'wp_loaded', array( $this, 'new_taxonomy_terms' ) );
		add_action( 'sliced_head', array( $this, 'public_inline_css' ) );
		add_action( 'sliced_invoice_footer', array( $this, 'public_inline_js' ) );
		add_action( 'sliced_payment_inline_after_form_fields', array( $this, 'add_payment_fields' ) );
		add_action( 'sliced_payment_popup_before_form_fields', array( $this, 'add_payment_chooser' ) );
		add_action( 'sliced_payment_made', array( $this, 'payment_made_1' ), 5, 3 );
		add_action( 'sliced_payment_made', array( $this, 'payment_made_2' ), 20, 3 );
		add_action( 'sliced_payment_message', array( $this, 'before_payment_message' ) );
		
		add_filter( 'get_the_terms', array( $this, 'pre_get_the_terms' ), 10, 3 );
		add_filter( 'sliced_get_invoice_total_due', array( $this, 'set_payment_amount' ), 100, 2 );
		add_filter( 'sliced_get_invoice_total_due_raw', array( $this, 'set_payment_amount_raw' ), 100, 2 );
		add_filter( 'sliced_invoices_check_for_reminder_args', array( $this, 'set_payment_reminder_args' ) );
		add_filter( 'sliced_payment_button_type', array( $this, 'set_payment_button_types' ), 10, 2 );
		
	}
	
	
	/**
	 * Adds the frontend payment chooser
	 *
	 * @since 1.0.0
	 */
	public function add_payment_chooser() {
		#region add_payment_chooser
		
		$id = sliced_get_the_id();
		
		$minimum_enabled = get_post_meta( $id, '_sliced_partial_payments_minimum_enable', true );
		$other_enabled   = get_post_meta( $id, '_sliced_partial_payments_other_enable', true );
		
		if ( $minimum_enabled ) {
			$minimum_amount = get_post_meta( $id, '_sliced_partial_payments_minimum_amount', true );
			$minimum_amount = $minimum_amount ? Sliced_Shared::get_raw_number( $minimum_amount ) : 0;
		}
		
		if ( $other_enabled ) {
			$other_minimum_amount = get_post_meta( $id, '_sliced_partial_payments_other_minimum_amount', true );
			$other_minimum_amount = $other_minimum_amount ? Sliced_Shared::get_raw_number( $other_minimum_amount ) : 0;
		}
		
		?>
		<div class="sliced-partial-payment-form-wrap">
			
			<h2><?php _e( 'How much do you want to pay now?', 'sliced-invoices-partial-payments' ); ?></h2>
			
			<?php if ( $other_enabled ): ?>
			<input type="text" class="sliced-partial-payment-form-other-amount" name="sliced_partial_payments_amount" placeholder="Other Amount"
				data-minimum="<?php echo $other_minimum_amount; ?>"
				data-minimum-formatted="<?php echo Sliced_Shared::get_formatted_currency( $other_minimum_amount );?>"
				data-total-due="<?php echo sliced_get_invoice_total_due_raw(); ?>" />
			<span class="sliced-partial-payment-form-warning"></span>
			<?php endif; ?>
			
			<button type="button" class="sliced-partial-payment-form-button" data-value="total">
				<span class="sliced-partial-payment-form-amount"><?php echo sliced_get_invoice_total(); ?></span>
				<i class="fa fa-check-circle"></i>
				<span class="sliced-partial-payment-form-label"><?php _e( 'Total Due', 'sliced-invoices' ); ?></span>
			</button>
			
			<?php if ( $minimum_enabled && $minimum_amount > 0 ): ?>
			<button type="button" class="sliced-partial-payment-form-button" data-value="minimum">
				<span class="sliced-partial-payment-form-amount"><?php echo Sliced_Shared::get_formatted_currency( $minimum_amount ); ?></span>
				<i class="fa fa-check-circle"></i>
				<span class="sliced-partial-payment-form-label"><?php _e( 'Minimum Payment', 'sliced-invoices' ); ?></span>
			</button>
			<?php endif; ?>
			
			<input type="hidden" name="sliced_partial_payments_option" value="" />
			
		</div>
		<?php
		#endregion add_payment_chooser
	}
	
	
	/**
	 * Adds the payment fields to carry on through to the payment return
	 *
	 * @since 1.0.0
	 */
	public function add_payment_fields() {
		#region add_payment_fields
		if ( isset( $_POST['sliced_partial_payments_option'] ) ) {
			?>
			<input type="hidden" name="sliced_partial_payments_option" value="<?php echo $_POST['sliced_partial_payments_option']; ?>" />
			<?php if ( isset( $_POST['sliced_partial_payments_amount'] ) ): ?>
			<input type="hidden" name="sliced_partial_payments_amount" value="<?php echo $_POST['sliced_partial_payments_amount']; ?>" />
			<?php endif; ?>
			<?php
		}
		#endregion add_payment_fields
	}
	
	
	/**
	 * Add some extra information on the payment page
	 *
	 * @since 1.0.0
	 */
	public function before_payment_message() {
		
		$id = (int) $_POST['sliced_payment_invoice_id'];
		$totals = Sliced_Shared::get_totals( $id );
		$original_total     = $totals['total'];
		$original_total_due = $totals['total'] - $totals['payments'];
		
		if ( ! (
			isset( $_POST['sliced_partial_payments_option'] ) ||
			$original_total_due < $original_total
		) ) {
			return;
		}
		
		?><br /><br />
		<span><?php _e( 'Balance Outstanding', 'sliced-invoices-partial-payments' ); ?>:</span> <?php echo Sliced_Shared::get_formatted_currency( $original_total_due, $id ); ?><br/>
		<span><?php _e( 'Amount to Pay', 'sliced-invoices-partial-payments' ); ?>:</span> <?php echo sliced_get_invoice_total_due( $id ); ?>
		<?php
		
	}
	
	
	public function new_taxonomy_terms() {
		
		$bypass = get_transient( 'sliced_partial_payments_taxonomy_terms_check' );
		if ( $bypass ) {
			return;
		}
		
		$flush_needed = false;
		
		$invoice_status = array(
			'invoice_status' => array(
				'Partially Paid',
			)
		);
		
		foreach ( $invoice_status as $taxonomy => $terms ) {
			foreach ( $terms as $term ) {
				if ( ! get_term_by( 'slug', sanitize_title( $term ), $taxonomy ) ) {
					$result = wp_insert_term( $term, $taxonomy );
					$flush_needed = true;
				}
			}
		}
		
		if ( $flush_needed ) {
			flush_rewrite_rules();
		}
		
		set_transient( 'sliced_partial_payments_taxonomy_terms_check', 'ok', 60*60*24 );
		
	}
	
	
	/**
	 * Post payment actions
	 *
	 * @since 1.0.0
	 */
	public function payment_made_1( $id, $gateway, $status ) {
		
		unset( $_POST['sliced_partial_payments_option'] );
		unset( $_POST['sliced_partial_payments_amount'] );
		
	}
	
	
	/**
	 * Post payment actions
	 *
	 * @since 1.0.0
	 */
	public function payment_made_2( $id, $gateway, $status ) {
		
		if ( $status !== 'success' ) {
			return;
		}
		
		// If partial payment was made, set status
		$output = Sliced_Shared::get_totals( $id );
		$total_still_due = $output['total_due'];
		if ( $total_still_due > 0.0001 ) { // we just want to know if it's > 0, but, you know, floating point bullshit
			Sliced_Invoice::set_status( 'partially-paid', $id );
		}
		
		// Extend due date?
		$extend_number = get_post_meta( $id, '_sliced_partial_payments_minimum_extend_number', true );
		$extend_number = $extend_number ? intval( Sliced_Shared::get_raw_number( $extend_number ) ) : 0;
		if ( $extend_number > 0 ) {
			// did payment meet minimum amount required to extend?
			$minimum_amount = get_post_meta( $id, '_sliced_partial_payments_minimum_amount', true );
			$minimum_amount = $minimum_amount ? Sliced_Shared::get_raw_number( $minimum_amount ) : 0;
			$payments = get_post_meta( $id, '_sliced_payment', true );
			if ( ! is_array( $payments ) ) {
				$payments = array();
			}
			$last_payment = end( $payments );
			$last_payment_amount = 0;
			if ( isset( $last_payment['amount'] ) ) {
				$last_payment_amount = Sliced_Shared::get_raw_number( $last_payment['amount'] );
			}
			if ( $last_payment_amount >= $minimum_amount ) {
				$extend_type = get_post_meta( $id, '_sliced_partial_payments_minimum_extend_type', true );
				$due_date = get_post_meta( $id, '_sliced_invoice_due', true );
				if ( $due_date ) {
					// nested conditionals FTW! (I confess my crime)
					$new_due_date = strtotime( "+$extend_number $extend_type", $due_date );
					update_post_meta( $id, '_sliced_invoice_due', $new_due_date );
				}
			}
		}
		
		do_action( 'sliced_partial_payment_made', $id );
		
	}
	
	
	/**
	 * Translate statuses.
	 *
	 * @since   1.1.1
	 */
	public function pre_get_the_terms( $terms, $post_id, $taxonomy ) {
		
		if ( $taxonomy === 'invoice_status' || $taxonomy === 'quote_status' ) {
			
			foreach ( $terms as &$term ) {
				$term->name = __( ucfirst( $term->name ), 'sliced-invoices-partial-payments' );
			}
			
		}
		
		return $terms;
	}
	
	
	/**
	 * Add inline css to public template.
	 *
	 * @since 1.0.0
	 */
	public function public_inline_css() {
		#region public_inline_css
		?>
		<style type="text/css">
			/* Partial Payments */
			.sliced-partial-payment-form-wrap {
				font-size: 18px;
				margin: 10px auto 30px;
				width: 320px;
			}
			.sliced-partial-payment-form-wrap h2 {
				font-size: 24px;
				font-weight: 700;
				line-height: 30px;
				padding-bottom: 16px;
				text-align: center;
			}
			.sliced-partial-payment-form-button {
				border-radius: 4px;
				background-color: #f4f0f3;
				border: 2px solid #f4f0f3;
				display: block;
				margin: 6px 0 0 0;
				outline: none;
				padding: 0;
				width: 100%;
				height: 48px;
			}
			.sliced-partial-payment-form-button:hover {
				background-color: #e8e4e7;
				border: 2px solid #e8e4e7;
			}
			.sliced-partial-payment-form-button.selected {
				border: solid 2px #0d74af;
				background-color: #f4f0f3;
			}
			.sliced-partial-payment-form-button i {
				display:none;
			}
			.sliced-partial-payment-form-button.selected i {
				display:block;
				font-size: 18px;
				color: #0D74AF;
				float: right;
				margin: 13px 10px 16px 0;
			}
			.sliced-partial-payment-form-amount {
				float: left;
				height: 22px;
				font-size: 18px;
				color: #011728;
				margin: 12px 0 12px 10px;
			}
			.sliced-partial-payment-form-label {
				float: right;
				line-height: 16px;
				height: 16px;
				font-size: 13px;
				color: #011728;
				margin: 15px 10px 15px 0;
				text-align: right;
			}
			.sliced-partial-payment-form-other-amount {
				display: block;
				padding: 10px;
				padding-right: 48px;
				font-size: 18px;
				color: #011728;
				line-height: 1.5em;
				border: 2px solid #cfcccf;
				border-radius: 4px;
				margin: 6px 0;
				outline: none;
				width: 100%;
			}
			.sliced-partial-payment-form-other-amount.selected {
				border: solid 2px #0d74af;
			}
			.sliced-partial-payment-form-warning {
				display: block;
				color: #0d74af;
				font-size: 12px;
				line-height: 12px;
				padding-bottom: 10px;
			}
		</style>
		<?php
		#endregion public_inline_css
	}
	
	
	/**
	 * Add inline js to public template.
	 *
	 * @since 1.0.0
	 */
	public function public_inline_js() {
		#region public_inline_js
		?>
		<script src="<?php echo SLICED_INVOICES_PARTIAL_PAYMENTS_URL . 'includes/js/autoNumeric.min.js'; ?>" type="text/javascript"></script>
		<script type="text/javascript">
			(function( $ ) {
				'use strict';
				
				$(document).ready( function () {
					
					// cache some selectors
					var $form = $('.sliced_payment_form_wrap form');
					var $otherAmount = $( $form ).find('input[name="sliced_partial_payments_amount"]');
					var $paymentOption = $( $form ).find('input[name="sliced_partial_payments_option"]');
					var $paymentButtons = $( $form ).find('.sliced-partial-payment-form-button');
					var $warningSpan = $( $form ).find('.sliced-partial-payment-form-warning');
					var $submitButton = $( $form ).find('#start-payment');
					
					// disable submit to start
					$( $submitButton ).attr('disabled',true);
					
					// ...and override default button text
					$( $submitButton ).val('<?php _e( 'Continue', 'sliced-invoices-partial-payments' ); ?>');
					
					// if "other amount" enabled, init input box here
					if ( $otherAmount.length > 0 ) {
						var otherAmount = new AutoNumeric('.sliced-partial-payment-form-other-amount', {
							currencySymbol: '<?php echo addslashes( sliced_get_currency_symbol() ); ?>',
							currencySymbolPlacement: '<?php echo ( sliced_get_currency_position() === 'right' ? 's' : 'p' ); ?>',
							decimalCharacter: '<?php echo addslashes( sliced_get_decimal_seperator() ); ?>',
							decimalPlaces: '<?php echo sliced_get_decimals(); ?>',
							digitGroupSeparator: '<?php echo addslashes( sliced_get_thousand_seperator() ); ?>',
							minimumValue: 0
						});
					}
					
					/*
					 * event handlers
					 */
					// other amount option
					$( $otherAmount ).on('click change keyup',function(){
						$( $paymentButtons ).removeClass('selected');
						$(this).addClass('selected');
						$( $paymentOption ).val("other");
						if ( otherAmount.getNumber() > 0 ) {
							// greater than 0
							if ( otherAmount.getNumber() > $otherAmount.data("total-due") ) {
								// paying greater than total due, are you sure?
								var warning = '<i class="fa fa-info-circle"></i> <?php _e( 'The amount you entered is more than the total due. Are you sure?', 'sliced-invoices-partial-payments'); ?>';
								$( $warningSpan ).html(warning);
								$( $submitButton ).attr('disabled',false);
							} else if ( otherAmount.getNumber() >= $otherAmount.data("minimum") ) {
								// okay
								$( $warningSpan ).html("");
								$( $submitButton ).attr('disabled',false);
							} else {
								// doesn't meet other amount minimum
								var warning = '<i class="fa fa-info-circle"></i> <?php _e( 'Amount must be at least %s', 'sliced-invoices-partial-payments'); ?>';
								warning = warning.replace( "%s", $otherAmount.data("minimum-formatted") );
								$( $warningSpan ).html(warning);
								$( $submitButton ).attr('disabled',true);
							}
						} else {
							// 0 or empty
							$( $warningSpan ).html("");
							$( $submitButton ).attr('disabled',true);
						}
					});
					
					// minimum payment / total due options
					$( $paymentButtons ).click(function(){
						$( $paymentButtons ).add( $otherAmount ).removeClass('selected');
						$(this).addClass('selected');
						$( $paymentOption ).val( $(this).data("value") );
						$( $submitButton ).attr('disabled',false);
						if ( $otherAmount.length > 0 ) {
							otherAmount.clear();
							$( $warningSpan ).html("");
						}
					});
					
				});
			})( jQuery );
		</script>
		<?php
		#endregion public_inline_js
	}
	
	
	/**
	 * Set the desired payment amount into sliced_get_invoice_total_due
	 *
	 * @since 1.0.0
	 */
	public function set_payment_amount( $total, $id ) {
		
		if ( isset( $_POST['sliced_partial_payments_option'] ) ) {
			switch ( $_POST['sliced_partial_payments_option'] ) {
				case 'minimum':
					$minimum_amount = get_post_meta( $id, '_sliced_partial_payments_minimum_amount', true );
					$minimum_amount = $minimum_amount ? Sliced_Shared::get_raw_number( $minimum_amount ) : 0;
					$total = Sliced_Shared::get_formatted_currency( $minimum_amount, $id );
					break;
				case 'other':
					$other_amount = Sliced_Shared::get_raw_number( $_POST['sliced_partial_payments_amount'] );
					$total = Sliced_Shared::get_formatted_currency( $other_amount, $id );
					break;
			}
		}
		
		return $total;
		
	}
	
	
	/**
	 * Set the desired payment amount into sliced_get_invoice_total_due_raw
	 *
	 * @since 1.0.0
	 */
	public function set_payment_amount_raw( $total, $id ) {
		
		if ( isset( $_POST['sliced_partial_payments_option'] ) ) {
			switch ( $_POST['sliced_partial_payments_option'] ) {
				case 'minimum':
					$minimum_amount = get_post_meta( $id, '_sliced_partial_payments_minimum_amount', true );
					$total = Sliced_Shared::get_raw_number( $minimum_amount );
					break;
				case 'other':
					$total = Sliced_Shared::get_raw_number( $_POST['sliced_partial_payments_amount'] );
					break;
			}
		}
		
		return $total;
		
	}
	
	
	/**
	 * Set the payment button types ('inline' or 'popup')
	 *
	 * @since 1.0.0
	 */
	public function set_payment_button_types( $type, $gateway ) {
		
		$id = sliced_get_the_id();
		
		// subscription invoices cannot be partially paid
		$subscription_status = get_post_meta( $id, '_sliced_subscription_status', true );
		if ( $subscription_status === 'pending' ) {
			return $type;
		}
		
		// make sure at least one partial payment option is enabled
		$minimum_enabled = get_post_meta( $id, '_sliced_partial_payments_minimum_enable', true );
		$minimum_amount = get_post_meta( $id, '_sliced_partial_payments_minimum_amount', true );
		$minimum_amount = $minimum_amount ? Sliced_Shared::get_raw_number( $minimum_amount ) : 0;
		$other_enabled   = get_post_meta( $id, '_sliced_partial_payments_other_enable', true );
		if ( ! ( $minimum_enabled && $minimum_amount ) && ! $other_enabled ) {
			return $type;
		}
		
		// okay, let's go ahead and activate the payment popup, so we can show
		// the partial payment options:
		$type = 'popup';
		/*
		// (maybe someday we'll have both popup and inline options...)
		switch ( $gateway ) {
			case 'paypal':
				$type = 'popup';
				break;
			case 'stripe':
				$type = 'inline';
				break;
		}
		*/
		
		return $type;
		
	}
	
	
	/**
	 * Add the 'partially-paid' term to payment reminder args.
	 *
	 * @since 1.1.2
	 */
	public function set_payment_reminder_args( $args ) {
		$args['tax_query'] = array(
			array(
				'taxonomy'  => 'invoice_status',
				'field'     => 'slug',
				'terms'     => array( 'unpaid', 'overdue', 'partially-paid' ),
			),
		);
		return $args;
	}
	
	
	/**
	 * Output requirements not met notice.
	 *
	 * @since   1.0.1
	 */
	public function requirements_not_met_notice() {
		echo '<div id="message" class="error">';
		echo '<p>' . sprintf( __( 'Sliced Invoices Partial Payments extension cannot find the required <a href="%s">Sliced Invoices plugin</a>. Please make sure the core Sliced Invoices plugin is <a href="%s">installed and activated</a>.', 'sliced-invoices-partial-payments' ), 'https://wordpress.org/plugins/sliced-invoices/', admin_url( 'plugins.php' ) ) . '</p>';
		echo '</div>';
	}
	
	
	/**
	 * Validate settings, make sure all requirements met, etc.
	 *
	 * @since   1.0.1
	 */
	public function validate_settings() {
		
		if ( ! class_exists( 'Sliced_Invoices' ) ) {
			
			// Add a dashboard notice.
			add_action( 'admin_notices', array( $this, 'requirements_not_met_notice' ) );
			
			return false;
		}
		
		return true;
	}
	
}
