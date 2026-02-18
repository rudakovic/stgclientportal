<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'WC_Order_Proposal_Dependencies' ) ) {

	class WC_Order_Proposal_Dependencies {

		/**
		 * @var array
		 */
		private $activated_plugins;

		/**
		 * @var string
		 */
		private $php_min_version = '7.2';

		/**
		 * @var string
		 */
		private $woocommerce_min_version = '3.3';

		/**
		 * @var string
		 */
		private $pdf_invoices_min_version = '3.8.0-beta-1';

		/**
		 * @var string
		 */
		private $plugin_name = 'WooCommerce Order Proposal';

		/**
		 * @var string
		 */
		private $plugin_text_domain = 'woocommerce-order-proposal';

		public function check_dependencies(): bool {
			$dependencies_met = true;

			// Check PHP version.
			if ( version_compare( PHP_VERSION, $this->php_min_version, '<' ) ) {
				add_action( 'admin_notices', array( $this, 'notice_php_version_requirement' ) );
				$dependencies_met = false;
			}

			// Check WooCommerce activation.
			if ( ! $this->is_active( 'woocommerce/woocommerce.php' ) ) {
				add_action( 'admin_notices', array( $this, 'notice_woocommerce_requirement' ) );
				$dependencies_met = false;
			}

			// Check WooCommerce version.
			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, $this->woocommerce_min_version, '<' ) ) {
				add_action( 'admin_notices', array( $this, 'notice_woocommerce_version_requirement' ) );
				$dependencies_met = false;
			}

			return $dependencies_met;
		}

		public function check_pdf_invoices_compatibility(): bool {
			if ( $this->is_active( 'woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php' ) ) {
				if ( function_exists( 'WPO_WCPDF' ) && version_compare( WPO_WCPDF()->version, $this->pdf_invoices_min_version, '<' ) ) {
					add_action( 'admin_notices', array( $this, 'notice_pdf_invoices_version_requirement' ) );

					return false;
				}

				return true;
			}

			return false;
		}

		public function check_quotation_compatibility(): bool {
			if ( $this->is_active( 'woocommerce-quotation/class.quote.php' ) ) {
				add_action( 'admin_notices', array( $this, 'notice_quotation_incompatibility' ) );

				return false;
			}

			return true;
		}

		public function notice_php_version_requirement(): void {
			/* translators: 1. Plugin name, 2. PHP version */
			$error_message = sprintf( __( '<strong>%1$s</strong> requires PHP %2$s or higher.', $this->plugin_text_domain ), $this->plugin_name, $this->php_min_version );
			$how_to_update = __( 'How to update your PHP version', $this->plugin_text_domain );
			printf( '<div class="notice notice-error"><p>%s</p><p><a href="%s">%s</a></p></div>', $error_message, 'http://docs.wpovernight.com/general/how-to-update-your-php-version/', $how_to_update );
		}

		public function notice_woocommerce_requirement(): void {
			/* translators: 1. Plugin name, 2: Opening anchor tag, 3: Closing anchor tag */
			$error_message = sprintf(
				__( '<strong>%1$s</strong> requires %2$sWooCommerce%3$s to be installed & activated!', $this->plugin_text_domain ),
				$this->plugin_name,
				'<a href="https://wordpress.org/plugins/woocommerce/">',
				'</a>'
			);
			printf( '<div class="notice notice-error"><p>%s</p></div>', $error_message );
		}

		public function notice_woocommerce_version_requirement(): void {
			/* translators: 1. Plugin name, 2: WooCommerce version, 3: Opening anchor tag, 4: Closing anchor tag */
			$error_message = sprintf(
				__( '<strong>%1$s</strong> requires at least version %2$s of WooCommerce to be installed. %3$sGet the latest version here%4$s!', $this->plugin_text_domain ),
				$this->plugin_name, $this->woocommerce_min_version,
				'<a href="https://wordpress.org/plugins/woocommerce/">',
				'</a>'
			);
			printf( '<div class="notice notice-error"><p>%s</p></div>', $error_message );
		}

		public function notice_pdf_invoices_version_requirement(): void {
			$latest_version_url = 'https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/';

			/* translators: 1. Plugin name, 2: PDF Invoices plugin version, 3: Opening anchor tag, 4: Closing anchor tag */
			$warning_message = sprintf(
				__( '<strong>%1$s</strong> requires at least version %2$s of PDF Invoices & Packing Slips for WooCommerce. %3$sGet the latest version here%4$s!', $this->plugin_text_domain ),
				$this->plugin_name,
				$this->pdf_invoices_min_version,
				'<a href="' . $latest_version_url . '" target="_blank" >',
				'</a>'
			);
			printf( '<div class="notice notice-warning"><p>%s</p></div>', $warning_message );
		}

		public function notice_quotation_incompatibility(): void {
			/* translators: Plugin name */
			$error_message = sprintf( __( '<strong>%s</strong> is not compatible with WC Quotation plugin. Please deactivate it!', $this->plugin_text_domain ), $this->plugin_name );
			printf( '<div class="notice notice-error"><p>%s</p></div>', $error_message );
		}

		private function is_active( string $plugin_slug ): bool {
			if ( empty( $this->activated_plugins ) ) {
				$this->activated_plugins = array_merge( get_option( 'active_plugins', array() ), get_site_option( 'active_sitewide_plugins', array() ) );
			}

			return in_array( $plugin_slug, $this->activated_plugins ) || array_key_exists( $plugin_slug, $this->activated_plugins );
		}

	}
}

return new WC_Order_Proposal_Dependencies();