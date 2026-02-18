<?php

class SuperSideMeFieldSelect extends SuperSideMeFieldBase {

	/**
	 * Generic callback to create a select/dropdown setting.
	 *
	 * @since 2.0.0
	 */
	public function do_field() {
		$function = 'pick_' . $this->field['options'];
		$options  = $this->$function();
		printf( '<label for="%s">', esc_attr( $this->get_field_id( $this->field ) ) );
		printf( '<select id="%s" name="%s">', esc_attr( $this->get_field_id( $this->field ) ), esc_attr( $this->get_field_name( $this->field ) ) );
		foreach ( (array) $options as $name => $key ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $name ), selected( $name, $this->get_setting( $this->field['setting'] ), false ), esc_attr( $key ) );
		}
		echo '</select></label>';
	}
}
