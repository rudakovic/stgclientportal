<?php

class SuperSideMeFieldText extends SuperSideMeFieldBase {

	/**
	 * text field input
	 * @since 1.3.3
	 */
	public function do_field() {
		printf(
			'<input type="text" class="%4$s" id="%1$s" name="%3$s" value="%2$s" />',
			esc_attr( $this->get_field_id( $this->field ) ),
			esc_attr( $this->get_setting( $this->field['setting'] ) ),
			esc_attr( $this->get_field_name( $this->field ) ),
			esc_attr( $this->get_field_class() )
		);
	}

	/**
	 * Get the class for the input field.
	 *
	 * @param  array $this->field
	 * @return string
	 */
	private function get_field_class() {
		return empty( $this->field['class'] ) ? 'text' : $this->field['class'];
	}
}
