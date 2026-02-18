<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Sliced_Invoices_Subscriptions_Admin
 */
class Sliced_Invoices_Subscriptions_Admin {
	
	/** @var  object  Instance of this class */
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
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	public function __construct() {
		
		add_action( 'add_meta_boxes', array( $this, 'add_subscriptions_meta_box' ) );
		add_action( 'admin_action_cancel_subscription_invoice', array( $this, 'admin_cancel_subscription_invoice' ) );
		add_action( 'admin_footer', array( $this, 'subscription_settings_form' ) );
		add_action( 'admin_head', array( $this, 'admin_inline_css' ) );
		add_action( 'admin_notices', array( $this, 'subscription_admin_notices' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_inline_js' ) );
		add_action( 'load-edit.php', array( $this, 'start_subscription_invoice' ) );
		add_action( 'load-edit.php', array( $this, 'update_subscription_invoice' ) );
		add_action( 'sliced_admin_col_after_invoice_number', array( $this, 'display_subscription_status_icon' ) );
		add_action( 'sliced_admin_col_views', array( $this, 'add_subscription_view_link' ) );
		add_filter( 'request', array( $this, 'filter_subscription') );
		
	}
	
	/**
	 * Add a link to view subscription invoices only.
	 *
	 * @since 1.0.0
	 */
	public function add_subscription_view_link( $views ) {
		
		if ( sliced_get_the_type() !== 'invoice' ) {
			return;
		}
		
		$args = array(
			'post_type'      => 'sliced_invoice',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => '_sliced_subscription_status',
					'compare' => 'EXISTS',
				),
			),
		);
		
		$ids = array();
		
		$the_query = new WP_Query( apply_filters( 'sliced_reports_query', $args ) );
		if ( $the_query->posts ) {
			foreach ( $the_query->posts as $id ) {
				$ids[] = $id;
			};
		}
		
		$count = count( $ids );
		
		$views['subscription'] = "<a href='" . esc_url( add_query_arg( array( 'invoice_type' => 'subscription' ) ) ) . "'>" . __( 'Subscription', 'sliced-invoices-subscriptions' ) . " <span class='count'>(" . esc_html( $count ) . ")</span></a>";
		
		return $views;
	}
	
	/**
	 * Adds the subscription meta box container.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 */
	public function add_subscriptions_meta_box() {
		
		global $pagenow;
		
		$SS = Sliced_Subscriptions::get_instance();
		
		// check if we are adding a new invoice
		if ( $pagenow === 'post-new.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'sliced_invoice' ) {
			// add the new invoice meta box
			add_meta_box( 
				'sliced_invoices_subscriptions', 
				sprintf( __( 'Subscription %s', 'sliced-invoices-subscriptions' ), sliced_get_invoice_label() ),
				array( $this, 'render_meta_box_help' ),
				'sliced_invoice',
				'side',
				'low'
			);
		}
		
		// otherwise, we go on...
		// check if we have a published invoice
		$id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : false;
		if ( ! $id ) {
			return;
		}
		
		// check if part of a deposit/balance
		$has_child  = get_post_meta( $id, '_sliced_deposit_child', true );
		$has_parent = get_post_meta( $id, '_sliced_deposit_parent', true );
		if ( $has_child || $has_parent ) {
			return;
		}
		
		// check if this is a recurring invoice
		$is_recurring = $SS->is_recurring_invoice( $id );
		if ( $is_recurring ) {
			return;
		}
		
		// add the meta box
		add_meta_box( 
			'sliced_invoices_subscriptions', 
			sprintf( __( 'Subscription %s', 'sliced-invoices-subscriptions' ), sliced_get_invoice_label() ) , array( $this, 'render_meta_box_content' ),
			'sliced_invoice',
			'side',
			'high'
		);
		
	}
	
