<?php
/**
 * Settings sanitization class
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2020 Robin Cornett
 * @license   GPL-2.0+
 */

class SuperSide_Me_Settings_Sanitize extends SuperSide_Me_Helper {

	/**
	 * @var array
	 */
	protected $setting;

	/**
	 * @var string
	 */
	protected $page;

	public function __construct( $setting, $page ) {
		$this->setting = $setting;
		$this->page    = $page;

		parent::__construct();
	}

	/**
	 * validate all inputs
	 *
	 * @param  array $new_value various settings
	 *
	 * @return array
	 *
	 * @since  1.0.0
	 */
	public function sanitize( $new_value ) {

		$new_value = array_merge( $this->setting, $new_value );
		$new_value = $this->switcher( $new_value );

		$new_value['menus']  = $this->sanitize_menus( $new_value['menus'] );
		$new_value['custom'] = $this->sanitize_custom_buttons( $new_value['custom'], $this->setting['custom'] );

		return $new_value;
	}

	/**
	 * Sanitize the registered menu location options.
	 *
	 * @param $new_value
	 *
	 * @return mixed
	 */
	protected function sanitize_menus( $new_value ) {
		$menus  = get_registered_nav_menus();
		$values = array();
		foreach ( $menus as $location => $description ) {
			$skip                           = isset( $new_value['skip'][ $location ] ) ? $new_value['skip'][ $location ] : 0;
			$heading                        = isset( $new_value['heading'][ $location ] ) ? $new_value['heading'][ $location ] : '';
			$values['skip'][ $location ]    = $this->one_zero( $skip );
			$values['heading'][ $location ] = sanitize_text_field( $heading );
		}

		return $values;
	}

	/**
	 * Sanitize the custom buttons fields.
	 * @since 2.4.0
	 *
	 * @param $new_value array
	 * @param $old_value array
	 * @return mixed
	 */
	protected function sanitize_custom_buttons( $new_value, $old_value ) {
		if ( $new_value === $old_value ) {
			return $old_value;
		}
		$count = count( $new_value );
		$new   = array();
		$n     = 0;
		for ( $i = 0; $i < $count; $i ++ ) {
			if ( ! $new_value[ $i ]['link'] ) {
				continue;
			}
			foreach ( $new_value[ $i ] as $key => $value ) {
				switch ( $key ) {
					case 'link':
						$new[ $n ][ $key ] = esc_url( $value );
						break;

					case 'show':
					case 'new':
						$new[ $n ][ $key ] = $this->one_zero( $value );
						break;

					default:
						$new[ $n ][ $key ] = trim( esc_attr( $value ) );
						break;
				}
			}
			$n++;
		}

		return $new;
	}

	/**
	 * Sanitize the standard fields.
	 *
	 * @param $new_value
	 *
	 * @return mixed
	 */
	protected function switcher( $new_value ) {
		$definitions = new SuperSideMeDefineSettings();

		foreach ( $definitions->register_fields() as $field ) {
			if ( ! isset( $field['type'] ) ) {
				continue;
			}
			switch ( $field['type'] ) {
				case 'checkbox':
					$new_value[ $field['setting'] ] = $this->one_zero( $new_value[ $field['setting'] ] );
					break;

				case 'select':
					$new_value[ $field['setting'] ] = esc_attr( $new_value[ $field['setting'] ] );
					break;

				case 'number':
					$new_value[ $field['setting'] ] = $this->check_value( $new_value[ $field['setting'] ], $this->setting[ $field['setting'] ], $field['input_attrs']['min'], $field['input_attrs']['max'] );
					break;

				case 'color':
					$old_value                      = $this->setting[ $field['setting'] ];
					$title                          = $field['title'];
					$new_value[ $field['setting'] ] = $this->is_color( $new_value[ $field['setting'] ], $old_value, $title );
					break;

				case 'text':
					$new_value[ $field['setting'] ] = sanitize_text_field( $new_value[ $field['setting'] ] );
					break;

				case 'radio':
					$new_value[ $field['setting'] ] = is_numeric( $new_value[ $field['setting'] ] ) ? (int) $new_value[ $field['setting'] ] : esc_attr( $new_value[ $field['setting'] ] );
					break;

				case 'checkbox_array':
					$choices = $field['choices'];
					foreach ( $choices as $key => $label ) {
						$new_value[ $field['setting'] ][ $key ] = $this->one_zero( $new_value[ $field['setting'] ][ $key ] );
					}
					break;

				case 'default':
					$new_value[ $field['setting'] ] = esc_attr( $new_value[ $field['setting'] ] );
					break;
			}
		} // End foreach().

		return $new_value;
	}

	/**
	 * Returns a 1 or 0, for all truthy / falsy values.
	 *
	 * Uses double casting. First, we cast to bool, then to integer.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $new_value Should ideally be a 1 or 0 integer passed in
	 *
	 * @return integer 1 or 0.
	 */
	protected function one_zero( $new_value ) {
		return (int) (bool) $new_value;
	}

	/**
	 * Function that will check if value is a valid HEX color.
	 *
	 * @param $new_value string
	 * @param $old_value string
	 * @param $title     string
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function is_color( $new_value, $old_value, $title ) {

		$new_value = trim( $new_value );
		$new_value = strip_tags( stripslashes( $new_value ) );

		$hex_color = '/^#[a-f0-9]{6}$/i';
		if ( preg_match( $hex_color, $new_value ) ) {
			return $new_value;
		}

		$message = sprintf( __( 'Well, that was unexpected. The %s has been reset to the last valid setting; the value you entered didn\'t work.', 'superside-me' ), $title );

		add_settings_error(
			'color',
			'not-updated',
			$message,
			'error'
		);

		return $old_value;
	}

	/**
	 * Check the numeric value against the allowed range. If it's within the range, return it; otherwise, return the
	 * old value.
	 *
	 * @param $new_value int new submitted value
	 * @param $old_value int old setting value
	 * @param $min       int minimum value
	 * @param $max       int maximum value
	 *
	 * @return int
	 */
	protected function check_value( $new_value, $old_value, $min, $max ) {
		if ( $new_value >= $min && $new_value <= $max ) {
			return (int) $new_value;
		}

		return (int) $old_value;
	}
}
