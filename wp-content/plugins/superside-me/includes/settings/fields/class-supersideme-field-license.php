<?php

class SuperSideMeFieldLicense extends SuperSideMeFieldBase {

	/**
	 * Do the licensing key field.
	 *
	 * @since 2.7.0
	 */
	public function do_field() {
		$this->show_indicator();
		printf(
			'<input type="password" class="regular-text" id="%1$s" name="%1$s" value="%2$s" />',
			esc_attr( $this->field['setting'] ),
			esc_attr( $this->field['license'] )
		);
		if ( ! empty( $this->field['license'] ) ) {
			$this->add_deactivation_button();
		}
		$this->do_description();
	}

	/**
	 * If the license is valid, add the indicator before the text field.
	 *
	 * @since 2.7.0
	 */
	private function show_indicator() {
		if ( ! $this->field['license'] || 'valid' !== $this->field['status'] ) {
			return;
		}
		$style = 'color:white;background-color:green;border-radius:100%;margin-right:8px;vertical-align:middle;';
		printf(
			'<span class="dashicons dashicons-yes" style="%s"></span>',
			esc_attr( $style )
		);
	}

	/**
	 * If the license is not set or is not valid, add the description.
	 *
	 * @since 2.7.0
	 */
	private function do_description() {
		if ( 'valid' === $this->field['status'] ) {
			return;
		}
		printf( '<p class="description"><label for="%3$s[%1$s]">%2$s</label></p>', esc_attr( $this->field['setting'] ), esc_html( $this->field['label'] ), esc_attr( $this->page ) );
	}

	/**
	 * License deactivation button
	 */
	private function add_deactivation_button() {
		if ( 'valid' !== $this->field['status'] ) {
			return;
		}

		$value = sprintf( __( 'Deactivate', 'superside-me' ) );
		$name  = 'supersideme_license_deactivate';
		$class = 'button-secondary';
		$this->print_button( $class, $name, $value );
	}

	/**
	 * Output a button to the settings page.
	 * @param $class string the button class
	 * @param $name string the name of the button
	 * @param $value string the button label
	 */
	private function print_button( $class, $name, $value ) {
		printf(
			'<input type="submit" class="%s" name="%s" value="%s"/>',
			esc_attr( $class ),
			esc_attr( $name ),
			esc_attr( $value )
		);
	}
}
