<?php
/**
 *
 * Defines defaults and retrieves settings.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2020 Robin Cornett
 * @license   GPL-2.0+
 */

class SuperSideMeGetSetting {

	/**
	 * The plugin setting.
	 *
	 * @var array
	 */
	protected $setting;

	/**
	 * SuperSideMeGetSetting constructor.
	 */
	public function __construct() {
		$this->setting = $this->get_setting();
	}

	/**
	 * Get the plugin setting
	 *
	 * @param  $key string
	 *
	 * @return array           get_option( 'supersideme' ) with defaults
	 *
	 * @since 1.5.1
	 */
	public function get_setting( $key = '' ) {
		if ( isset( $this->setting ) && ! is_customize_preview() ) {
			return $key ? $this->setting[ $key ] : $this->setting;
		}

		$setting = get_option( 'supersideme', $this->defaults() );
		if ( ! isset( $setting['position'] ) || empty( $setting['position'] ) ) {
			$setting['position'] = isset( $setting['shrink'] ) && $setting['shrink'] ? 'absolute' : 'relative';
		}

		if ( ! isset( $setting['svg'] ) ) {
			$setting['svg'] = 0;
		}

		if ( ! empty( $setting['bottom_stick'] ) ) {
			$setting['position'] = 'bottom';
		}

		$this->setting = wp_parse_args( $setting, $this->defaults() );

		return $key ? $this->setting[ $key ] : $this->setting;
	}

	/**
	 * Set the default values for the supersideme option.
	 * @return array
	 */
	public function defaults() {
		return include 'definitions/defaults.php';
	}
}
