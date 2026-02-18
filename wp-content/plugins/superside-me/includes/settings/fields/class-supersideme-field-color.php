<?php

class SuperSideMeFieldColor extends SuperSideMeFieldBase {

	/**
	 * Set color for an element
	 *
	 * @since 1.3.0
	 */
	public function do_field() {
		printf(
			'<input type="text" name="%1$s" value="%2$s" class="color-field">',
			esc_attr( $this->get_field_name( $this->field ) ),
			esc_attr( $this->get_setting( $this->field['setting'] ) )
		);
	}
}
