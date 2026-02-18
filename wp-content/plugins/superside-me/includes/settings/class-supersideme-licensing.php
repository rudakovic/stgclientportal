<?php
/**
 * Licensing class for SuperSide Me.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2020 Robin Cornett
 * @license   GPL-2.0+
 */

class SuperSide_Me_Licensing extends SuperSide_Me_Helper {

	/**
	 * Licensing page/setting
	 * @var string $page
	 */
	protected $page = 'supersideme';

	/**
	 * License key
	 * @var $license
	 */
	protected $license;

	/** License status
	 * @var $status
	 */
	protected $status;

	/**
	 * Store URL for Easy Digital Downloads.
	 * @var string
	 */
	protected $store_url = 'https://robincornett.com/edd-sl-api/';

	/**
	 * Plugin name for EDD.
	 * @var string
	 */
	protected $name = 'SuperSide Me';

	/**
	 * Plugin slug for license check.
	 * @var string
	 */
	protected $slug = 'superside-me';

	/**
	 * Value for the licensing nonce.
	 * @var string $action
	 */
	protected $action = 'superside_license_nonce';

	/**
	 * Value for the licensing nonce.
	 * @var string $nonce
	 */
	protected $nonce = 'superside_license_nonce';

	/**
	 * The plugin licensing data.
	 * @var $data
	 */
	private $data;

	/**
	 * The item ID in our store.
	 *
	 * @var integer
	 */
	private $item_id = 3772;

	/**
	 * Set up EDD licensing updates
	 * @since 1.4.0
	 */
	public function updater() {

		if ( is_multisite() && ! is_main_site() ) {
			return;
		}

		if ( ! class_exists( 'SuperSideMeUpdater' ) ) {
			// load our custom updater if it doesn't already exist
			include plugin_dir_path( __FILE__ ) . 'class-supersideme-updater.php';
		}

		$edd_updater = new SuperSideMeUpdater(
			$this->store_url,
			SUPERSIDEME_BASENAME,
			array(
				'version'   => SUPERSIDEME_VERSION,
				'license'   => trim( $this->get_license_key() ),
				'item_name' => $this->name,
				'author'    => 'Robin Cornett',
				'url'       => home_url(),
				'item_id'   => $this->item_id,
			)
		);

		$this->register_settings();
		$this->activate_license();
		$this->deactivate_license();

		add_action( 'admin_notices', array( $this, 'select_error_message' ) );
		add_action( "load-appearance_page_{$this->page}", array( $this, 'build_settings_page' ) );
	}

	/**
	 * Build the licensing settings page.
	 */
	public function build_settings_page() {
		$sections = $this->register_section();
		$this->add_sections( $sections );
		$this->add_fields( $this->register_fields(), $sections );
	}

	/**
	 * Register plugin license settings and fields
	 * @since 1.4.0
	 */
	public function register_settings() {
		register_setting( $this->page . '_licensing', 'supersidemelicense_key', array( $this, 'sanitize_license' ) );
	}

	/**
	 * Register the licensing section.
	 * @return array
	 */
	protected function register_section() {
		return array(
			'licensing' => array(
				'id'          => 'licensing',
				'label'       => __( 'SuperSide Me[nu] License', 'superside-me' ),
				'description' => array( $this, 'licensing_section_description' ),
			),
		);
	}

	/**
	 * Do the custom license section description.
	 * @since 2.6.0
	 */
	public function licensing_section_description() {
		$description = __( 'Licensed users of SuperSide Me receive plugin updates, support, and good vibes.', 'superside-me' );
		$status      = $this->get_status();
		if ( 'valid' === $status ) {
			$description .= __( ' Great news--your license is activated!', 'superside-me' );
			$data         = $this->get_license_data();
			if ( $data['expires'] && 'lifetime' !== $data['expires'] ) {
				$pretty_date  = $this->pretty_date( array( 'field' => strtotime( $data['expires'] ) ) );
				$description .= ' <span class="description">' . __( 'Your license will expire on ' ) . $pretty_date . '.</span>';
			}
		}

		echo wp_kses_post( wpautop( $description ) );
	}

	/**
	 * Register the license key field.
	 * @return array
	 */
	protected function register_fields() {
		return array(
			array(
				'setting' => 'supersidemelicense_key',
				'title'   => __( 'License Key', 'superside-me' ),
				'section' => 'licensing',
				'label'   => __( 'Enter your license key.', 'superside-me' ),
				'type'    => 'license',
				'status'  => $this->get_status(),
				'license' => $this->get_license_key(),
			),
		);
	}

