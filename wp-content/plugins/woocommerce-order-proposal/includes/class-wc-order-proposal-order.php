<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'WC_Order_Proposal_Order' ) ) {

	class WC_Order_Proposal_Order {

		/**
		 * @var int
		 */
		private $order_id;

		public function __construct() {
			// Create a new order box
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );

			// Save the shop order data
			add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_meta_box' ), 10, 2 );

			// Change time on order change
			add_action( 'woocommerce_order_status_changed', array( $this, 'wc_order_proposal_change_date_if_necessary' ), 1, 3 );

			add_filter( 'woocommerce_order_actions', array( $this, 'wc_order_proposal_order_action' ), 10, 1 );
			add_action( 'woocommerce_order_action_send_order_proposal', array( $this,'wc_order_proposal_action_send_order_proposal' ), 10, 1 );
			add_action( 'woocommerce_order_action_send_order_confirmation', array( $this, 'wc_order_proposal_action_send_order_confirmation' ), 10, 1 );

			// Increase stock when order was cancelled
			add_action( 'woocommerce_order_status_cancelled', array( $this, 'wc_order_proposal_increase_order_stock' ), 10, 1 );

			// Add reduce stock action button
			add_action( 'woocommerce_order_item_add_action_buttons', array( $this, 'order_proposal_order_item_add_action_buttons' ), 10, 1 );

			// Add button to the my account page
			add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'add_accept_proposal_my_account_orders_status' ), 9999, 2 );
			add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'add_decline_proposal_my_account_orders_status' ), 9999, 2 );

			// Add woocommerce options
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
			add_action( 'woocommerce_settings_tabs_wpo_wcop_settings_tab', array( $this, 'settings_tab' ) );
			add_action( 'woocommerce_update_options_wpo_wcop_settings_tab', array( $this, 'update_settings' ) );
			add_filter( 'plugin_action_links_' . wc_order_proposal()->plugin_basename, array( $this, 'settings_link' ) );

			// Allow paying from order proposal status
			add_filter( 'woocommerce_valid_order_statuses_for_payment', array( $this, 'wc_order_proposal_pay_by_link' ), 10, 2 );
			add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', array( $this, 'wc_order_proposal_payment_complete' ), 10, 2 );

			// Prevent reduce stock
			add_action( 'woocommerce_order_status_changed', array( $this, 'wc_order_proposal_order_status_changed' ), 10, 3 );
			if ( wc_order_proposal_no_reduce_stock() ) {
				if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.8', '>=' ) ) {
					add_filter( 'woocommerce_prevent_adjust_line_item_product_stock', array( $this, 'wc_order_proposal_do_not_reduce_stock' ), 100, 3 );
					add_filter( 'woocommerce_product_get_manage_stock', array( $this, 'wc_order_proposal_do_not_reduce_stock_old' ), 100 );
					add_filter( 'woocommerce_product_variation_get_manage_stock', array( $this, 'wc_order_proposal_do_not_reduce_stock_old' ), 100 );
				} elseif ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.6', '>=' ) ) {
					add_filter( 'woocommerce_product_get_manage_stock', array( $this, 'wc_order_proposal_do_not_reduce_stock_old' ), 100 );
					add_filter( 'woocommerce_product_variation_get_manage_stock', array( $this, 'wc_order_proposal_do_not_reduce_stock_old' ), 100 );
				}
			}

			// Hide cancelled and failed proposals on my account page
			add_filter( 'woocommerce_my_account_my_orders_query', array( $this, 'wc_order_proposal_my_account_my_orders_query' ), 10, 1 );

			// Rename an order to proposal
			add_action( 'woocommerce_before_template_part', array( $this, 'wc_order_proposal_rename_order' ), 10, 4 );

			// Show button in order detail page
			add_action( 'woocommerce_order_details_after_order_table', array( $this, 'wc_order_proposal_order_detail_button' ), 1, 1 );

			// Allow payment without login
			add_action( 'before_woocommerce_pay', array( $this, 'wc_order_proposal_allow_payment_without_login' ) );

			// Allow Proposal payment instructions when using BACS or Cheque
			add_filter( 'woocommerce_bacs_email_instructions_order_status', array( $this, 'wc_order_proposal_payment_email_instructions' ), 10, 2 );
			add_filter( 'woocommerce_cheque_email_instructions_order_status', array( $this, 'wc_order_proposal_payment_email_instructions' ), 10, 2 );

			add_action( 'woocommerce_email_after_order_table', array( $this, 'email_payment_instructions' ), 10, 4 );
		}

		/**
		 * Hide cancelled and failed proposals on my account page
		 * WP action: woocommerce_my_account_my_orders_query
		 */
		public function wc_order_proposal_my_account_my_orders_query( $args ) {
			$hide = get_option( WC_Order_Proposal::ORDER_PROPOSAL_HIDE_PROPOSALS, 'no' );

			if ( 'yes' === $hide ) {
				$orders = wc_get_orders( array(
					'customer' => get_current_user_id(),
					'return'   => 'ids',
					'limit'    => -1,
				) );

				$exclude_orders = array();

				foreach ( $orders as $order_id ) {
					if ( wc_order_proposal_order_has_proposal( $order_id ) ) {
						$order = wc_get_order( $order_id );
						if ( $order ) {
							$status = $order->get_status();
							if ( ( 'cancelled' === $status ) || ( 'failed' === $status ) ) {
								$exclude_orders[] = $order_id;
							}
						}
					}
				}

				$args['exclude'] = $exclude_orders;
			}

			return $args;
		}

		/**
		 * Prevent reduce stock
		 * WP action: woocommerce_order_status_changed
		 */
		public function wc_order_proposal_order_status_changed( $order_id, $old_status, $new_status ) {
			$order = wc_get_order( $order_id );
			if ( wc_order_proposal_order_has_proposal( $order_id ) && wc_order_proposal_reserve_stock_manually() && $order->get_meta( '_order_stock_reduced' ) ) {
				add_filter( 'woocommerce_can_reduce_order_stock', '__return_false' );
			}
		}

		/**
		 * Prevent reduce stock on single order items since WC 3.8.0
		 * WP filter: woocommerce_prevent_adjust_line_item_product_stock
		 */
		public function wc_order_proposal_do_not_reduce_stock( $value, $item, $item_quantity ) {
			$order = $item->get_order();

			if ( $order && ( $order->has_status( 'pending' ) || $order->has_status( 'auto-draft' ) || $order->has_status( 'draft' ) || $order->has_status( 'order-proposal' ) || $order->has_status( 'order-proposalreq' ) ) ) {
				return true;
			}

			return $value;
		}

		/**
		 * Prevent reduce stock on single order items since WC 3.6 until 3.8. This is hacky.
		 * WP filter: woocommerce_product_get_manage_stock
		 */
		public function wc_order_proposal_do_not_reduce_stock_old( $value ) {
			if ( is_admin() && isset( $_REQUEST['order_id'] ) ) {
				$order_id = intval( $_REQUEST['order_id'] );
				$order    = wc_get_order( $order_id );

				if ( $order && ( $order->has_status( 'pending' ) || $order->has_status( 'auto-draft' ) || $order->has_status( 'draft' ) || $order->has_status( 'order-proposal' ) || $order->has_status( 'order-proposalreq' ) ) ) {
					return 0;
				}
			}

			return $value;
		}

		/**
		 * Increase stock when order was cancelled
		 * WP action: woocommerce_order_status_cancelled
		 */
		public function wc_order_proposal_increase_order_stock( $order_id ) {
			wc_order_proposal_increase_order_stock( $order_id );
		}

		/**
		 * Resend email fron order page
		 * WP filter: woocommerce_order_actions
		 */
		public function wc_order_proposal_order_action( $actions ) {
			$actions['send_order_proposal']     = __( 'Email order proposal to customer', 'woocommerce-order-proposal' );
			$actions['send_order_confirmation'] = __( 'Email order confirmation to customer', 'woocommerce-order-proposal' );

			return $actions;
		}

		/**
		 * Generate EMail
		 * WP filter: woocommerce_order_action_customer_order_details
		 */
		public function wc_order_proposal_action_send_order_proposal( $order ) {
			WC()->payment_gateways();
			WC()->shipping();
			# Allow for sending even when not enabled
			add_filter( 'woocommerce_email_enabled_order_proposal', '__return_true' );
			WC()->mailer()->emails['WC_Email_Order_Proposal']->trigger( $order->get_id() );
			remove_filter( 'woocommerce_email_enabled_order_proposal', '__return_true' );

			# Transition to order proposal from request
			if ( $order->get_status() == 'order-proposalreq' ) {
				$emails = WC_Emails::instance()->emails;
				# Do not send the email again
				remove_action( 'woocommerce_order_status_order-proposalreq_to_order-proposal_notification', array( $emails['WC_Email_Order_Proposal'], 'trigger' ), 10, 2 );

				// Set order status to order-proposal
				$order->update_status( 'wc-order-proposal', __( 'Order Proposal', 'woocommerce' ) );
			}
		}

		/**
		 * Generate EMail
		 * WP filter: woocommerce_order_action_customer_order_details
		 */
		public function wc_order_proposal_action_send_order_confirmation( $order ) {
			WC()->payment_gateways();
			WC()->shipping();
			WC()->mailer()->emails['WC_Email_Order_Confirmation']->trigger( $order->get_id() );
		}

		/**
		 * Pay by link
		 * WP filter: woocommerce_valid_order_statuses_for_payment
		 */
		public function wc_order_proposal_pay_by_link( $statuses, $order ) {
			global $wp;

			if ( isset( $wp->query_vars['order-pay'] ) ) {
				$order_id = absint( $wp->query_vars['order-pay'] );
			}

			// Handle payment
			if ( ( isset( $_GET['pay_for_order'] ) || isset( $_POST['woocommerce_pay'] ) ) && $order_id && $order_id == $order->get_id() ) {
				if ( $order && wc_order_proposal_order_has_proposal( $order->get_id() ) ) {
					//add filter so we can pay via link
					$statuses[] = "order-proposal";
				}
			}

			return $statuses;
		}

		/**
		 * Payment status to be eligible for payment complete
		 * WP action: woocommerce_order_details_after_order_table
		 */
		public function wc_order_proposal_order_detail_button( $order ) {
			if ( $order && wc_order_proposal_order_has_proposal( $order->get_id() ) && ( $order->has_status( 'order-proposal' ) ) ) {
				echo '<button class="button wc-order-proposal-reject-button" style="float: right; margin-top: 20px; margin-left: 20px;" onclick="window.location.href=\'' . wp_nonce_url( admin_url( 'admin-ajax.php?action=mark_order_as_declined_proposal&order_id=' . $order->get_id() ), 'mark_order_as_declined_proposal' ) . '\'">' . apply_filters( 'wc_order_proposal_order_reject_button_text', esc_html__( 'Decline Proposal', 'woocommerce-order-proposal' ) ) . '</button>';
				echo '<button class="button wc-order-proposal-accept-button" style="float: right; margin-top: 20px;" onclick="window.location.href=\'' . wp_nonce_url( admin_url( 'admin-ajax.php?action=mark_order_as_accept_proposal&order_id=' . $order->get_id() ), 'mark_order_as_accept_proposal' ) . '\'">' . apply_filters( 'wc_order_proposal_order_accept_button_text', esc_html__( 'Accept Proposal', 'woocommerce-order-proposal' ) ) . '</button>';
			}

			if ( $order && $order->has_status( 'pending' ) ) {
				echo '<button class="button wc-order-proposal-pay-button" style="float: right; margin-top: 20px;" onclick="window.location.href=\'' . esc_url( $order->get_checkout_payment_url() ) . '\'">' . apply_filters( 'wc_order_proposal_order_payment_button_text', esc_html__( 'Pay for this order', 'woocommerce' ) ) . '</button>';
			}
		}

		/**
		 * Payment status to be eligible for payment complete
		 * WP filter: woocommerce_valid_order_statuses_for_payment_complete
		 */
		public function wc_order_proposal_payment_complete( $statuses, $order ) {
			if ( $order && wc_order_proposal_order_has_proposal( $order->get_id() ) ) {
				//add filter so we can pay via link
				$statuses[] = "order-proposal";
			}

			return $statuses;
		}

		/**
		 * Add meta box to the order overview page
		 */
		public function add_meta_boxes( $post_type, $post ) {
			$screen = wc_order_proposal()->order_util->custom_order_table_screen();

			if ( wc_order_proposal()->order_util->custom_orders_table_usage_is_enabled() && $post_type === $screen ) {
				$post_id = $post->get_id();
			} else {
				$post_id = $post->ID;
			}

			if ( wc_order_proposal()->order_util->get_order_type( $post_id ) === 'shop_order' && ! empty( $order = wc_get_order( $post_id ) ) ) {
				add_meta_box(
					'wc_order_proposal',
					__( 'Order Proposal Options', 'woocommerce-order-proposal' ),
					array( $this, 'wc_order_proposal_meta_box' ),
					$screen,
					'side',
					'default',
					[ 'order' => $order ]
				);
			}
		}

		/**
		 * Add reduce stock action button
		 * WP filter: woocommerce_order_item_add_action_buttons
		 */
		public function order_proposal_order_item_add_action_buttons( $order ) {
			if ( $order ) {
				if ( wc_order_proposal_order_has_proposal( $order->get_id() ) && wc_order_proposal_reserve_stock_manually() && ! $order->get_meta( '_order_stock_reduced' ) ) {
					echo '<button class="button button-primary order_proposal_reduce_stock">' . __( 'Reduce Stock (Order Proposal)', 'woocommerce-order-proposal' ) . '</button>';
				}
			}
		}

		public function add_settings_tab( $settings_tabs ) {
			$settings_tabs['wpo_wcop_settings_tab'] = __( 'Order Proposal', 'woocommerce-order-proposal' );

			return $settings_tabs;
		}

		public function settings_tab() {
			woocommerce_admin_fields( $this->get_settings() );
		}

		public function update_settings() {
			woocommerce_update_options( $this->get_settings() );
		}

		public function get_settings() {
			$settings = array(
				array(
					'title' => __( 'Order Proposal', 'woocommerce-order-proposal' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'order_proposal_options',
				),
				array(
					'name'     => __( 'Default Proposal Valid Days', 'woocommerce-order-proposal' ),
					'desc_tip' => __( 'The Default Proposal Valid Days for the Order Proposal Plugin.', 'woocommerce-order-proposal' ),
					'id'       => 'woocommerce_order_proposal_default_proposal_valid_days',
					'type'     => 'number',
					'css'      => 'width:60px;',
					'default'  => '14',
				),
				array(
					'name'     => __( 'Cancel Expired Proposals', 'woocommerce-order-proposal' ),
					'desc_tip' => __( 'Cancel Orders with expired proposal date.', 'woocommerce-order-proposal' ),
					'id'       => WC_Order_Proposal::ORDER_PROPOSAL_CANCEL_EXPIRED,
					'type'     => 'checkbox',
					'default'  => 'yes',
				),
				array(
					'name'     => __( 'Disable Stock Management', 'woocommerce-order-proposal' ),
					'desc_tip' => __( 'Do not reduce stock for proposals when they are created. Stock will only be reduced when the customer pays for the order or it is completed. Disables also the stock increasing.', 'woocommerce-order-proposal' ),
					'id'       => WC_Order_Proposal::ORDER_PROPOSAL_NO_REDUCE_STOCK,
					'type'     => 'checkbox',
					'default'  => 'yes',
				),
				array(
					'name'     => __( 'Default Prepay Option in Order Proposals', 'woocommerce-order-proposal' ),
					'desc_tip' => __( 'The Default Prepay Option for the Order Proposal Plugin.', 'woocommerce-order-proposal' ),
					'id'       => WC_Order_Proposal::ORDER_PROPOSAL_DEFAULT_PREPAY,
					'type'     => 'checkbox',
					'default'  => 'no',
				),
				array(
					'name'     => __( 'Reserve Stock Button in Order Proposals', 'woocommerce-order-proposal' ),
					'desc_tip' => __( 'Adds a button to the order for manual stock reduction. This option will prevent automatic stock reduction on the Gateway.', 'woocommerce-order-proposal' ),
					'id'       => WC_Order_Proposal::ORDER_PROPOSAL_RESERVE_STOCK,
					'type'     => 'checkbox',
					'default'  => 'no',
				),
				array(
					'name'     => __( 'Hide Cancelled/Failed Proposals', 'woocommerce-order-proposal' ),
					'desc_tip' => __( 'Hide Cancelled and Failed Proposals for Customers on their My Account page.', 'woocommerce-order-proposal' ),
					'id'       => WC_Order_Proposal::ORDER_PROPOSAL_HIDE_PROPOSALS,
					'type'     => 'checkbox',
					'default'  => 'no',
				),
				array(
					'name'     => __( 'Pay without login', 'woocommerce-order-proposal' ),
					'desc_tip' => __( 'Allow users to pay Order Proposals without login.', 'woocommerce-order-proposal' ),
					'id'       => WC_Order_Proposal::ORDER_PROPOSAL_PAY_NO_LOGIN,
					'type'     => 'checkbox',
					'default'  => 'yes',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'order_proposal_options'
				),
			);

			return apply_filters( 'wpo_wcop_settings', $settings );
		}

		/**
		 * Adds a plugin settings page link to the plugins list
		 */
		public function settings_link( $links ) {
			$links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=wpo_wcop_settings_tab' ) . '">' . __( 'Settings', 'woocommerce-ultimate-barcodes' ) . '</a>';
			return $links;
		}

		/**
		 * Renders the meta box on the order overview page
		 */
		public function wc_order_proposal_meta_box( $post_or_order_object ) {
			$order = ( $post_or_order_object instanceof \WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

			$default_date       = wc_order_proposal_format_date( wc_order_proposal_get_date( $order->get_id() ), 'Y-m-d' );
			$default_start_date = wc_order_proposal_format_date( wc_order_proposal_get_start_date( $order->get_id() ), 'Y-m-d' );

			echo '<div id="wc_order_proposal_box">';
			echo '<div class="order_proposal_output" style="display: none;"></div>';

			woocommerce_wp_text_input( array(
				'id'                => 'wc_order_proposal_start_time',
				'label'             => __( 'Proposal Valid From:', 'woocommerce-order-proposal' ),
				'placeholder'       => esc_attr( $default_start_date ),
				'description'       => '',
				'class'             => 'date-picker-field',
				'value'             => esc_attr( $default_start_date ),
				'custom_attributes' => array(
					'pattern' => apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ),
				),
			) );

			woocommerce_wp_text_input( array(
				'id'                => 'wc_order_proposal_time',
				'label'             => __( 'Proposal Valid Until:', 'woocommerce-order-proposal' ),
				'placeholder'       => esc_attr( $default_date ),
				'description'       => '',
				'class'             => 'date-picker-field',
				'value'             => esc_attr( $default_date ),
				'custom_attributes' => array(
					'pattern' => apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ),
				),
			) );

			woocommerce_wp_checkbox( array(
				'id'    => 'wc_order_proposal_prepay',
				'label' => __( 'Print we require a prepayment text on the proposal?', 'woocommerce-order-proposal' ),
			) );

			do_action( 'wc_order_proposal_meta_box_end', $order->get_id() );

			echo '<button class="button button-primary button-save-form">' . __( 'Save Settings', 'woocommerce-order-proposal' ) . '</button>';

			echo '</div>';

			$translate_proposal = __( 'Proposal', 'woocommerce-order-proposal' );

			$js = "jQuery('#wc_order_proposal_box').data('proposaltranslate', \"$translate_proposal\");";

			// We have the default time set so we used it
			if ( wc_order_proposal_order_has_proposal( $order->get_id() ) ) {
				$js .= "
			$('#wc_order_proposal').show();
			jQuery('#wc_order_proposal_box').data('enabled', 'yes');
			";
			} else {
				$js .= "
			$('#wc_order_proposal').hide();
			jQuery('#wc_order_proposal_box').data('enabled', 'no');
			";
			}

			if ( wc_order_proposal_get_prepay( $order->get_id() ) ) {
				$js .= "
			$('#wc_order_proposal #wc_order_proposal_prepay').attr('checked', 'checked');
			";
			}

			// Add our dynamic js
			if ( function_exists( 'wc_enqueue_js' ) ) {
				wc_enqueue_js( $js );
			} else {
				$woocommerce->add_inline_js( $js );
			}

			// Add admin javascript file
			wp_enqueue_script( 'wc_order_proposal-admin-js', wc_order_proposal()->get_plugin_url() . 'assets/js/admin.js', array( 'jquery' ) );
			wp_localize_script( 'wc_order_proposal-admin-js', 'wpo_wcop', array(
					'save_nonce'         => wp_create_nonce( 'wpo_wcop_save-form' ),
					'lang_nonce'         => wp_create_nonce( 'wpo_wcop_save-lang' ),
					'reduce_stock_nonce' => wp_create_nonce( 'wpo_wcop_reduce_stock' ),
					'save_data'          => apply_filters( 'wc_order_proposal_js_extra_data', array() ),
				)
			);
		}


		/**
		 * Saves the data from our meta box on the save entire order button
		 * WP action: woocommerce_process_shop_order_meta
		 */
		public function save_meta_box( $post_id, $post ) {
			if ( empty( $post_id ) ) {
				return;
			}

			if ( wc_order_proposal()->order_util->get_order_type( $post_id ) === 'shop_order' && ! empty( $order = wc_get_order( $post_id ) ) ) {
				if ( isset( $_POST['wc_order_proposal_time'] ) && isset( $_POST['wc_order_proposal_start_time'] ) && isset( $_POST['order_status'] ) && ( $_POST['order_status'] == "wc-order-proposal" || wc_order_proposal_order_has_proposal( $post_id ) ) ) {

					// Download data
					$order_start_time = wc_clean( $_POST['wc_order_proposal_start_time'] );
					$order_time       = wc_clean( $_POST['wc_order_proposal_time'] );
					$order_prepay     = "false";

					if ( isset( $_POST['wc_order_proposal_prepay'] ) ) {
						$order_prepay = "true";
					}

					// Check if the variable is number
					if ( wc_order_proposal_validate_date( $order_time ) ) {
						wc_order_proposal_save_time( $post_id, $order_start_time, $order_time, $order_prepay );
					}

					do_action( 'wc_order_proposal_save_meta_box', $post_id, $order );
				}
			}
		}


		/**
		 * Order Proposal change date when status changes to other than "Cancel" or "Failed" and if not already changed
		 * WP filter: woocommerce_order_status_changed
		 */
		public function wc_order_proposal_change_date_if_necessary( $order_id, $old_status, $new_status ) {

			$order = wc_get_order( $order_id );

			$is_proposal       = wc_order_proposal_order_has_proposal( $order_id );

			$excluded_statuses = array(
				"cancelled",
				"failed",
				"order-proposal",
			);

			if ( ! $is_proposal || in_array( $new_status, $excluded_statuses ) || wc_order_proposal_date_changed( $order_id ) ) {
				return;
			}

			// Save old time
			wc_order_proposal_save_order_old_time( $order_id );

			// Set order proposal date changed
			wc_order_proposal_set_date_changed( $order_id, true );

			$this->order_id = $order_id;

			// Change time
			wc_order_proposal_change_time( $order_id );
			// Reload Order to remove old time in cache
			wc_get_order( $order_id );
		}


		/**
		 * Order Proposal Add Button to Accept proposal to my account page
		 * WP filter: woocommerce_my_account_my_orders_actions
		 */
		public function add_accept_proposal_my_account_orders_status( $actions, $order ) {
			if ( $order->has_status( 'order-proposal' ) ) {
				$actions['accept-proposal'] = array(
					'url'  => wp_nonce_url( admin_url( 'admin-ajax.php?action=mark_order_as_accept_proposal&order_id=' . $order->get_id() ), 'mark_order_as_accept_proposal' ),
					'name' => __( 'Accept Proposal', 'woocommerce-order-proposal' )
				);
			}

			return $actions;
		}


		/**
		 * Order Proposal Add Button to decline proposal to my account page
		 * WP filter: woocommerce_my_account_my_orders_actions
		 */
		public function add_decline_proposal_my_account_orders_status( $actions, $order ) {
			if ( $order->has_status( 'order-proposal' ) ) {
				$actions['decline-proposal'] = array(
					'url'  => wp_nonce_url( admin_url( 'admin-ajax.php?action=mark_order_as_declined_proposal&order_id=' . $order->get_id() ), 'mark_order_as_declined_proposal' ),
					'name' => __( 'Decline Proposal', 'woocommerce-order-proposal' )
				);
			}

			return $actions;
		}

		/**
		 * Hook in the function to rename an order to proposal
		 * WP action: woocommerce_before_template_part
		 */
		public function wc_order_proposal_rename_order( $template_name, $template_path, $located, $args ) {
			if ( $template_name == 'checkout/thankyou.php' ) {
				$order = $args['order'];

				if (
					is_a( $order, 'WC_Order' ) &&
					wc_order_proposal_order_has_proposal( $order->get_id() ) &&
					in_array( $order->get_status(), array( 'order-proposal', 'order-proposalreq' ) )
				) {
					add_filter( 'gettext', array( $this, 'wc_order_proposal_rename_order_string' ), 10, 3 );
					add_filter( 'ngettext', array( $this, 'wc_order_proposal_rename_order_string' ), 10, 3 );
					add_filter( 'woocommerce_thankyou_order_received_text', array( $this, 'wc_order_proposal_thankyou_order_received_text' ), 10, 2 );
				}
			}
		}

		/**
		 * Change the string translation for order to proposal
		 */
		public function wc_order_proposal_rename_order_string( $translation, $text, $domain ) {
			if ( 'woocommerce' === $domain ) {
				switch ( $text ) {
					case 'Order Complete':
						$translation = __( 'Proposal Complete', 'woocommerce-order-proposal' );
						break;
					case 'Order number':
						$translation = __( 'Proposal number', 'woocommerce-order-proposal' );
						break;
					case 'Order details':
						$translation = __( 'Proposal details', 'woocommerce-order-proposal' );
						break;
				}
			}

			return $translation;
		}

		/**
		 * Change the order proposal thank you text
		 */
		public function wc_order_proposal_thankyou_order_received_text( $text, $order ) {
			return __( 'Thank you. Your order proposal has been received.', 'woocommerce-order-proposal' );
		}

		/**
		 * Pay by link cleanups
		 * WP action: before_woocommerce_pay
		 */
		public function wc_order_proposal_allow_payment_without_login() {
			global $wp;

			if ( ! wc_order_proposal_pay_without_login() ) {
				return;
			}

			$order_id = absint( $wp->query_vars['order-pay'] );

			if ( isset( $_GET['pay_for_order'], $_GET['key'] ) && $order_id ) { // WPCS: input var ok, CSRF ok.
				try {
					$order_key = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : ''; // WPCS: input var ok, CSRF ok.
					$order     = wc_get_order( $order_id );

					// Order or payment link is invalid.
					if ( ! $order || $order->get_id() !== $order_id || ! hash_equals( $order->get_order_key(), $order_key ) ) {
						return;
					}

					// Logged out customer does not have permission to pay for this order.
					if ( $order->get_user_id() > 0 && ! is_user_logged_in() ) {
						wp_clear_auth_cookie();
						wp_set_current_user( $order->get_user_id() ); // set the current wp user
						wp_set_auth_cookie( $order->get_user_id() ); // start the cookie for the current registered user
						// we need to reload
						wp_safe_redirect( $_SERVER['REQUEST_URI'] );
						exit;
					}

				} catch ( Exception $e ) {
					wc_print_notice( $e->getMessage(), 'error' );
				}
			}
		}

		/**
		 * Allow Proposal payment instructions when using BACS or Cheque
		 */
		public function wc_order_proposal_payment_email_instructions( $status, $order ) {
			if ( ! empty( $order ) && $order->get_status() == 'order-proposal' ) {
				$status = $order->get_status();
			}

			return $status;
		}

		/**
		 * Display email payment instructions
		 */
		public function email_payment_instructions( $order, $sent_to_admin, $plain_text, $email ) {
			if (
				! empty( $payment_gateway = wc_get_payment_gateway_by_order( $order ) ) &&
				in_array( $order->get_payment_method(), array( 'bacs', 'cheque' ) ) &&
				$email->id == 'order_proposal'
			) {
				$payment_gateway->email_instructions( $order, false, $plain_text );
			}
		}

	}
}

global $wc_order_proposal_order_class;
$wc_order_proposal_order_class = new WC_Order_Proposal_Order();
