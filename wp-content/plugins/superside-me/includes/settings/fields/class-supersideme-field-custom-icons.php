<?php

require_once 'class-supersideme-field-custom.php';

class SuperSideMeFieldCustomIcons extends SuperSideMeFieldCustom {

	/**
	 * Build the fields for the icons tab.
	 * @since 2.4.0
	 */
	public function do_field() {
		$fields = include plugin_dir_path( dirname( __FILE__ ) ) . 'definitions/icons-custom.php';
		echo '<div class="icons-container">';
		foreach ( $fields as $field ) {
			$field['class'] = 'ssme-iconpicker';
			printf(
				'<div class="field-container field-type %s" data-label="%s">',
				esc_attr( strtolower( str_replace( ' ', '-', $field['label'] ) ) ),
				esc_html( $field['label'] )
			);
			printf(
				'<h4 class="heading-type"><label for="%s-%s">%s</label></h4>',
				esc_attr( $this->get_field_id( $this->field ) ),
				esc_attr( $field['setting'] ),
				esc_html( $field['label'] )
			);
			$this->text_fields( $field );
			echo '</div>';
		}
		echo '</div>';
	}
}
