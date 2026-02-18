<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Sliced_Subscriptions
 *
 * @package Sliced_Subscriptions
 */
class Sliced_Subscriptions {
	
	/** @var object Instance of this class */
	protected static $instance = null;
	
	
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
			'sliced-invoices-subscriptions',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
		
		if ( ! $this->validate_settings() ) {
			return;
		}
		
		Sliced_Invoices_Subscriptions_Admin::get_instance();
		
		add_action( 'sliced_do_payment', array( $this, 'payment_return' ) );
		add_action( 'sliced_head', array( $this, 'public_inline_css' ) );
		add_action( 'sliced_invoice_footer', array( $this, 'public_inline_js' ) );
		add_action( 'sliced_invoice_after_totals', array( $this, 'add_frontend_terms' ) );
		add_action( 'sliced_invoice_top_bar_left', array( $this, 'add_frontend_buttons' ) );
		add_action( 'sliced_payment_inline_after_submit_button', array( $this, 'add_frontend_payment_disclaimer' ) );
		add_action( 'sliced_payment_message', array( $this, 'add_frontend_terms_payment_page' ) );
		add_filter( 'sliced_get_invoice_watermark', array( $this, 'filter_watermark' ), 10, 2 );
		
	}
	
	
	/**
	 * Activate subscription invoice upon successful gateway result.
	 *
	 * @since 1.0.0
	 */
	public static function activate_subscription_invoice( $id, $gateway, $gateway_subscr_id, $gateway_extra_data ) {
		update_post_meta( $id, '_sliced_subscription_status', 'active' );
		update_post_meta( $id, '_sliced_subscription_amount', sliced_get_invoice_total( $id ) );
		update_post_meta( $id, '_sliced_subscription_amount_raw', sliced_get_invoice_total_raw( $id ) );
		update_post_meta( $id, '_sliced_subscription_gateway', $gateway );
		update_post_meta( $id, '_sliced_subscription_gateway_subscr_id', $gateway_subscr_id );
		update_post_meta( $id, '_sliced_subscription_log',
			array( 
				array(
					'action'             => 'activated',
					'timestamp'          => time(),
					'gateway_extra_data' => $gateway_extra_data,
				)
			)
		);
	}
	
	/**
	 * Adds subscription-related buttons to the frontend invoice view.
	 *
	 * @since 1.2.0
	 */
	public function add_frontend_buttons() {
		
		$id = Sliced_Shared::get_item_id();
		$subscription = $this->is_subscription_invoice( $id );
		
		if ( $subscription === 'active' ) {
			$payments = get_option( 'sliced_payments' );
			?>
			<div class="sliced_gateway_button">
				<form method="POST" action="<?php echo esc_url( get_permalink( (int)$payments['payment_page'] ) ) ?>" class="sliced_subscription_cancel">
					<?php do_action( 'sliced_before_payment_form_fields' ) ?>
					<?php wp_nonce_field( 'sliced_invoices_payment', 'sliced_payment_nonce' ); ?>
					<input type="hidden" name="sliced_payment_invoice_id" value="<?php the_ID(); ?>">
					<input type="hidden" name="sliced_gateway" value="sliced_subscriptions" />
					<input type="submit" name="cancel-subscription" class="gateway btn btn-danger btn-sm" value="<?php
						_e( 'Cancel subscription', 'sliced-invoices-subscriptions' );
					?>">
					<?php do_action( 'sliced_after_payment_form_fields' ) ?>
				</form>
			</div>
			<?php
		}
		
	}
	
	/**
	 * Adds a disclaimer to the frontend payment page.
	 *
	 * @since 1.4.0
	 */
	public function add_frontend_payment_disclaimer() {
		
		$id = Sliced_Shared::get_item_id();
		$subscription = $this->is_subscription_invoice( $id );
		
		if ( $subscription ) {
			
			$subscription_data = $this->existing_settings_data( $id );
			
			ob_start();
			?>
			<p class="sliced-subscription-terms-text"><?php _e( 'By completing checkout, you consent to be charged automatically according to the terms shown above.', 'sliced-invoices-subscriptions' ); ?></p>
			<?php
			$output = ob_get_clean();
			
			echo apply_filters( 'sliced_invoice_subscription_disclaimer_output', $output );
			
		}
	}
	
	/**
	 * Adds the subscription terms to the frontend invoice view.
	 *
	 * @since 1.0.0
	 */
	public function add_frontend_terms() {
		
		$id = Sliced_Shared::get_item_id();
		$subscription = $this->is_subscription_invoice( $id );
		
		if ( $subscription ) {
			
			$subscription_data = $this->existing_settings_data( $id );
			
			do_action( 'sliced_invoice_subscription_before_terms' );
			
			ob_start();
			?>
			<tr class="sliced-subscription-terms">
				<td colspan="2">
					<p class="sliced-subscription-terms-text">
						<strong><?php _e( 'Subscription Terms:', 'sliced-invoices-subscriptions' ); ?></strong>
						<?php echo sprintf( __( '%1$s charged every %2$s %3$s %4$s', 'sliced-invoices-subscriptions' ),
							$subscription_data['amount'],
							$subscription_data['interval_number'],
							$this->get_localized_interval_type( $subscription_data['interval_type'], $subscription_data['interval_number'] ),
							$this->get_localized_duration( $subscription_data['cycles_type'], $subscription_data['cycles_count'] )
						); ?>
						<?php if ( $subscription_data['trial'] == "1" ): ?>
							<br /><strong><?php _e( 'Trial period:', 'sliced-invoices-subscriptions' ); ?></strong>
							<?php echo sprintf( __( '%1$s for %2$s %3$s (%4$s %5$s)', 'sliced-invoices-subscriptions' ),
								Sliced_Shared::get_formatted_currency( $subscription_data['trial_amount'] ),
								$subscription_data['trial_interval_number'],
								$this->get_localized_interval_type( $subscription_data['trial_interval_type'], $subscription_data['trial_interval_number'] ),
								$subscription_data['trial_cycles_count'],
								_n( 'cycle', 'cycles', (int)$subscription_data['trial_cycles_count'], 'sliced-invoices-subscriptions' )
							); ?>
						<?php endif; ?>
					</p>
				</td>
			</tr>
			<?php
			$output = ob_get_clean();
			
			echo apply_filters( 'sliced_invoice_subscription_terms_output', $output );
			
			do_action( 'sliced_invoice_subscription_after_terms' );
			
		}
	}
	
	/**
	 * Adds the subscription terms to the frontend payment page.
	 *
	 * @since 1.4.0
	 */
	public function add_frontend_terms_payment_page() {
		
		$id = Sliced_Shared::get_item_id();
		$subscription = $this->is_subscription_invoice( $id );
		
		if ( $subscription ) {
			
			$subscription_data = $this->existing_settings_data( $id );
			
			do_action( 'sliced_invoice_subscription_before_terms' );
			
			ob_start();
			?>
			<br /><span><?php _e( 'Subscription Terms:', 'sliced-invoices-subscriptions' ); ?></span>
			<?php echo sprintf( __( '%1$s charged every %2$s %3$s %4$s', 'sliced-invoices-subscriptions' ),
				$subscription_data['amount'],
				$subscription_data['interval_number'],
				$this->get_localized_interval_type( $subscription_data['interval_type'], $subscription_data['interval_number'] ),
				$this->get_localized_duration( $subscription_data['cycles_type'], $subscription_data['cycles_count'] )
			); ?>
			<?php if ( $subscription_data['trial'] == "1" ): ?>
				<br /><span><?php _e( 'Trial period:', 'sliced-invoices-subscriptions' ); ?></span>
				<?php echo sprintf( __( '%1$s for %2$s %3$s (%4$s %5$s)', 'sliced-invoices-subscriptions' ),
					Sliced_Shared::get_formatted_currency( $subscription_data['trial_amount'] ),
					$subscription_data['trial_interval_number'],
					$this->get_localized_interval_type( $subscription_data['trial_interval_type'], $subscription_data['trial_interval_number'] ),
					$subscription_data['trial_cycles_count'],
					_n( 'cycle', 'cycles', (int)$subscription_data['trial_cycles_count'], 'sliced-invoices-subscriptions' )
				); ?>
			<?php endif; ?>
			<?php
			$output = ob_get_clean();
			
			echo apply_filters( 'sliced_invoice_subscription_terms_output', $output );
			
			do_action( 'sliced_invoice_subscription_after_terms' );
			
		}
	}
	
	/**
	 * Cancel subscription invoice (customer initiated).
	 *
	 * @since 1.0.0
	 */
	public static function cancel_subscription_invoice( $id, $gateway_extra_data ) {
		
		$log = get_post_meta( $id, '_sliced_subscription_log', true );
		if ( ! is_array( $log ) ) {
			$log = array();
		}
		$log[] = array(
			'action'             => 'client-cancelled',
			'timestamp'          => time(),
			'gateway_extra_data' => $gateway_extra_data,
		);
		update_post_meta( $id, '_sliced_subscription_log', $log );
		update_post_meta( $id, '_sliced_subscription_status', 'cancelled' );
		
	}
	
	/**
	 * Create payment receipt invoice after incoming payment webhook.
	 *
	 * @version 1.3.7
	 * @since   1.3.0
	 */
	public static function create_receipt_invoice( $id, $gateway_extra_data ) {
		
		global $wpdb;
		
		// log it
		$log = get_post_meta( $id, '_sliced_subscription_log', true );
		if ( ! is_array( $log ) ) {
			$log = array();
		}
		$log[] = array(
			'action'             => 'client-payment',
			'timestamp'          => time(),
			'gateway_extra_data' => $gateway_extra_data,
		);
		update_post_meta( $id, '_sliced_subscription_log', $log );
		
		// get the parent invoice post object so we can copy the data
		$parent_invoice = get_post( $id );
		
		// stop here if receipts are not enabled
		$receipt_enabled = get_post_meta( $parent_invoice->ID, '_sliced_subscription_receipts', true );
		if ( ! $receipt_enabled ) { return; }
		
		// otherwise, we go on...
		
		// setup for post_date in local timezone
		$timezone = new DateTimeZone( SLICED_TIMEZONE );
		$post_date = new DateTime();
		$post_date->setTimestamp( time() );
		$post_date->setTimezone( $timezone );
		
		// Arguments for the new invoice
		$args = array(
			'post_title'    => __( 'Receipt -', 'sliced-invoices-subscriptions' ) . ' ' . $parent_invoice->post_title,
			'post_content'  => $parent_invoice->post_content,
			'post_author'   => $parent_invoice->post_author,
			'post_status'   => 'publish',
			'post_type'     => 'sliced_invoice',
			'post_parent'   => $id,
			'post_password' => $parent_invoice->post_password,
			'post_date'     => $post_date->format( 'Y-m-d H:i:s' ), // local timezone timestamp
			// 'post_date_gmt' => date( 'Y-m-d H:i:s', $next ),        // UTC timestamp
		);
		
		// Insert the new invoice into the database
		$new_invoice_id = wp_insert_post( $args );
		
		/*
		 * get all current post terms ad set them to the new post draft
		 */
		wp_set_object_terms($new_invoice_id, 'paid', 'invoice_status', false);
		
		// duplicate post metas
		$post_metas = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id=%d",
				$parent_invoice->ID
			)
		);
		if ( $post_metas && count( $post_metas ) ) {
			$sql_query = "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES ";
			$sql_values = array();
			foreach ( $post_metas as $post_meta ) {
				$meta_key = esc_sql( $post_meta->meta_key );
				$meta_value = esc_sql( $post_meta->meta_value );
				if ( ! in_array( $meta_key, array( '_sliced_log', '_sliced_subscription_log' ) ) ) {
					$sql_values[]= "($new_invoice_id, '$meta_key', '$meta_value')";
				}
			}
			$sql_query .= implode( ',', $sql_values );
			$wpdb->query( $sql_query );
		}
		
		delete_post_meta( $new_invoice_id, '_sliced_payment_methods' );
		delete_post_meta( $new_invoice_id, '_sliced_payment' );
		delete_post_meta( $new_invoice_id, '_sliced_subscription_gateway_subscr_id' );
		
		// update post meta on new invoice with new values
		update_post_meta( $new_invoice_id, '_sliced_invoice_created', $post_date->format( 'U' ) );
		update_post_meta( $new_invoice_id, '_sliced_invoice_due', $post_date->format( 'U' ) );
		update_post_meta( $new_invoice_id, '_sliced_invoice_number', sliced_get_next_invoice_number() );
		update_post_meta( $new_invoice_id, '_sliced_subscription_status', 'receipt' );
		
		// update the invoice number on the new id
		Sliced_Invoice::update_invoice_number( $new_invoice_id );
		
		// auto send it?
		$auto_send  = get_post_meta( $parent_invoice->ID, '_sliced_subscription_receipts_auto_send', true );
		if( $auto_send == 'yes' ) {
			$send = new Sliced_Notifications;
			$send->send_the_invoice( $new_invoice_id );
		}
		
	}
	
	/**
	 * Get existing subscription data for the popop form inputs.
	 *
	 * @since 1.0.0
	 */
	public function existing_settings_data( $id ) {
		
		if ( ! $id ) {
			return;
		}
		
		return array(
			'status'                => $this->get_meta( $id, '_sliced_subscription_status' ),
			'amount'                => $this->get_meta( $id, '_sliced_subscription_amount' ),
			'amount_raw'            => $this->get_meta( $id, '_sliced_subscription_amount_raw' ),
			'gateway'               => $this->get_meta( $id, '_sliced_subscription_gateway' ),
			'gateway_subscr_id'     => $this->get_meta( $id, '_sliced_subscription_gateway_subscr_id' ),
			'interval_number'       => $this->get_meta( $id, '_sliced_subscription_interval_number' ),
			'interval_type'         => $this->get_meta( $id, '_sliced_subscription_interval_type' ),
			'cycles_type'           => $this->get_meta( $id, '_sliced_subscription_cycles_type' ),
			'cycles_count'          => $this->get_meta( $id, '_sliced_subscription_cycles_count' ),
			'trial'                 => $this->get_meta( $id, '_sliced_subscription_trial' ),
			'trial_interval_number' => $this->get_meta( $id, '_sliced_subscription_trial_interval_number' ),
			'trial_interval_type'   => $this->get_meta( $id, '_sliced_subscription_trial_interval_type' ),
			'trial_cycles_count'    => $this->get_meta( $id, '_sliced_subscription_trial_cycles_count' ),
			'trial_amount'          => $this->get_meta( $id, '_sliced_subscription_trial_amount' ),
			'receipts'              => $this->get_meta( $id, '_sliced_subscription_receipts' ),
			'receipts_auto_send'    => $this->get_meta( $id, '_sliced_subscription_receipts_auto_send' ),
		);
	}
	
	/**
	 * Adjust watermark shown on invoice page (frontend).
	 *
	 * @since 1.2.0
	 */
	public function filter_watermark( $output, $id ) {
		
		$id = Sliced_Shared::get_item_id();
		$subscription = $this->is_subscription_invoice( $id );
		
		switch ( $subscription ) {
			case 'active':
				return __( 'Active', 'sliced-invoices-subscriptions' );
				break;
			case 'cancelled':
				return __( 'Cancelled', 'sliced-invoices-subscriptions' );
				break;
		}
		
		return $output;
		
	}
	
	/**
	 * Convert internal interval_type designation to localized string.
	 *
	 * @since 1.0.0
	 */
	public function get_localized_interval_type( $interval_type, $interval_number ) {
		$output = false;
		switch ( $interval_type ) {
			case 'days':
				$output = _n( 'day', 'days', (int)$interval_number, 'sliced-invoices-subscriptions' );
				break;
			case 'months':
				$output = _n( 'month', 'months', (int)$interval_number, 'sliced-invoices-subscriptions' );
				break;
			case 'years':
				$output = _n( 'year', 'years', (int)$interval_number, 'sliced-invoices-subscriptions' );
				break;
		}
		return $output;
	}
	
	/**
	 * Convert internal cycles_type and cycles_count to human-readable localized string.
	 *
	 * @since 1.0.0
	 */
	public function get_localized_duration( $cycles_type, $cycles_count ) {
		$output = false;
		switch ( $cycles_type ) {
			case 'infinite':
				$output = __( 'until cancelled', 'sliced-invoices-subscriptions' );
				break;
			case 'fixed':
				$output = sprintf( __( 'for %1$s %2$s', 'sliced-invoices-subscriptions' ),
					$cycles_count,
					_n( 'payment', 'payments', (int)$cycles_count, 'sliced-invoices-subscriptions' )
				);
				break;
		}
		return $output;
	}
	
	/**
	 * Get the post meta.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 */
	public function get_meta( $id = 0, $key = '', $single = true ) {
		if ( ! $id ) {
			$id = Sliced_Shared::get_item_id();
		}
		$meta = get_post_meta( $id, $key, $single );
		return $meta;
	}
	
	/**
	 * Check if this is a recurring invoice.
	 *
	 * @since 1.0.0
	 */
	public function is_recurring_invoice( $id ) {
		$recurring  = $this->get_meta( $id, '_sliced_recurring_number' );
		$stopped    = $this->get_meta( $id, '_sliced_recurring_stopped' );
		if ( ! empty( $recurring ) && empty( $stopped ) ) { // this is a recurring invoice
			return $recurring;
		} else { // not recurring
			return false;
		}
	}
	
	/**
	 * Check if this is a subscription invoice.
	 *
	 * @since 1.0.0
	 */
	public function is_subscription_invoice( $id ) {
		$subscription_status = get_post_meta( $id, '_sliced_subscription_status', true );
		if ( ! empty( $subscription_status ) ) { // this is a subscription invoice
			return $subscription_status;
		} else { // not subscription
			return false;
		}
	}
	
	/**
	 * Handle payment page transactions (user-initiated cancel, payment info update, etc.).
	 *
	 * @since 1.2.0
	 */
	public function payment_return() {
		
		if ( ! isset( $_POST['sliced_gateway'] ) || $_POST['sliced_gateway'] !== 'sliced_subscriptions' ) {
			return;
		}
		
		// check the nonce
		if( ! isset( $_POST['sliced_payment_nonce'] ) || ! wp_verify_nonce( $_POST['sliced_payment_nonce'], 'sliced_invoices_payment' ) ) {
			sliced_print_message( $id, __( 'There was an error with the form submission, please try again.', 'sliced-invoices' ), 'error' );
			return;
		}
		
		$id = (int)$_POST['sliced_payment_invoice_id'];
		
		// set the start of the error message as a default. Overwritten if success
		$status = 'failed';
		$message = '<strong>Error!</strong> ';
		
		// cancel subscription
		if ( isset( $_POST['cancel-subscription'] ) ) {
		
			$subscription_status = get_post_meta( $id, '_sliced_subscription_status', true );
			
			if ( $subscription_status === 'active' ) {
				
				// if subscription status is 'active', we need to call the appropriate gateway to stop future payments
				$subscription_gateway = get_post_meta( $id, '_sliced_subscription_gateway', true );
				$subscription_gateway_subscr_id = get_post_meta( $id, '_sliced_subscription_gateway_subscr_id', true );
				$gateway_class = "Sliced_{$subscription_gateway}";
				
				if ( class_exists( $gateway_class ) ) {
				
					$gateway = new $gateway_class();
					$response = $gateway->cancel_subscription( $id, $subscription_gateway_subscr_id );
					
					if ( $response['status'] === 'success' ) {
						
						$status = $response['status'];
						
						$log = get_post_meta( $id, '_sliced_subscription_log', true );
						if ( ! is_array( $log ) ) {
							$log = array();
						}
						$log[] = array(
							'action'                  => 'cancelled',
							'timestamp'               => time(),
							'gateway_extra_data'      => $response,
						);
						update_post_meta( $id, '_sliced_subscription_log', $log );
						update_post_meta( $id, '_sliced_subscription_status', 'cancelled' );
						
						$message = '<h2>' . __( 'Success', 'sliced-invoices-subscriptions' ) .'</h2>';
						$message .= '<p>';
						$message .= __( 'Subscription has been cancelled.', 'sliced-invoices-subscriptions' ) . '<br>';
						$message .= '</p>';
						$message .= '<p>';
						$message .= sprintf( __( '<a href="%1s">Click here to return to %s</a>', 'sliced-invoices-subscriptions' ), apply_filters( 'sliced_get_the_link', get_permalink($id), $id ), sliced_get_invoice_label() );
						$message .= '</p>';
						
					} else {
						$message .= '<p>' . $response['message'] . '</p>';
					}
				} else {
					$message .= '<p>' . __( 'Invalid payment gateway', 'sliced-invoices-subscriptions' ) . '</p>';
				}
			}
		}
		
		sliced_print_message( $id, $message, $status );
		
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
			/* subscription invoices */
			.sliced-subscription-terms-text {
				text-align: left;
			}
		</style>
		<?php
		#endregion public_inline_css
	}
	
	/**
	 * Add inline js to public template.
	 *
	 * @since 1.2.0
	 */
	public function public_inline_js() {
		#region public_inline_js
		?>
		<script type="text/javascript">
			(function( $ ) {
				'use strict';
				
				$(document).ready( function () {
					
					$( ".sliced_subscription_cancel" ).submit(function() {
						return confirm( "<?php _e( 'Are you sure you want to cancel this subscription?  This cannot be undone.', 'sliced-invoices-subscriptions' ); ?>" );
					});
					
				});
			})( jQuery );
		</script>
		<?php
		#endregion public_inline_js
	}
	
	/**
	 * Output requirements not met notice.
	 *
	 * @since   1.3.3
	 */
	public function requirements_not_met_notice() {
		echo '<div id="message" class="error">';
		echo '<p>' . sprintf( __( 'Sliced Invoices Subscriptions extension cannot find the required <a href="%s">Sliced Invoices plugin</a>. Please make sure the core Sliced Invoices plugin is <a href="%s">installed and activated</a>.', 'sliced-invoices-subscriptions' ), 'https://wordpress.org/plugins/sliced-invoices/', admin_url( 'plugins.php' ) ) . '</p>';
		echo '</div>';
	}
	
	/**
	 * Validate settings, make sure all requirements met, etc.
	 *
	 * @version 1.3.6
	 * @since   1.3.3
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
