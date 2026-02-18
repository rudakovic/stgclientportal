<?php

class SuperSideMeFieldNumber extends SuperSideMeFieldBase {

	/**
	 * Generic callback to create a number field setting.
	 *
	 * @since 1.8.0
	 */
	public function do_field() {
		printf(
			'<label for="%3$s"><input type="number" step="%6$s" min="%1$s" max="%2$s" id="%3$s" name="%5$s" value="%4$s" class="small-text" />%7$s</label>',
			$this->field['input_attrs']['min'],
			(int) $this->field['input_attrs']['max'],
			esc_attr( $this->get_field_id( $this->field ) ),
			esc_attr( $this->get_setting( $this->field['setting'] ) ),
			esc_attr( $this->get_field_name( $this->field ) ),
			(int) $this->get_step(),
			esc_attr( $this->get_value() )
		);
	}

	/**
	 * Get the increment value for the number field.
	 *
	 * @return mixed
	 */
	private function get_step() {
		return ! empty( $this->field['step'] ) ? esc_attr( $this->field['step'] ) : (int) 1;
	}

	/**
	 * Get the field value.
	 *
	 * @return string|int
	 */
	private function get_value() {
		return ! empty( $this->field['value'] ) ? esc_attr( $this->field['value'] ) : '';
	}
}