	/**
	 * Cancel subscription invoice (admin action).
	 *
	 * @since 1.0.0
	 */
	public function admin_cancel_subscription_invoice() {
		
		if ( ! ( isset( $_REQUEST['action'] ) && 'cancel_subscription_invoice' === $_REQUEST['action'] ) ) {
			wp_die( 'No subscription to cancel' );
		}
		
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'cancel_subscription') ) {
			wp_die( 'Ooops, something went wrong, please try again later.' );
		}
		
		/*
		 * passed the checks, now cancel
		 */
		$id = (int)$_GET['parent_id'];
		$subscription_status = get_post_meta( $id, '_sliced_subscription_status', true );
		
		$can_cancel = true;
		$response = '';
		$query_args = array( 'post_type' => 'sliced_invoice', 'subscription' => 'cancelled' );
		
		if ( $subscription_status === 'active' ) {
			// if subscription status is 'active', we need to call the appropriate gateway to stop future payments
			$subscription_gateway = get_post_meta( $id, '_sliced_subscription_gateway', true );
			$subscription_gateway_subscr_id = get_post_meta( $id, '_sliced_subscription_gateway_subscr_id', true );
			$gateway_class = "Sliced_{$subscription_gateway}";
			if ( class_exists( $gateway_class ) ) {
				$gateway = new $gateway_class();
				$response = $gateway->cancel_subscription( $id, $subscription_gateway_subscr_id );
				if ( $response['status'] !== 'success' ) {
					$query_args = array( 'post_type' => 'sliced_invoice', 'subscription' => 'error', 'message' => urlencode( $response['message'] ) );
					$can_cancel = false;
				}
			} else {
				$query_args = array( 'post_type' => 'sliced_invoice', 'subscription' => 'error', 'message' => urlencode( 'Invalid payment gateway' ) );
				$can_cancel = false;
			}
		}
		
		if ( $can_cancel ) {
			$log = get_post_meta( $id, '_sliced_subscription_log', true );
			if ( ! is_array( $log ) ) {
				$log = array();
			}
			$log[] = array(
				'action'             => 'cancelled',
				'timestamp'          => time(),
				'gateway_extra_data' => $response,
			);
			update_post_meta( $id, '_sliced_subscription_log', $log );
			update_post_meta( $id, '_sliced_subscription_status', 'cancelled' );
		}
		
		$admin_url  = get_admin_url() . 'edit.php';
		wp_redirect( add_query_arg( $query_args, $admin_url ) );
		exit;
		
	}
	
	/**
	 * Add inline css to admin area.
	 *
	 * @since 1.0.0
	 */
	public function admin_inline_css() {
		#region admin_inline_css
		
		if ( sliced_get_the_type() !== 'invoice' ) {
			return;
		}
		?>
		<style type="text/css">
			/* subscription invoices */
			.sliced .sliced-subscription-status {
				font-weight: bold;
			}
			.sliced-subscription-pending {
				color: #ED904E;
			}
			.sliced-subscription-active {
				color: #60AD5D;
			}
			.sliced-subscription-cancelled {
				color: #a00;
			}
			.sliced tr .row-title .dashicons-clock {
				padding: 0 3px 0 0;
			}
			.sliced .sliced-invoices-subscriptions input.cycles_count {
				display: inline;
				margin: 0 0 0 10px;
				width: 50px;
			}
			.sliced .sliced-invoices-subscriptions input.trial_cycles_count {
				display: inline;
				margin: 0 10px 0 0;
				width: 50px;
			}
			.sliced .sliced-invoices-subscriptions input.interval_days {
				float: left;
				margin: 0 10px 0 0;
				width: 50px;
			}
			.sliced .sliced-invoices-subscriptions select {
				margin-top: 0;
				min-width: 50px;
			}
			.sliced .sliced-invoices-subscriptions .sliced-trial,
			.sliced .sliced-invoices-subscriptions .sliced-receipts {
				display: none;
				background-color: #f0f0f0;
			}
			.sliced #TB_ajaxContent .sliced-invoices-subscriptions tr.sliced-trial th,
			.sliced #TB_ajaxContent .sliced-invoices-subscriptions tr.sliced-trial td,
			.sliced #TB_ajaxContent .sliced-invoices-subscriptions tr.sliced-receipts th,
			.sliced #TB_ajaxContent .sliced-invoices-subscriptions tr.sliced-receipts td {
				padding: 5px 10px;
			}
		</style>
		<?php
		#endregion admin_inline_css
	}
	
	/**
	 * Add inline js to add dashicons.
	 *
	 * @since 1.0.0
	 */
	public function admin_inline_js() {
		#region admin_inline_js
		
		if ( sliced_get_the_type() !== 'invoice' ) {
			return;
		}
		?>
			<script type="text/javascript">
				(function( $ ) {
					'use strict';
					
					$(document).ready( function () {
						
						var pending = $( '.sliced_subscription_is_pending' ).closest( '.type-sliced_invoice' );
						var active = $( '.sliced_subscription_is_active' ).closest( '.type-sliced_invoice' );
						var cancelled = $( '.sliced_subscription_is_cancelled' ).closest( '.type-sliced_invoice' );
						
						$( pending ).find( '.row-title' ).prepend('<span class="dashicons dashicons-clock sliced-subscription-pending"></span>');
						$( active ).find( '.row-title' ).prepend('<span class="dashicons dashicons-clock sliced-subscription-active"></span>');
						$( cancelled ).find( '.row-title' ).prepend('<span class="dashicons dashicons-clock sliced-subscription-cancelled"></span>');
						
						$( "#sliced_cancel_subscription" ).click(function() {
							if( ! confirm( "<?php _e( 'Are you sure you want to cancel this subscription?', 'sliced-invoices-subscriptions' ); ?>" ) ) {
								return false;
							}
						});
						
						if( $( '#sliced_subscription_trial' ).is(':checked') ) {
							$( '.sliced-trial' ).show();
						}
						
						$( "#sliced_subscription_trial" ).change(function() {
							if( $(this).is(':checked') ) {
								$('.sliced .sliced-invoices-subscriptions .sliced-trial').slideDown();
							} else {
								$('.sliced .sliced-invoices-subscriptions .sliced-trial').slideUp();
							}
						});
						
						if( $( '#sliced_subscription_receipts' ).is(':checked') ) {
							$( '.sliced-receipts' ).show();
						}
						
						$( "#sliced_subscription_receipts" ).change(function() {
							if( $(this).is(':checked') ) {
								$('.sliced .sliced-invoices-subscriptions .sliced-receipts').slideDown();
							} else {
								$('.sliced .sliced-invoices-subscriptions .sliced-receipts').slideUp();
							}
						});
						
					});
				})( jQuery );
			</script>
			
		<?php
		#endregion admin_inline_js
	}
	
	/**
	 * Add subscription status icons in admin list.
	 *
	 * @since 1.0.0
	 */
	public function display_subscription_status_icon() {
		
		if ( sliced_get_the_type() !== 'invoice' ) {
			return;
		}
		
		$SS = Sliced_Subscriptions::get_instance();
		$id = sliced_get_the_id();
		
		$subscription = $SS->is_subscription_invoice( $id );
		
		if ( $subscription ) {
			
			echo '<span class="sliced_subscription_is_' . $subscription . '"></span>';
			
		}
		
	}
	
	/**
	 * Modify the query when only displaying subscriptions.
	 *
	 * @since 1.0.0
	 */
	public function filter_subscription( $vars ) {
		
		if (
			sliced_get_the_type() === 'invoice'
			&& isset( $_GET['invoice_type'] )
			&& $_GET['invoice_type'] === 'subscription'
		) {
			$vars = array_merge(
				$vars,
				array(
					'meta_query' => array(
						array(
							'key'     => '_sliced_subscription_status',
							'compare' => 'EXISTS',
						),
					),
				)
			);
		}
		
		return $vars;
	}
	
	/**
	 * Render Meta Box content.
	 *
	 * @since 1.0.0
	 */
	public function render_meta_box_content() {
		
		$SS = Sliced_Subscriptions::get_instance();
		$id = intval( $_GET['post'] );
		$subscription = $SS->is_subscription_invoice( $id );
		
		if ( $subscription ) {
		
			if ( $subscription === 'receipt' ) {
				// get parent data
				$id = wp_get_post_parent_id( $id );
				$subscription = $SS->is_subscription_invoice( $id );
			}
			
			$subscription_data = $SS->existing_settings_data( $id );
			
			$output = '<p class="sliced-subscription-status">' . __( 'Subscription status', 'sliced-invoices-subscriptions' ) . ': <span class="sliced-subscription-'.$subscription.'">';
			switch ( $subscription ) {
				case 'pending':
					$output .= __( 'Pending', 'sliced-invoices-subscriptions' );
					break;
				case 'active':
					$output .= __( 'Active', 'sliced-invoices-subscriptions' );
					break;
				case 'cancelled':
					$output .= __( 'Cancelled', 'sliced-invoices-subscriptions' );
					break;
			}
			$output .= '</span></p>';
			
			$output .= '<p class="sliced-subscription-info">' .
				'<strong>' . __( 'Terms:', 'sliced-invoices-subscriptions' ) . '</strong> ' .
				sprintf( __( '%1$s charged every %2$s %3$s %4$s', 'sliced-invoices-subscriptions' ),
					$subscription_data['amount'],
					$subscription_data['interval_number'],
					$SS->get_localized_interval_type( $subscription_data['interval_type'], $subscription_data['interval_number'] ),
					$SS->get_localized_duration( $subscription_data['cycles_type'], $subscription_data['cycles_count'] )
				) .
				'<br /><strong>' . __( 'Trial period:', 'sliced-invoices-subscriptions' ) . '</strong> ' .
				( $subscription_data['trial'] == "1" ?
					sprintf( __( '%1$s for %2$s %3$s (%4$s %5$s)', 'sliced-invoices-subscriptions' ),
						Sliced_Shared::get_formatted_currency( $subscription_data['trial_amount'] ),
						$subscription_data['trial_interval_number'],
						$SS->get_localized_interval_type( $subscription_data['trial_interval_type'], $subscription_data['trial_interval_number'] ),
						$subscription_data['trial_cycles_count'],
						_n( 'cycle', 'cycles', (int)$subscription_data['trial_cycles_count'], 'sliced-invoices-subscriptions' )
					)
					: __( 'no trial period', 'sliced-invoices-subscriptions' )
				) .
				( $subscription === 'active' ?
					'<br /><strong>' . __( 'Gateway:', 'sliced-invoices-subscriptions' ) . '</strong> ' .
					$subscription_data['gateway'] . ' ' . $subscription_data['gateway_subscr_id']
					: ''
				) .
				'</p>';
			
			// Buttons
			if ( $subscription !== 'cancelled' ) {
				$output .= '<a class="button button-small thickbox" href="#TB_inline?width=500&height=500&inlineId=sliced_subscription_settings" title="' . __( 'Subscription Invoice', 'sliced-invoices-subscriptions' ) . '">' . __( 'Edit Subscription', 'sliced-invoices-subscriptions' ) . '</a>';
				$cancel_url = add_query_arg( array( 'action' => 'cancel_subscription_invoice', 'parent_id' => $id, '_wpnonce' => wp_create_nonce( 'cancel_subscription' ) ), admin_url( 'admin.php' ) );
				$output .= '<a id="sliced_cancel_subscription" class="button button-small" href="' . esc_url( $cancel_url ) . '" title="">' . __( 'Cancel Subscription', 'sliced-invoices-subscriptions' ) . '</a> ';
			}
			
		} else {
			
			$output = '<a class="button button-large thickbox" href="#TB_inline?width=500&height=500&inlineId=sliced_subscription_settings" title="' . __( 'Subscription Invoice', 'sliced-invoices-subscriptions' ) . '">' . __( 'Create Subscription Invoice', 'sliced-invoices-subscriptions' ) . '</a>';
			
		}
		
		echo $output;
		
	}
	
	/**
	 * Render Help Meta Box content.
	 *
	 * @since 1.3.0
	 */
	public function render_meta_box_help() {
		
		$output = '<em>' . __( 'Subscription Invoice options will be displayed after you save this invoice.', 'sliced-invoices-subscriptions' ) . '</em>';
		
		echo $output;
		
	}
	
	/**
	 * Set the subscription invoice data.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 */
	public function start_subscription_invoice() {
		
		if ( sliced_get_the_type() !== 'invoice' ) {
			return;
		}
		
		if ( ! isset( $_POST['sliced_start_subscription_nonce'] ) ) {
			return;
		}
		
		if ( ! wp_verify_nonce( $_POST['sliced_start_subscription_nonce'], 'start_subscription_invoice' ) ) {
			wp_die( 'Oh no, there was an issue creating the subscription invoice.' );
		}
		
		$id = (int)$_POST['sliced_subscription_invoice_id'];
		
		$subscription_meta = array(
			'_sliced_subscription_status'                => 'pending',
			'_sliced_subscription_amount'                => sliced_get_invoice_total( $id ),
			'_sliced_subscription_amount_raw'            => sliced_get_invoice_total_raw( $id ),
			'_sliced_subscription_interval_number'       => intval( $_POST['sliced_subscription_interval_number'] ),
			'_sliced_subscription_interval_type'         => sanitize_text_field( $_POST['sliced_subscription_interval_type'] ),
			'_sliced_subscription_cycles_count'          => intval( $_POST['sliced_subscription_cycles_count'] ),
			'_sliced_subscription_cycles_type'           => sanitize_text_field( $_POST['sliced_subscription_cycles_type'] ),
			'_sliced_subscription_trial'                 => isset( $_POST['sliced_subscription_trial'] ) && $_POST['sliced_subscription_trial'] ? '1' : '',
			'_sliced_subscription_trial_interval_number' => intval( $_POST['sliced_subscription_trial_interval_number'] ),
			'_sliced_subscription_trial_interval_type'   => sanitize_text_field( $_POST['sliced_subscription_trial_interval_type'] ),
			'_sliced_subscription_trial_cycles_count'    => intval( $_POST['sliced_subscription_trial_cycles_count'] ),
			'_sliced_subscription_trial_amount'          => sanitize_text_field( $_POST['sliced_subscription_trial_amount'] ),
			'_sliced_subscription_receipts'              => isset( $_POST['sliced_subscription_receipts'] ) && $_POST['sliced_subscription_receipts'] ? '1' : '',
			'_sliced_subscription_receipts_auto_send'    => sanitize_text_field( $_POST['sliced_subscription_receipts_auto_send'] ),
		);
		
		if ( isset( $_POST['sliced_subscription_braintree_plan'] ) ) {
			$subscription_meta['_sliced_subscription_braintree_plan'] = sanitize_text_field( $_POST['sliced_subscription_braintree_plan'] );
		}
		
		foreach ( $subscription_meta as $key => $value ) {
			update_post_meta( $id, $key, $value );
		}
		
	}
	
	/**
	 * Admin notices.
	 *
	 * @version 1.3.6
	 * @since   1.0.0
	 */
	public function subscription_admin_notices() {
		
		global $pagenow;
		
		if ( sliced_get_the_type() !== 'invoice' ) {
			return;
		}
		
		if ( $pagenow === 'edit.php' && isset( $_GET['subscription'] ) && $_GET['subscription'] === 'created' ) {
			echo '<div class="updated">
				<p>' . __( 'Subscription invoice successfully created.', 'sliced-invoices-subscriptions' ) . '</p>
			</div>';
		}
		
		if ( $pagenow === 'edit.php' && isset( $_GET['subscription'] ) && $_GET['subscription'] === 'updated' ) {
			echo '<div class="updated">
				<p>' . __( 'Subscription invoice successfully updated.', 'sliced-invoices-subscriptions' ) . '</p>
			</div>';
		}
		
		if ( $pagenow === 'edit.php' && isset( $_GET['subscription'] ) && $_GET['subscription'] === 'cancelled' ) {
			echo '<div class="notice notice-info">
				<p>' . __( 'Subscription invoice cancelled.', 'sliced-invoices-subscriptions' ) . '</p>
			</div>';
		}
		
		if ( $pagenow === 'edit.php' && isset( $_GET['subscription'] ) && $_GET['subscription'] === 'error' ) {
			echo '<div class="notice notice-error">
				<p>' . __( 'Subscription error', 'sliced-invoices-subscriptions' ) . ': ' . urldecode( $_GET['message'] ) . '</p>
			</div>';
		}
		
	}
	
	/**
	 * Popup form to create the subscription invoice.
	 *
	 * @since 1.0.0
	 */
	public function subscription_settings_form() {
		#region subscription_settings_form
		global $pagenow;
		
		if ( $pagenow !== 'post.php' || sliced_get_the_type() !== 'invoice' ) {
			return;
		}
		
		$SS           = Sliced_Subscriptions::get_instance();
		$id           = intval( $_GET['post'] );
		$existing     = $SS->existing_settings_data( $id );
		$subscription = $SS->is_subscription_invoice( $id );
		
		if ( $subscription === 'active' || $subscription === 'pending' ) {
			$query_args = array( 'post_type' => 'sliced_invoice', 'subscription' => 'updated' );
		} else {
			$query_args = array( 'post_type' => 'sliced_invoice', 'subscription' => 'created' );
		}
		$admin_url = get_admin_url() . 'edit.php';
		
		?>
		<div id="sliced_subscription_settings" style="display:none;">
			
			<?php
			if ( $subscription === 'active' ):
				// update settings for active subscriptions
				?>
				
				<p><em><strong><?php _e( 'Friendly reminder:', 'sliced-invoices-subscriptions' ) ?></strong> <?php _e( 'Subscription terms cannot be changed once the subscription is active.  To change the subscription amount or schedule, you must first cancel this subscription, then start a new one.', 'sliced-invoices-subscriptions' ) ?></em></p>
				
				<p><em><?php _e( 'Any other settings can be changed below:', 'sliced-invoices-subscriptions' ) ?></em></p>
				
				<form method="POST" action="<?php echo esc_url( add_query_arg( $query_args, $admin_url ) ); ?>">
					
					<table class="form-table sliced-invoices-subscriptions">
						<tbody>
							
							<tr class="form-field">
								<th scope="row">
									<label><?php _e( 'Enable receipt invoices?', 'sliced-invoices-subscriptions' ) ?> </label>
								</th>
								<td>
									<input type="checkbox" name="sliced_subscription_receipts" id="sliced_subscription_receipts" value="1"  <?php echo $existing['receipts'] == '1' ? 'checked="checked"' : ''; ?> />
								</td>
							</tr>
							
							<tr class="form-field sliced-receipts">
								<th scope="row">
									<label><?php _e( 'Automatically send to client?', 'sliced-invoices-subscriptions' ) ?> </label>
								</th>
								<td>
									<select name="sliced_subscription_receipts_auto_send" id="sliced_subscription_receipts_auto_send">
										<option value="yes" <?php selected( $existing['receipts_auto_send'], 'yes' ); ?>><?php _e( 'Yes', 'sliced-invoices-subscriptions' ) ?></option>
										<option value="no" <?php selected( $existing['receipts_auto_send'], 'no' ); ?>><?php _e( 'No', 'sliced-invoices-subscriptions' ) ?></option>
									</select>
								</td>
							</tr>
							
						</tbody>
					</table>
					
					<input type="hidden" name="sliced_subscription_invoice_id" value="<?php echo (int)$_GET['post'] ?>" />
					
					<?php wp_nonce_field( 'update_subscription_invoice', 'sliced_update_subscription_nonce' ); ?>
					<p><input class="button button-primary button-large" type="submit" name="" value="<?php _e( 'Update Subscription', 'sliced-invoices-subscriptions' ) ?>"></p>
					
				</form>
				
			<?php
			else:
				// create new subscription settings, or update only if still in "pending" status
				?>
				
				<p><em><strong><?php _e( 'Important!', 'sliced-invoices-subscriptions' ) ?></strong> <?php _e( 'Please ensure that any changes to this invoice are saved before creating the subscription.', 'sliced-invoices-subscriptions' ) ?></em></p>
				
				<form method="POST" action="<?php echo esc_url( add_query_arg( $query_args, $admin_url ) ); ?>">
					
					<table class="form-table sliced-invoices-subscriptions">
						<tbody>
							
							<tr class="form-field">
								<th scope="row">
									<label><?php _e( 'Charge every...', 'sliced-invoices-subscriptions' ) ?> </label>
								</th>
								<td>
									<input class="interval_days" type="text" name="sliced_subscription_interval_number" id="sliced_subscription_interval_number" value="<?php echo $existing['interval_number'] ? $existing['interval_number'] : '30' ?>" />
									<select name="sliced_subscription_interval_type" id="sliced_subscription_interval_type">
										<option value="days" <?php echo $existing['interval_type'] == 'days' ? 'selected="selected"' : ''; ?>><?php _e( 'Day(s)', 'sliced-invoices-subscriptions' ) ?></option>
										<option value="months" <?php echo $existing['interval_type'] == 'months' ? 'selected="selected"' : ''; ?>><?php _e( 'Month(s)', 'sliced-invoices-subscriptions' ) ?></option>
										<option value="years" <?php echo $existing['interval_type'] == 'years' ? 'selected="selected"' : ''; ?>><?php _e( 'Year(s)', 'sliced-invoices-subscriptions' ) ?></option>
									</select>
								</td>
							</tr>
							
							<tr class="form-field">
								<th scope="row">
									<label><?php _e( 'Number of payments', 'sliced-invoices-subscriptions' ) ?> </label>
								</th>
								<td>
									<input type="radio" name="sliced_subscription_cycles_type" value="infinite" <?php echo $existing['cycles_type'] != 'fixed' ? 'checked="checked"' : ''; ?> /> <?php _e( 'Continue until cancelled', 'sliced-invoices-subscriptions' ) ?><br />
									<input type="radio" name="sliced_subscription_cycles_type" value="fixed" <?php echo $existing['cycles_type'] == 'fixed' ? 'checked="checked"' : ''; ?> /> <?php _e( 'Fixed term:', 'sliced-invoices-subscriptions' ) ?>
									<input class="cycles_count" type="text" name="sliced_subscription_cycles_count" value="<?php echo $existing['cycles_count'] ? $existing['cycles_count'] : '12' ?>" /> <?php _e( 'payments', 'sliced-invoices-subscriptions' ) ?><br />
								</td>
							</tr>
							
							<tr class="form-field">
								<th scope="row">
									<label><?php _e( 'Enable Trial?', 'sliced-invoices-subscriptions' ) ?> </label>
								</th>
								<td>
									<input type="checkbox" name="sliced_subscription_trial" id="sliced_subscription_trial" value="1"  <?php echo $existing['trial'] == '1' ? 'checked="checked"' : ''; ?> />
								</td>
							</tr>
							
							<tr class="form-field sliced-trial">
								<th scope="row">
									<label><?php _e( 'Trial cycle duration', 'sliced-invoices-subscriptions' ) ?> </label>
								</th>
								<td>
									<input class="interval_days" type="text" name="sliced_subscription_trial_interval_number" id="sliced_subscription_trial_interval_number" value="<?php echo $existing['trial_interval_number'] ? $existing['trial_interval_number'] : '14' ?>" />
									<select name="sliced_subscription_trial_interval_type" id="sliced_subscription_trial_interval_type">
										<option value="days" <?php echo $existing['trial_interval_type'] == 'days' ? 'selected="selected"' : ''; ?>><?php _e( 'Day(s)', 'sliced-invoices-subscriptions' ) ?></option>
										<option value="months" <?php echo $existing['trial_interval_type'] == 'months' ? 'selected="selected"' : ''; ?>><?php _e( 'Month(s)', 'sliced-invoices-subscriptions' ) ?></option>
										<option value="years" <?php echo $existing['trial_interval_type'] == 'years' ? 'selected="selected"' : ''; ?>><?php _e( 'Year(s)', 'sliced-invoices-subscriptions' ) ?></option>
									</select>
								</td>
							</tr>
							
							<tr class="form-field sliced-trial">
								<th scope="row">
									<label><?php _e( 'Trial number of cycles', 'sliced-invoices-subscriptions' ) ?> </label>
								</th>
								<td>
									<input class="trial_cycles_count" type="text" name="sliced_subscription_trial_cycles_count" id="sliced_subscription_trial_cycles_count" value="<?php echo $existing['trial_cycles_count'] ? $existing['trial_cycles_count'] : '1' ?>" /> <?php _e( 'Cycle(s)', 'sliced-invoices-subscriptions' ) ?><br />
								</td>
							</tr>
							
							<tr class="form-field sliced-trial">
								<th scope="row">
									<label><?php _e( 'Trial amount', 'sliced-invoices-subscriptions' ) ?> </label>
								</th>
								<td>
									<input type="text" name="sliced_subscription_trial_amount" id="sliced_subscription_trial_amount" value="<?php echo $existing['trial_amount'] ? $existing['trial_amount'] : '0' ?>" />
								</td>
							</tr>
							
							<tr class="form-field">
								<th scope="row">
									<label><?php _e( 'Enable receipt invoices?', 'sliced-invoices-subscriptions' ) ?> </label>
								</th>
								<td>
									<input type="checkbox" name="sliced_subscription_receipts" id="sliced_subscription_receipts" value="1"  <?php echo $existing['receipts'] == '1' ? 'checked="checked"' : ''; ?> />
								</td>
							</tr>
							
							<tr class="form-field sliced-receipts">
								<th scope="row">
									<label><?php _e( 'Automatically send to client?', 'sliced-invoices-subscriptions' ) ?> </label>
								</th>
								<td>
									<select name="sliced_subscription_receipts_auto_send" id="sliced_subscription_receipts_auto_send">
										<option value="yes" <?php selected( $existing['receipts_auto_send'], 'yes' ); ?>><?php _e( 'Yes', 'sliced-invoices-subscriptions' ) ?></option>
										<option value="no" <?php selected( $existing['receipts_auto_send'], 'no' ); ?>><?php _e( 'No', 'sliced-invoices-subscriptions' ) ?></option>
									</select>
								</td>
							</tr>
							
						</tbody>
					</table>
					
					<input type="hidden" name="sliced_subscription_invoice_id" value="<?php echo (int)$_GET['post'] ?>" />
					
					<?php wp_nonce_field( 'start_subscription_invoice', 'sliced_start_subscription_nonce' ); ?>
					<?php if ( $subscription === 'pending' ): ?>
						<p><input class="button button-primary button-large" type="submit" name="" value="<?php _e( 'Update Subscription', 'sliced-invoices-subscriptions' ) ?>"></p>
					<?php else: ?>
						<p><input class="button button-primary button-large" type="submit" name="" value="<?php _e( 'Create Subscription', 'sliced-invoices-subscriptions' ) ?>"></p>
					<?php endif; ?>
					
				</form>
				
			<?php endif; ?>
			
		</div>
		
		<?php
		#endregion subscription_settings_form
	}
	
	/**
	 * Update the subscription invoice data.
	 *
	 * @since 1.3.0
	 */
	public function update_subscription_invoice() {
		
		if ( sliced_get_the_type() !== 'invoice' ) {
			return;
		}
		
		if ( ! isset( $_POST['sliced_update_subscription_nonce'] ) ) {
			return;
		}
		
		if ( ! wp_verify_nonce( $_POST['sliced_update_subscription_nonce'], 'update_subscription_invoice' ) ) {
			wp_die( 'Oh no, there was an issue updating the subscription invoice.' );
		}
		
		$id = (int)$_POST['sliced_subscription_invoice_id'];
		
		$subscription_meta = array(
			'_sliced_subscription_receipts'           => esc_html( $_POST['sliced_subscription_receipts'] ),
			'_sliced_subscription_receipts_auto_send' => esc_html( $_POST['sliced_subscription_receipts_auto_send'] ),
		);
		
		foreach ( $subscription_meta as $key => $value ) {
			update_post_meta( $id, $key, $value );
		}
		
	}
	
}
