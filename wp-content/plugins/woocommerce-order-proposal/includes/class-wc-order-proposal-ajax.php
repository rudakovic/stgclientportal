<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'WC_Order_Proposal_Ajax' ) ) {

	class WC_Order_Proposal_Ajax {

		public function __construct() {
			add_action( 'wp_ajax_wc_order_proposal_save_form', array( $this, 'save_meta_box_ajax' ) );
			add_action( 'wp_ajax_wc_order_proposal_reduce_stock', array( $this, 'reduce_stock_ajax' ) );
			add_action( 'wp_ajax_wc_order_proposal_add_language', array( $this, 'save_language_ajax' ) );
			add_action( 'wp_ajax_mark_order_as_accept_proposal', array( $this, 'wc_order_proposal_mark_order_as_accept_proposal' ) );
			add_action( 'wp_ajax_mark_order_as_declined_proposal', array( $this, 'wc_order_proposal_mark_order_as_declined_proposal' ) );
		}

		/**
		 * Reduce stock via ajax
		 * WP action: wp_ajax_wc_order_proposal_reduce_stock
		 */
		public function reduce_stock_ajax() {
			check_ajax_referer( 'wpo_wcop_reduce_stock', 'security' );

			// Check user rights
			if ( ! current_user_can( 'edit_shop_orders' ) ) {
				die( -1 );
			}

			$data = array( 'result' => false );

			if ( isset( $_POST['order_id'] ) ) {
				$order_id = wc_clean( $_POST['order_id'] );
				wc_reduce_stock_levels( $order_id );
				$data = array( 'result' => true );
			}

			wp_send_json( $data );
			wp_die();
		}

		/**
		 * Order Proposal Ajax
		 * Function for saving order proposal via AJAX
		 * WP action: wp_ajax_wc_order_save_form
		 */
		public function save_meta_box_ajax() {
			check_ajax_referer( 'wpo_wcop_save-form', 'security' );

			// Check user rights
			if ( ! current_user_can( 'edit_shop_orders' ) ) {
				die( -1 );
			}

			if ( isset( $_POST['wc_order_proposal_time'] ) && isset( $_POST['wc_order_proposal_start_time'] ) ) {
				$post_id = wc_clean( $_POST['order_id'] );

				// Download data
				$order_start_time = wc_clean( $_POST['wc_order_proposal_start_time'] );
				$order_time       = wc_clean( $_POST['wc_order_proposal_time'] );
				$order_prepay     = wc_clean( $_POST['wc_order_proposal_prepay'] );

				// Check if the variable is number
				if ( wc_order_proposal_validate_date( $order_time ) ) {
					wc_order_proposal_save_time( $post_id, $order_start_time, $order_time, $order_prepay );
					echo '1';
				} else {
					echo '-1';
				}

				do_action( 'wc_order_proposal_meta_box_save_ajax', $post_id );
			} else {
				echo '-1';
			}

			wp_die();
		}


		/**
		 * Order VAT Save Language on new orders
		 * Function for saving language via AJAX
		 * WP action: wp_ajax_wc_order_proposal_add_language
		 */
		public function save_language_ajax() {
			check_ajax_referer( 'wpo_wcop_save-lang', 'security' );

			if ( class_exists( 'woocommerce_wpml' ) ) {
				// Check user rights
				if ( ! current_user_can( 'edit_shop_orders' ) ) {
					die( -1 );
				}

				if ( isset( $_POST['wc_order_language'] ) ) {
					$order_id = wc_clean( $_POST['order_id'] );

					// Download data
					$order_lang = wc_clean( $_POST['wc_order_language'] );

					wc_order_proposal_save_language( $order_id, $order_lang );
				}
			}

			wp_die();
		}


		/**
		 * Order Proposal Accept proposal on my account page ajax call
		 * WP filter: wp_ajax_mark_order_as_accept_proposal
		 */
		function wc_order_proposal_mark_order_as_accept_proposal() {
			// Check the nonce
			if ( empty( $_GET['action'] ) || ! is_user_logged_in() || ! check_admin_referer( $_GET['action'] ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'woocommerce-order-proposal' ) );
			}

			if ( isset( $_GET['order_id'] ) ) {
				$order_id = wc_clean( $_GET['order_id'] );
				$order    = wc_get_order( $order_id );

				if ( $order ) {
					$order->add_order_note( __( 'Customer accepted proposal.', 'woocommerce-order-proposal' ) );
					$new_status = apply_filters( 'wc_order_proposal_accept_order_status', 'wc-pending' );
					$order->update_status( $new_status );
				}
			}

			wp_safe_redirect( wp_get_referer() ? wp_get_referer() : get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );

			die();
		}


		/**
		 * Order Proposal Accept proposal on my account page ajax call
		 * WP filter: wp_ajax_mark_order_as_declined_proposal
		 */
		public function wc_order_proposal_mark_order_as_declined_proposal() {
			// Check the nonce
			if ( empty( $_GET['action'] ) || ! is_user_logged_in() || ! check_admin_referer( $_GET['action'] ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'woocommerce-order-proposal' ) );
			}

			if ( isset( $_GET['order_id'] ) ) {
				$order_id = wc_clean( $_GET['order_id'] );
				$order    = wc_get_order( $order_id );

				if ( $order ) {
					$order->add_order_note( __( 'Customer declined proposal.', 'woocommerce-order-proposal' ) );
					$new_status = apply_filters( 'wc_order_proposal_declined_order_status', 'wc-cancelled' );
					$order->update_status( $new_status );
					do_action( 'wc_order_proposal_declined', $order );
				}
			}

			wp_safe_redirect( wp_get_referer() ? wp_get_referer() : get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );

			die();
		}

	}
}

new WC_Order_Proposal_Ajax();