	/**
	 * Sanitize license key
	 *
	 * @param  string $new_value license key
	 *
	 * @return string license key
	 *
	 * @since 1.4.0
	 */
	public function sanitize_license( $new_value ) {
		$license = get_option( 'supersidemelicense_key' );
		$status  = get_option( 'supersidemelicense_status', '' );
		if ( ( $license && $license !== $new_value ) || empty( $new_value ) ) {
			delete_option( 'supersideme_status' );
		}
		if ( $license !== $new_value || 'valid' !== $status ) {
			$this->activate_license( $new_value );
		}

		return sanitize_text_field( $new_value );
	}

	/**
	 * Activate plugin license
	 *
	 * @param  string $new_value entered license key
	 *
	 * @uses  do_remote_request()
	 *
	 * @since 1.4.0
	 */
	public function activate_license( $new_value = '' ) {

		// listen for our activate button to be clicked
		if ( empty( $_POST['supersideme_activate'] ) ) {
			return;
		}

		// If the user doesn't have permission to save, then display an error message
		if ( ! $this->user_can_save( $this->action, $this->nonce ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'superside-me' ) );
		}

		// run a quick security check
		if ( ! check_admin_referer( $this->action, $this->nonce ) ) {
			return; // get out if we didn't click the Activate button
		}

		// retrieve the license from the database
		$license = trim( $this->get_license_key() );
		$license = $new_value !== $license ? trim( $new_value ) : $license;

		if ( empty( $license ) || empty( $new_value ) ) {
			delete_option( 'supersidemelicense_status' );

			return;
		}

