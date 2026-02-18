<?php

include_once 'class-supersideme-field-custom.php';

class SuperSideMeFieldCustomCustom extends SuperSideMeFieldCustom {

	/**
	 * Build the custom buttons settings tab/table.
	 *
	 *
	 * @since 2.4.0
	 */
	public function do_field() {
		echo '<div id="ssme-custom-buttons" class="widefat icons-container">';
		$fields  = include plugin_dir_path( dirname( __FILE__ ) ) . 'definitions/menu-bar.php';
		$count   = count( $this->get_setting( $this->field['setting'] ) );
		$buttons = ! $count ? 2 : $count + 1;
		for ( $i = 0; $i < $buttons; $i ++ ) {
			echo '<div class="button-row">';
			foreach ( $fields as $field ) {
				$heading = empty( $field['heading'] ) ? $field['label'] : $field['heading'];
				$class   = strtolower( str_replace( ' ', '-', $heading ) );
				printf(
					'<div class="field-container field-%s %s" data-label="%s">',
					esc_attr( $field['type'] ),
					esc_attr( $class ),
					esc_html( $heading )
				);
				printf(
					'<h4 class="heading-%s">%s</h4>',
					esc_attr( $field['type'] ),
					esc_html( $heading )
				);
				$function = "{$field['type']}_fields";
				$this->$function( $field, $i );
				echo '</div>';
			}
			printf( '<div><button class="ssme-remove-button button-secondary">%s</button></div>', esc_html__( 'Remove Button', 'superside-me' ) );
			echo '</div>';
		}
		echo '</div>';
		printf( '<p><button class="button-secondary" id="ssme-add-button">%s</button></p>', esc_html__( 'Add Button', 'superside-me' ) );
	}
}
