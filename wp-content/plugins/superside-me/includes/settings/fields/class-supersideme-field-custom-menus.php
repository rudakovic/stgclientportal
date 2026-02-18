<?php

class SuperSideMeFieldCustomMenus extends SuperSideMeFieldBase {

	/**
	 * settings for each registered menu location
	 *
	 * @param $this->field array
	 *
	 * @since 1.3.0
	 */
	public function do_field() {
		$this->do_heading_input( $this->field['location'] );
		$this->do_skip_input( $this->field['location'] );
	}

	/**
	 * Do the heading input field for each menu location.
	 *
	 * @param string $location
	 */
	private function do_heading_input( $location ) {
		$setting = $this->get_setting( 'menus' );
		if ( empty( $setting['heading'][ $location ] ) ) {
			$setting['heading'][ $location ] = '';
		}
		printf(
			'<label for="%4$s[menus][heading][%1$s]"><input type="text" class="text" id="%4$s[menus][heading][%1$s]" name="%4$s[menus][heading][%1$s]" value="%2$s" /> %3$s</label><br />',
			esc_attr( $location ),
			esc_attr( $setting['heading'][ $location ] ),
			esc_html__( '[Visible] Heading', 'superside-me' ),
			esc_attr( $this->page )
		);
	}

	/**
	 * Do the skip checkbox for each menu location.
	 *
	 * @param string $location
	 */
	private function do_skip_input( $location ) {
		$setting = $this->get_setting( 'menus' );
		if ( empty( $setting['skip'][ $location ] ) ) {
			$setting['skip'][ $location ] = 0;
		}

		printf( '<input type="hidden" name="%1$s[menus][skip][%2$s]" value="0" />', esc_attr( $this->page ), esc_attr( $location ) );
		if ( 'supersideme' === $location ) {
			return;
		}
		printf(
			'<input type="checkbox" name="%4$s[menus][skip][%1$s]" id="%4$s[menus][skip][%1$s]" value="1"%2$s class="code" /> <label for="%4$s[menus][skip][%1$s]">%3$s</label><br />',
			esc_attr( $location ),
			checked( 1, $setting['skip'][ $location ], false ),
			esc_html__( 'Do not add this menu to the panel.', 'superside-me' ),
			esc_attr( $this->page )
		);
	}
}