		// data to send in our API request
		$api_params   = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => rawurlencode( $this->name ), // the name of our product in EDD
			'url'        => esc_url( home_url() ),
		);
		$license_data = $this->do_remote_request( $api_params );
		$this->update_status( $license_data );
	}

	/**
	 * Deactivate license: deletes license status key and deactivates with store
	 * @uses  do_remote_request()
	 *
	 * @since 1.4.0
	 */
	protected function deactivate_license() {

		// listen for our activate button to be clicked
		if ( empty( $_POST['supersideme_license_deactivate'] ) ) {
			return;
		}

		// If the user doesn't have permission to save, then display an error message
		if ( ! $this->user_can_save( $this->action, $this->nonce ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'superside-me' ) );
		}

		// run a quick security check
		if ( ! check_admin_referer( $this->action, $this->nonce ) ) {
			return; // get out if we didn't click the Activate button
		}

		// retrieve the license from the database
		$license = trim( $this->get_license_key() );

		// data to send in our API request
		$api_params   = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => rawurlencode( $this->name ), // the name of our product in EDD
			'url'        => home_url(),
		);
		$license_data = $this->do_remote_request( $api_params );

		// $license_data->license will be either "deactivated" or "failed"
		if ( is_object( $license_data ) && 'deactivated' === $license_data->license ) {
			delete_option( 'supersidemelicense_status' );
		}
	}

	/**
	 * Weekly cron job to compare activated license with the server.
	 * @uses  check_license()
	 * @since 2.0.0
	 */
	public function weekly_license_check() {
		if ( apply_filters( 'supersideme_skip_license_check', false ) ) {
			return;
		}

		if ( ! empty( $_POST['supersideme_nonce'] ) ) {
			return;
		}

		$license = $this->get_license_key();
		if ( empty( $license ) ) {
			delete_option( 'supersidemelicense_status' );

			return;
		}

		// Update local plugin status
		$license_data = $this->check_license( $license );
		$this->update_status( $license_data );
	}

	/**
	 * Update the plugin license status.
	 * @since 2.5.0
	 *
	 * @param $license_data object
	 */
	private function update_status( $license_data ) {
		$status = 'invalid';
		if ( is_object( $license_data ) ) {
			$status = $license_data->license;
			if ( false === $license_data->success ) {
				$status = $license_data->error;
			}
			$this->update_supersideme_data_option( $license_data );
		}
		if ( $status === $this->get_status() ) {
			return;
		}
		update_option( 'supersidemelicense_status', $status );
	}

	/**
	 * Updates supersideme_data with correct information.
	 *
	 * @param $license_data
	 */
	protected function update_supersideme_data_option( $license_data ) {
		$data_setting = 'supersidemelicense_data';
		$data         = $this->get_license_data();
		if ( ! isset( $data['expires'] ) || $license_data->expires !== $data['expires'] ) {
			$this->update_settings(
				array(
					'expires' => $license_data->expires,
					'limit'   => (int) $license_data->license_limit,
				),
				$data_setting
			);
		}

		if ( 'valid' === $license_data->license ) {
			return;
		}

		$latest_version = $this->get_latest_version();
		if ( ! isset( $data['latest_version'] ) || $latest_version !== $data['latest_version'] ) {
			$this->update_settings(
				array(
					'latest_version' => $latest_version,
				),
				$data_setting
			);
		}
	}

	/**
	 * Check plugin license status
	 *
	 * @param $license string
	 *
	 * @uses  do_remote_request()
	 * @return mixed data
	 *
	 * @since 1.4.0
	 */
	protected function check_license( $license = '' ) {

		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $license,
			'item_name'  => rawurlencode( $this->name ), // the name of our product in EDD
			'url'        => esc_url( home_url() ),
		);
		if ( empty( $api_params['license'] ) ) {
			return '';
		}

		return $this->do_remote_request( $api_params );
	}

	/**
	 * Get the latest plugin version.
	 * @uses  do_remote_request()
	 * @return mixed
	 *
	 * @since 2.0.0
	 */
	protected function get_latest_version() {
		$api_params = array(
			'edd_action' => 'get_version',
			'item_name'  => $this->name,
			'slug'       => $this->slug,
		);
		$request    = $this->do_remote_request( $api_params );

		if ( $request && isset( $request->sections ) ) {
			$request->sections = maybe_unserialize( $request->sections );
		} else {
			return false;
		}

		return $request->new_version;
	}

	/**
	 * Send the request to the remote server.
	 *
	 * @param $api_params array
	 * @param $timeout    int
	 *
	 * @return array|bool|mixed|object
	 *
	 * @since 2.0.0
	 */
	private function do_remote_request( $api_params, $timeout = 15 ) {
		$response = wp_remote_post(
			$this->store_url,
			array(
				'timeout'   => $timeout,
				'sslverify' => true,
				'body'      => $api_params,
			)
		);
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Pick the correct error message based on the new information from EDD.
	 *
	 * @param $status
	 * @param string $message
	 *
	 * @return string
	 */
	protected function license_data_error( $status, $message = '' ) {
		if ( 'valid' === $status ) {
			return $message;
		}

		switch ( $status ) {

			case 'expired':
				$license     = $this->get_license_data();
				$pretty_date = $this->pretty_date( array( 'field' => strtotime( $license['expires'] ) ) );
				/* translators:  date of license expiration */
				$message  = sprintf( __( 'It looks like your license expired on %s.', 'superside-me' ), $pretty_date );
				$message .= sprintf(
					/* translators: link to purchase license renewal */
					__( ' To continue receiving updates, <a href="%s" rel="noopener" target="_blank">renew now and receive a discount</a>.', 'superside-me' ),
					esc_url( $this->get_renewal_url( $license['limit'] ) )
				);
				break;

			case 'revoked':
			case 'disabled':
				$message = __( 'Your license key has been disabled.', 'superside-me' );
				break;

			case 'missing':
			case 'item_name_mismatch':
				/* translators: name of plugin */
				$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'superside-me' ), $this->name );
				break;

			case 'invalid':
			case 'site_inactive':
				$message = __( 'If you\'re seeing this message and have recently migrated from another site, you should just need to reactivate your license.', 'superside-me' );
				break;

			case 'no_activations_left':
				$message = __( 'Your license key has reached its activation limit.', 'superside-me' );
				break;

			default:
				$url     = str_replace( 'edd-sl-api/', '', $this->store_url );
				$message = sprintf(
					/* translators: link to store URL/account */
					__( 'I\'m honestly not sure what went wrong. You may want to <a href="%s" target="_blank" rel="noopener">log into your account</a> and check your license key.', 'superside-me' ),
					esc_url( trailingslashit( $url ) . 'account/' )
				);
				break;
		}

		return ' ' . $message;
	}

	/**
	 * Build the URL to renew a license.
	 *
	 * @since 2.5.0
	 * @param $limit
	 *
	 * @return string
	 */
	private function get_renewal_url( $limit ) {
		$url    = str_replace( 'edd-sl-api/', '', $this->store_url );
		$choice = 1;
		if ( 5 === $limit ) {
			$choice = 2;
		} elseif ( 0 === $limit ) {
			$choice = 3;
		}

		return add_query_arg(
			array(
				'edd_action'  => 'add_to_cart',
				'download_id' => $this->item_id,
				'discount'    => 'PASTDUE15',
				'edd_options' => array(
					'price_id' => $choice,
				),
			),
			$url . 'checkout/'
		);
	}

	/**
	 * Pick which error message to display. Based on whether license has never been activated, or is no longer valid,
	 * or has expired.
	 *
	 */
	public function select_error_message() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$status = $this->get_status();
		if ( 'valid' === $status || apply_filters( 'supersideme_skip_license_check', false ) ) {
			return;
		}
		$screen   = get_current_screen();
		$haystack = array( 'appearance_page_supersideme', 'update', 'update-core', 'plugins' );
		$class    = 'notice-info';
		if ( ! in_array( $screen->id, $haystack, true ) ) {
			return;
		}
		$license = $this->get_license_key();
		if ( empty( $license ) || false === $status ) {
			$message = '<p>' . sprintf(
				/* translators: 1) URL for license activation screen; 2) name of plugin */
				__( 'Please make sure you <a href="%1$s">activate your %2$s license</a> in order to receive automatic updates and support.', 'superside-me' ),
				esc_url( $this->get_licensing_tab() ),
				esc_attr( $this->name )
			) . '</p>';
		} else {
			$message = '<p>' . sprintf(
				/* translators: 1) name of plugin; 2) URL for license activation screen */
				__( 'Sorry, there is an issue with your license for %1$s. Please check the <a href="%2$s">plugin license</a>.', 'superside-me' ),
				esc_attr( $this->name ),
				esc_url( $this->get_licensing_tab() )
			);
			if ( $license && ! in_array( $status, array( 'valid', false ), true ) ) {
				if ( 'invalid' !== $status ) {
					$class = 'error';
				}
				$message .= $this->license_data_error( $status );
			}
			$message .= '</p>';
			$data     = $this->get_license_data();
			if ( isset( $data['latest_version'] ) && SUPERSIDEME_VERSION < $data['latest_version'] ) {
				$message .= '<p>' . sprintf(
					/* translators: 1) name of plugin; 2) latest plugin version number; 3) current/active plugin version */
					__( 'The latest version of %1$s is %2$s and you are running %3$s. ', 'superside-me' ),
					esc_attr( $this->name ),
					esc_attr( $data['latest_version'] ),
					esc_attr( SUPERSIDEME_VERSION )
				) . '</p>';
			}
		}

		$this->do_error_message( $message, $class );
	}

	/**
	 * Get the link to the licensing tab.
	 *
	 * @return array
	 */
	private function get_licensing_tab() {
		return add_query_arg(
			array(
				'page' => 'supersideme',
				'tab'  => 'licensing',
			),
			admin_url( 'themes.php' )
		);
	}

	/**
	 * Get the license key.
	 * @since 2.5.0
	 * @return string
	 */
	private function get_license_key() {
		if ( isset( $this->license ) ) {
			return $this->license;
		}
		$this->license = get_option( 'supersidemelicense_key', '' );

		return $this->license;
	}

	/**
	 * Get the license key status.
	 *
	 * @since 2.5.0
	 * @return bool|mixed
	 */
	private function get_status() {
		if ( isset( $this->status ) ) {
			return $this->status;
		}
		$this->status = get_option( 'supersidemelicense_status', false );

		return $this->status;
	}

	/**
	 * Get the license data for the plugin.
	 *
	 * @return array
	 */
	private function get_license_data() {
		if ( isset( $this->data ) ) {
			return $this->data;
		}
		$default    = array(
			'expires'        => false,
			'limit'          => 1,
			'latest_version' => SUPERSIDEME_VERSION,
		);
		$setting    = get_option( 'supersidemelicense_data', $default );
		$this->data = wp_parse_args( $setting, $default );

		return $this->data;
	}

	/**
	 * Error messages if license is empty or invalid
	 *
	 * @param $message string
	 * @param $class   void|string
	 *
	 * @since 1.4.0
	 */
	protected function do_error_message( $message, $class = '' ) {
		if ( empty( $message ) ) {
			return;
		}
		printf( '<div class="notice %s">%s</div>', esc_attr( $class ), wp_kses_post( $message ) );
	}

	/**
	 * Convert a date string to a pretty format.
	 *
	 * @param $args
	 * @param string $before
	 * @param string $after
	 *
	 * @return string
	 */
	protected function pretty_date( $args, $before = '', $after = '' ) {
		$date_format = isset( $args['date_format'] ) ? $args['date_format'] : get_option( 'date_format' );

		return $before . date_i18n( $date_format, $args['field'] ) . $after;
	}

	/**
	 * Takes an array of new settings, merges them with the old settings, and pushes them into the database.
	 *
	 * @since 2.0.0
	 *
	 * @param string|array $new     New settings. Can be a string, or an array.
	 * @param string       $setting Optional. Settings field name. Default is supersideme.
	 * @return mixed
	 */
	protected function update_settings( $new = '', $setting = 'supersideme' ) {
		return update_option( $setting, wp_parse_args( $new, get_option( $setting ) ) );
	}
}
