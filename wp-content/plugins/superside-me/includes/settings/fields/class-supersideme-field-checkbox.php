<?php

class SuperSideMeFieldCheckbox extends SuperSideMeFieldBase {

	/**
	 * Do the checkbox field.
	 */
	public function do_field() {
		printf( '<input type="hidden" name="%s" value="0" />', esc_attr( $this->get_field_name( $this->field ) ) );
		printf(
			'<label for="%1$s"><input type="checkbox" name="%4$s" id="%1$s" value="1" %2$s class="code" />%3$s</label>',
			esc_attr( $this->get_field_id( $this->field ) ),
			checked( 1, esc_attr( $this->get_setting( $this->field['setting'] ) ), false ),
			esc_attr( $this->field['label'] ),
			esc_attr( $this->get_field_name( $this->field ) )
		);
	}
}
