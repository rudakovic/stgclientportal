<?php

if ( class_exists( 'woocommerce_wpml' ) ) {

	class WC_Order_Proposal_WC_wpml_hooks {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'woocommerce_order_proposal_gateway_start', array($this, 'translate_gateway_emails') );

			// Translate the cancel email headers
			// todo
			// add_action( 'woocommerce_order_status_order-proposal_to_cancelled_notification', array($GLOBALS['WC_Others_Components_WC_wpml_hooks'], 'email_heading_cancelled' ) );

			add_filter( 'wcml_emails_options_to_translate', array( $this, 'wcml_emails_options_to_translate_order_proposal'), 10);

			// Translate header and subject for order action
			add_action( 'woocommerce_order_action_send_order_proposal', array( $this, 'email_before_resend_order_emails_header_translation' ), 1, 1 );

			// creates issues whith all emails being partially translated
			//add_action( 'wpml_language_has_switched', array( $this, 'reload_textdomains' ), 10, 1 );
			
			add_action( 'change_locale', array( $this, 'reload_textdomains' ), 999 ); // Since WP 4.7

			add_action( 'woocommerce_order_status_pending_to_order-proposal_notification', array( $this, 'refresh_email_lang' ), 9 );
			add_action( 'woocommerce_order_status_draft_to_order-proposal_notification', array( $this, 'refresh_email_lang' ), 9 );
			add_action( 'woocommerce_order_status_order-proposalreq_to_order-proposal_notification', array( $this, 'refresh_email_lang' ), 9 );

			add_action( 'woocommerce_order_status_order-proposal_to_cancelled_notification', array( $this, 'refresh_email_lang' ), 9 );
			add_action( 'woocommerce_order_status_order-proposal_to_processing_notification', array( $this, 'refresh_email_lang' ), 9 );
			
			add_action( 'woocommerce_order_status_pending_to_order-proposal_notification', array( $this, 'refresh_email_lang' ), 9 );
		}

		public function refresh_email_lang( $order_id ) {
			global $woocommerce_wpml;

			if ( empty( $woocommerce_wpml->emails ) || ! is_callable( array( $woocommerce_wpml->emails, 'refresh_email_lang' ) ) ) {
				return;
			}

			$woocommerce_wpml->emails->refresh_email_lang( $order_id );
		}

		/**
         * Translate strings of notifications.
         *
         * @param integer $order_id Order ID.
         */
		public function translate_notification( $order_id ) {
			global $woocommerce_wpml;

			if ( empty( $woocommerce_wpml->emails ) || ! is_callable( array( $woocommerce_wpml->emails, 'refresh_email_lang' ) ) ) {
				return;
			}

			$woocommerce_wpml->emails->refresh_email_lang( $order_id );
		}

		public function reload_textdomains( $locale = '' ) {
			// unload text domains
			unload_textdomain( 'woocommerce-order-proposal' );
			unload_textdomain( 'woocommerce' );

			wc_order_proposal()->load_plugin_textdomain();
			WC()->load_plugin_textdomain();
		}

		public function translate_gateway_emails( $emailproposal ) {
			// Translate the header and subject
			if ( wc_string_to_bool( $emailproposal ) ) {
				add_action( 'woocommerce_order_status_pending_to_order-proposal_notification', array( $this, 'email_order_proposal' ) );
				add_action( 'woocommerce_order_status_pending_to_order-proposal_notification', array( $this, 'email_order_proposal' ) );
				add_action( 'woocommerce_order_status_order-proposalreq_to_order-proposal_notification', array( $this, 'email_order_proposal' ) );
			}

			add_action( 'woocommerce_order_status_pending_to_order-proposal_notification', array( $this, 'email_order_proposal_gateway' ) );
			add_action( 'woocommerce_order_status_pending_to_order-proposal_notification', array( $this, 'email_admin_order_proposal_gateway' ) );
		}

		public function email_before_resend_order_emails_header_translation( $order_id ) {
			global $woocommerce_wpml;

			if ( empty( $woocommerce_wpml->emails ) || ! is_callable( array( $woocommerce_wpml->emails, 'refresh_email_lang' ) ) ) {
				return;
			}

			$order    = wc_get_order( $order_id );
			$order_id = $order->get_id();

			$this->email_order_proposal( $order_id );

			$woocommerce_wpml->emails->refresh_email_lang( $order_id );
		}

		public function email_order_proposal( $order_id ) {
			global $woocommerce, $woocommerce_wpml;

			if (
				empty( $woocommerce_wpml->emails ) ||
				! is_callable( array( $woocommerce_wpml->emails, 'wcml_get_translated_email_string' ) ) ||
				! class_exists( 'WC_Email_Customer_Order_Proposal' ) ||
				! isset( $woocommerce->mailer()->emails['WC_Email_Order_Proposal'] )
			) {
				return;
			}

			$emails = $woocommerce->mailer()->emails;

			$emails['WC_Email_Order_Proposal']->heading = $woocommerce_wpml->emails->wcml_get_translated_email_string( 'admin_texts_woocommerce_order_proposal_settings', '[woocommerce_order_proposal_settings]heading', $order_id );
			$emails['WC_Email_Order_Proposal']->subject = $woocommerce_wpml->emails->wcml_get_translated_email_string( 'admin_texts_woocommerce_order_proposal_settings', '[woocommerce_order_proposal_settings]subject', $order_id );

			$enabled                                    = $emails['WC_Email_Order_Proposal']->enabled;
			$emails['WC_Email_Order_Proposal']->enabled = false;
			$emails['WC_Email_Order_Proposal']->trigger( $order_id );
			$emails['WC_Email_Order_Proposal']->enabled = $enabled;
		}

		public function email_order_proposal_gateway( $order_id ) {
			global $woocommerce, $woocommerce_wpml;

			if (
				empty( $woocommerce_wpml->emails ) ||
				! is_callable( array( $woocommerce_wpml->emails, 'wcml_get_translated_email_string' ) ) ||
				! class_exists( 'WC_Email_Customer_Order_Proposal_Gateway' ) ||
				! isset( $woocommerce->mailer()->emails['WC_Email_Order_Proposal_Gateway'] )
			) {
				return;
			}

			$emails = $woocommerce->mailer()->emails;

			$emails['WC_Email_Order_Proposal_Gateway']->heading = $woocommerce_wpml->emails->wcml_get_translated_email_string( 'admin_texts_woocommerce_order_proposal_gateway_settings', '[woocommerce_order_proposal_gateway_settings]heading', $order_id );
			$emails['WC_Email_Order_Proposal_Gateway']->subject = $woocommerce_wpml->emails->wcml_get_translated_email_string( 'admin_texts_woocommerce_order_proposal_gateway_settings', '[woocommerce_order_proposal_gateway_settings]subject', $order_id );

			$enabled                                            = $emails['WC_Email_Order_Proposal_Gateway']->enabled;
			$emails['WC_Email_Order_Proposal_Gateway']->enabled = false;
			$emails['WC_Email_Order_Proposal_Gateway']->trigger( $order_id );
			$emails['WC_Email_Order_Proposal_Gateway']->enabled = $enabled;
		}

		public function email_admin_order_proposal_gateway( $order_id ) {
			global $woocommerce, $woocommerce_wpml, $sitepress;

			if (
				empty( $woocommerce_wpml->emails ) ||
				! is_callable( array( $woocommerce_wpml->emails, 'wcml_get_translated_email_string' ) ) ||
				! is_callable( array( $woocommerce_wpml->emails, 'refresh_email_lang' ) ) ||
				! is_callable( array( $woocommerce_wpml->emails, 'change_email_language' ) ) ||
				! class_exists( 'WC_Email_Admin_Order_Proposal_Gateway' ) ||
				! isset( $woocommerce->mailer()->emails['WC_Email_Admin_Order_Proposal_Gateway'] )
			) {
				return;
			}

			$emails      = $woocommerce->mailer()->emails;
			$wpml_emails = $woocommerce_wpml->emails;
			$recipients  = explode( ',', $emails['WC_Email_Admin_Order_Proposal_Gateway']->get_recipient() );

			foreach ( $recipients as $recipient ) {
				$user = get_user_by( 'email', $recipient );
				if ( $user && ! empty( $sitepress ) && is_callable( array( $sitepress, 'get_user_admin_language' ) ) ) {
					$user_lang = $sitepress->get_user_admin_language( $user->ID, true );
				} else {
					$order     = wc_get_order( $order_id );
					$user_lang = $order->get_meta( 'wpml_language' );
				}

				$wpml_emails->change_email_language( $user_lang );

				$emails['WC_Email_Admin_Order_Proposal_Gateway']->heading   = $wpml_emails->wcml_get_translated_email_string( 'admin_texts_woocommerce_admin_order_proposal_gateway_settings', '[woocommerce_admin_order_proposal_gateway_settings]heading', $order_id, $user_lang );
				$emails['WC_Email_Admin_Order_Proposal_Gateway']->subject   = $wpml_emails->wcml_get_translated_email_string( 'admin_texts_woocommerce_admin_order_proposal_gateway_settings', '[woocommerce_admin_order_proposal_gateway_settings]subject', $order_id, $user_lang );
				$emails['WC_Email_Admin_Order_Proposal_Gateway']->recipient = $recipient;
				$emails['WC_Email_Admin_Order_Proposal_Gateway']->trigger( $order_id );
			}

			$emails['WC_Email_Admin_Order_Proposal_Gateway']->enabled = false;
			$wpml_emails->refresh_email_lang( $order_id );
		}

		/**
		 * Order Proposal Email settings translations
		 * WP filter: wcml_emails_options_to_translate
		 */
		public function wcml_emails_options_to_translate_order_proposal( $emails_options ) {
			$emails_options[] = 'woocommerce_admin_order_proposal_gateway_settings';
			$emails_options[] = 'woocommerce_order_proposal_gateway_settings';
			$emails_options[] = 'woocommerce_order_proposal_settings';

			return $emails_options;
		}
	}

	new WC_Order_Proposal_WC_wpml_hooks();
}
