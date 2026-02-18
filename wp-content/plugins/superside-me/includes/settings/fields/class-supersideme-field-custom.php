<?php

class SuperSideMeFieldCustom extends SuperSideMeFieldBase {

	/**
	 * Build the text fields for the custom buttons.
	 *
	 * @since 2.4.0
	 *
	 * @param $field
	 * @param $i
	 */
	protected function text_fields( $field, $i = false ) {
		$class = 'text widefat';
		if ( ! empty( $field['class'] ) ) {
			echo '<div class="icon input">';
			$class .= ' ' . $field['class'];
		}
		printf(
			'<input type="text" class="%3$s" id="%1$s" name="%4$s" value="%2$s" />',
			esc_attr( $this->get_custom_id( $field['setting'], $i ) ),
			esc_attr( $this->get_custom_text_value( $field['setting'], $i ) ),
			esc_attr( $class ),
			esc_attr( $this->get_custom_name( $field['setting'], $i ) )
		);
		if ( ! empty( $field['class'] ) ) {
			echo '</div>';
		}
	}

	/**
	 * Get the value of the custom text field.
	 *
	 * @param string $key
	 * @param integer $i
	 * @return string
	 */
	private function get_custom_text_value( $key, $i ) {
		$setting = $this->get_setting( $this->field['setting'] );
		$value   = empty( $setting[ $key ] ) ? '' : $setting[ $key ];
		if ( false !== $i && ! empty( $setting[ $i ][ $key ] ) ) {
			return $setting[ $i ][ $key ];
		}

		return $value;
	}

	/**
	 * Get the custom field ID.
	 *
	 * @param string $key
	 * @param integer $i
	 * @return string
	 */
	private function get_custom_id( $key, $i ) {
		return false !== $i ? "{$this->page}-{$this->field['setting']}-{$i}-{$key}" : "{$this->page}-{$this->field['setting']}-{$key}";
	}

	/**
	 * Get the custom field name.
	 *
	 * @param string $key
	 * @param integer $i
	 * @return string
	 */
	private function get_custom_name( $key, $i ) {
		return false !== $i ? "{$this->page}[{$this->field['setting']}][{$i}][{$key}]" : "{$this->page}[{$this->field['setting']}][{$key}]";
	}

	/**
	 * Build the checkbox fields for the custom buttons.
	 *
	 * @since 2.4.0
	 *
	 * @param $field
	 * @param $i
	 */
	protected function checkbox_fields( $field, $i ) {
		printf(
			'<input type="hidden" name="%1$s[%3$s][%2$s]" value="0" />',
			esc_attr( $this->get_field_name( $this->field ) ),
			esc_attr( $field['setting'] ),
			(int) $i
		);
		printf(
			'<input type="checkbox" name="%4$s" id="%1$s" value="1"%2$s class="code" /> <label for="%1$s">%3$s</label>',
			esc_attr( $this->get_custom_id( $field['setting'], $i ) ),
			checked( 1, $this->get_custom_checkbox_value( $field['setting'], $i ), false ),
			esc_html( $field['label'] ),
			esc_attr( $this->get_custom_name( $field['setting'], $i ) )
		);
	}

	/**
	 * Get the value of the custom checkbox field.
	 *
	 * @param string $key
	 * @param integer $i
	 * @return boolean
	 */
	private function get_custom_checkbox_value( $key, $i ) {
		$value   = 0;
		$setting = $this->get_setting( $this->field['setting'] );
		if ( ! empty( $setting[ $i ][ $key ] ) ) {
			return $setting[ $i ][ $key ];
		}
		return $value;
	}
}
