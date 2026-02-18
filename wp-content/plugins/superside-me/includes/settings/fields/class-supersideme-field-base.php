<?php

class SuperSideMeFieldBase {

	/**
	 * The plugin page/base.
	 *
	 * @var string
	 */
	protected $page = 'supersideme';

	/**
	 * The current plugin setting.
	 *
	 * @var array
	 */
	protected $setting;

	/**
	 * The field parameters.
	 *
	 * @var array
	 */
	protected $field;

	/**
	 * The field class constructor.
	 *
	 * @param array $field
	 * @param array $setting
	 */
	public function __construct( $field ) {
		$this->field = $field;
	}

	/**
	 * Get the plugin setting.
	 *
	 * @param  string $key
	 * @return string|array
	 */
	protected function get_setting( $key = '' ) {
		if ( isset( $this->setting ) ) {
			return $key ? $this->setting[ $key ] : $this->setting;
		}
		$this->setting = supersideme_get_settings();

		return $key ? $this->setting[ $key ] : $this->setting;
	}

	/**
	 * Get the setting/field ID.
	 * @param $field
	 *
	 * @return string
	 */
	protected function get_field_id( $field ) {
		return "{$this->page}-{$field['setting']}";
	}

	/**
	 * Get the setting/field name.
	 * @param $field
	 *
	 * @return string
	 */
	protected function get_field_name( $field ) {
		return "{$this->page}[{$field['setting']}]";
	}
}
