<?php

/**
 * Class SuperSideMeFontAwesome
 * Get the Font Awesome CSS and glyphs, if FontAwesome is enabled.
 *
 * @since 2.5.0
 */
class SuperSideMeFontAwesome extends SuperSideMeGetter {

	/**
	 * Build the inline CSS for all :before elements, with their glyphs
	 * @return mixed
	 */
	public function fontawesome_css() {
		$fontawesome_css  = $this->get_fontawesome_font_styles();
		$fontawesome_css .= '.menu-close:before, .sidr .sub-menu-toggle:before { font-size: 16px; }';
		$fontawesome_css .= $this->get_glyph_css();

		return apply_filters( 'supersideme_modify_glyphs_css', $fontawesome_css, $this->glyphs() );
	}

	/**
	 * Get the correct Font Awesome styling based on v4/v5
	 *
	 * @return string
	 */
	protected function get_fontawesome_font_styles() {
		$fontawesome = '-webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; display: inline-block; font-style: normal; font-variant: normal; font-weight: 900; font-family: \'Font Awesome 5 Free\', \'FontAwesome\'; font-size: 20px;';

		return sprintf( '.slide-nav-link:before, .search-me:before, .menu-close:before, .sidr .sub-menu-toggle:before, .ssme-search:before, .ssme-button:before { %s }', $fontawesome );
	}

	/**
	 * Get the CSS string of all elements with icons.
	 *
	 * @since 2.4.0
	 *
	 * @return string
	 */
	protected function get_glyph_css() {
		$css      = '';
		$elements = $this->get_icon_elements();
		foreach ( $elements as $element => $glyph ) {
			if ( $glyph && false === strpos( $glyph, '\\' ) ) {
				$glyph = '\\' . $glyph;
			}
			if ( $glyph ) {
				$css .= sprintf( '.%s:before { content: \'%s\'; }', $element, $glyph );
			}
		}

		return $css;
	}

	/**
	 * Get an array of all elements with icons/styles.
	 *
	 * @since 2.4.0
	 *
	 * @return array
	 */
	protected function get_icon_elements() {
		$glyphs   = $this->glyphs();
		$elements = array(
			'slide-nav-link'             => $glyphs['slide-nav-link'],
			'slide-nav-link.menu-open'   => $glyphs['slide-nav-link-open'],
			'sidr .menu-close'           => $glyphs['menu-close'],
			'sidr .sub-menu-toggle'      => $glyphs['sub-menu-toggle'],
			'sidr .sub-menu-toggle-open' => $glyphs['sub-menu-toggle-open'],
			'search-me'                  => $glyphs['search'],
			'ssme-search'                => $glyphs['search'],
		);
		foreach ( $this->get_custom_buttons() as $button ) {
			if ( isset( $button['icon'] ) && $button['icon'] ) {
				$class              = 'ssme-button.ssme-' . strtolower( str_replace( ' ', '-', $button['label'] ) );
				$elements[ $class ] = $button['icon'];
			}
		}

		return $elements;
	}

	/**
	 * Set default glyphs for the menu output
	 * @return array font awesome content values
	 */
	public function glyphs() {
		$glyphs = apply_filters(
			'supersideme_default_glyphs',
			array(
				'slide-nav-link'       => '\f0c9',
				'slide-nav-link-open'  => '\f0c9',
				'menu-close'           => '\f00d',
				'sub-menu-toggle'      => '\f107',
				'sub-menu-toggle-open' => '\f106',
				'search'               => '\f002',
			)
		);

		return array_map( 'esc_attr', $glyphs );
	}
}
