<?php

class SuperSideMeFieldRadio extends SuperSideMeFieldBase {

	/**
	 * Generic function for radio buttons
	 */
	public function do_field() {
		echo '<fieldset>';
		printf( '<legend class="screen-reader-text">%s</legend>', esc_attr( $this->field['label'] ) );
		foreach ( $this->field['choices'] as $key => $button ) {
			printf(
				'<label for="%5$s-%2$s" style="margin-right:12px !important;"><input type="radio" id="%5$s-%2$s" name="%1$s" value="%2$s"%3$s />%4$s</label>  ',
				esc_attr( $this->get_field_name( $this->field ) ),
				esc_attr( $key ),
				checked( $key, $this->get_setting( $this->field['setting'] ), false ),
				esc_attr( $button ),
				esc_attr( $this->get_field_id( $this->field ) )
			);
		}
		echo '</fieldset>';
	}
}
