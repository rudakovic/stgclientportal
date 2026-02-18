<?php

class SuperSideMeFieldCheckbox_array extends SuperSideMeFieldBase {

	/**
	 * Set up choices for checkbox array
	 */
	public function do_field() {
		$setting = $this->get_setting( $this->field['setting'] );
		foreach ( $this->field['choices'] as $key => $label ) {
			printf(
				'<input type="hidden" name="%s[%s]" value="0" />',
				esc_attr( $this->get_field_name( $this->field ) ),
				esc_attr( $key )
			);
			printf(
				'<input type="checkbox" name="%4$s[%1$s]" id="%5$s-%1$s" value="1"%2$s class="code" /> <label for="%5$s-%1$s">%3$s</label><br />',
				esc_attr( $key ),
				checked( 1, $setting[ $key ], false ),
				esc_html( $label ),
				esc_attr( $this->get_field_name( $this->field ) ),
				esc_attr( $this->get_field_id( $this->field ) )
			);
		}
	}
}
