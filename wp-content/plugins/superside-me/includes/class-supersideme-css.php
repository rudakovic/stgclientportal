<?php
/**
 * CSS Output class.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2020 Robin Cornett
 * @license   GPL-2.0+
 */

class SuperSide_Me_CSS extends SuperSideMeGetter {

	/**
	 * Add a no-js body class to the body element.
	 * Changed from being added to the html element, as that was kind of a hack.
	 *
	 * @param array $classes
	 * @return array
	 * @since 2.8.0
	 */
	public function no_js_body_class( $classes ) {
		$classes[] = 'no-js';

		return $classes;
	}

	/**
	 * Use javascript to remove the no-js class from the html element.
	 *
	 * @since 1.6.0
	 */
	public function add_js_class() {
		?>
<script>/* <![CDATA[ */(function(){var c = document.body.classList;c.remove('no-js');c.add('js');})();/* ]]> */</script>
		<?php
	}

	/**
	 * Backwards compatible no-js replacement--runs if the wp_body_open hook
	 * is not present.
	 *
	 * @since 2.8.1
	 */
	public function back_compat_add_js_class() {
		if ( did_action( 'wp_body_open' ) ) {
			return;
		}
		$this->add_js_class();
	}

	/**
	 * Custom CSS.
	 *
	 * Outputs custom CSS to control the look of the menu.
	 */
	public function css() {
		return $this->minify( $this->display_css() . $this->menu_css() );
	}

	/**
	 * Quick and dirty way to mostly minify CSS.
	 *
	 * @since  1.0.0
	 * @author Gary Jones
	 *
	 * @param string $css CSS to minify
	 *
	 * @return string minified CSS
	 */
	protected function minify( $css ) {

		$css = preg_replace( '/\s+/', ' ', $css );
		$css = preg_replace( '/(\s+)(\/\*(.*?)\*\/)(\s+)/', '$2', $css );
		$css = preg_replace( '~/\*(?![\!|\*])(.*?)\*/~', '', $css );
		$css = preg_replace( '/;(?=\s*})/', '', $css );
		$css = preg_replace( '/(,|:|;|\{|}|\*\/|>) /', '$1', $css );
		$css = preg_replace( '/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css );
		$css = preg_replace( '/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css );
		$css = preg_replace( '/0 0 0 0/', '0', $css );
		$css = preg_replace( '/#([a-f0-9])\\1([a-f0-9])\\2([a-f0-9])\\3/i', '#\1\2\3', $css );

		return trim( $css );
	}

	/**
	 * Build array of elements to be hidden by inline CSS
	 * @return string general navigation elements
	 *
	 * @since 1.7.1
	 */
	protected function hidden_elements() {
		$hidden_elements = array(
			'nav',
			'#nav',
			'.nav-primary',
			'.nav-secondary',
			'.supersideme .site-header .secondary-toggle',
			'.menu-toggle',
		);
		$hidden_elements = $this->merge( 'hidden', $hidden_elements );

		return $this->convert_arrays( $hidden_elements );
	}

	/**
	 * Build elements to display as block elements.
	 * @return string
	 * @since 2.0.0
	 */
	protected function block_elements() {
		$block_elements = array(
			'.slide-nav-link',
			'.ssme-search',
			'.button.ssme-button.ssme-custom',
		);
		$block_elements = $this->merge( 'block', $block_elements );

		return $this->convert_arrays( $block_elements );
	}

	/**
	 * Merge the default and setting for each display element.
	 *
	 * @param $setting string the setting key
	 * @param $array   array default array of elements
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function merge( $setting, $array ) {
		$option = $this->get_setting();
		if ( ! empty( $option[ $setting ] ) ) {
			$array = array_merge( $array, (array) $option[ $setting ] );
		}

		/**
		 * Add a filter to modify the display elements
		 * @since 2.0.0
		 */
		return apply_filters( "supersideme_{$setting}_elements", $array );
	}

	/**
	 * Convert merged arrays to a string.
	 *
	 * @param $array
	 *
	 * @return string
	 * @since 2.0.0
	 */
	protected function convert_arrays( $array ) {
		return is_array( $array ) ? implode( ',', $array ) : $array;
	}

	/**
	 * Set up the inline CSS for the menu (button) display
	 * @return string inline CSS
	 */
	protected function display_css() {
		$options     = $this->options();
		$display_css = $this->get_media_query_css();
		if ( ! $options['desktop'] ) {
			$display_css = sprintf( '@media only screen and (max-width: %s) {%s}', $options['maxwidth'], $display_css );
		}

		/**
		 * Filter on the entire "display" CSS.
		 * This filter is less desirable to use than the intermediate filters.
		 *
		 * @param string $display_css The output CSS
		 * @param array  $options     The plugin options, parsed
		 * @param string $hidden      The elements to be hidden when SuperSide Me buttons are visible
		 * @param string $block       The elements to be displayed: block when SuperSide Me buttons are visible
		 */
		return apply_filters( 'supersideme_modify_display_css', $display_css, $options, $this->hidden_elements(), $this->block_elements() );
	}

	/**
	 * Get the CSS to be inserted into the media query.
	 *
	 * @return string
	 */
	private function get_media_query_css() {
		$hidden      = $this->hidden_elements();
		$block       = $this->block_elements();
		$display_css = '';
		if ( ! empty( $hidden ) ) {
			$display_css .= apply_filters( 'supersideme_display_none_css', sprintf( ' %s { display: none; }', $hidden ), $hidden );
		}
		if ( ! empty( $block ) ) {
			$display_css .= apply_filters( 'supersideme_display_block_css', sprintf( ' %s { display: block; }', $block ), $block );
		}
		/**
		 * Filter on the "display" CSS before it's wrapped in a media query.
		 * @param string $display_css The output CSS
		 * @param array  $options     The plugin options, parsed
		 * @param string $hidden      The elements to be hidden when SuperSide Me buttons are visible
		 * @param string $block       The elements to be displayed: block when SuperSide Me buttons are visible
		 */
		return apply_filters( 'supersideme_pre_display_css', $display_css, $this->options(), $hidden, $block );
	}

	/**
	 * Set up the inline CSS for the panel itself
	 * @return string inline CSS for side panel
	 */
	protected function menu_css() {

		$options = $this->options();
		$setting = $this->get_setting();

		$menu_css = sprintf(
			'.sidr { width: %1$s; } .sidr.left { left: -%1$s; } .sidr.right { right: -%1$s; }',
			$options['panel_width']
		);
		$position = $options['position'];
		$width    = $options['width'];
		$stick    = '';
		if ( 'bottom' === $position ) {
			$position = 'fixed';
			$stick    = ' bottom: 0;';
		}
		if ( $this->extra_buttons() ) {
			$container = sprintf(
				'position: %s; %s: 0; width: %s;',
				$position,
				$options['side'],
				$width
			);
			if ( 'sticky' === $position ) {
				$stick = ' top: 0;';
			}
			$position  = 'relative';
			$width     = 'auto';
			$menu_css .= sprintf( '.ssme-buttons { %s }', $container . $stick );
			$menu_css .= $this->search_button_css();
		}

		// SuperSide menu CSS
		$menu_css   .= sprintf(
			'.slide-nav-link { %6$s %2$s: 0; color: %3$s; position: %4$s; width: %5$s;%9$s }
			.sidr { %8$s color: %3$s; }
			.sidr h3, .sidr h4, .sidr .widget, .sidr p { color: %3$s; }
			.slide-nav-link:focus, .sidr:focus, .sidr a:focus, .menu-close:focus, .sub-menu-toggle:focus { outline: %3$s %7$s 1px; }
			.sidr a, .sidr a:focus, .sidr a:active, .sidr button, .sidr .sub-menu-toggle:before { color: %3$s; }
			.search-me { color: %1$s; }',
			$options['background'], // %1$s
			$options['side'], // %2$s
			$options['link_color'], // %3$s
			$position, // %4$s
			$width, // %5$s
			empty( $options['button_color'] ) ? $this->background_color( $options['background'], $setting['opacity'] ) : $this->background_color( $options['button_color'] ), // %6$s
			$options['outline'], // %7$s
			$this->background_color( $options['background'], $setting['opacity'] ), // %8$s
			$this->extra_buttons() ? '' : $stick // %9$s
		);
		$fontawesome = $this->get_fontawesome();
		$menu_css   .= $fontawesome['css'];

		return apply_filters( 'supersideme_modify_menu_css', $menu_css, $options, $setting, $fontawesome['glyphs'] );
	}

	/**
	 * Get the possible FontAwesome CSS and glyphs.
	 * None are returned if SVG are enabled.
	 *
	 * @return array
	 * @since 2.5.0
	 */
	protected function get_fontawesome() {
		$option   = $this->get_setting();
		$fa_array = array(
			'css'    => '',
			'glyphs' => array(),
		);
		if ( $option['svg'] ) {
			$fa_array['css'] = '.sidr .sub-menu-toggle:before { content: none; }';

			return $fa_array;
		}
		if ( ! $option['fontawesome']['glyphs'] ) {
			return $fa_array;
		}

		include_once 'class-supersideme-fontawesome.php';
		$fontawesome = new SuperSideMeFontAwesome();

		return array(
			'css'    => $fontawesome->fontawesome_css(),
			'glyphs' => $fontawesome->glyphs(),
		);
	}

	/**
	 * If the search button is enabled, add the inline CSS for it.
	 * @return string
	 * @since 2.2.0
	 */
	protected function search_button_css() {
		$menu_css = '';
		$option   = $this->get_setting();
		if ( $option['shrink'] ) {
			return $menu_css;
		}
		$elements = array();
		if ( ! $option['search_button_text'] && ! $option['navigation'] ) {
			$elements[] = '.ssme-buttons > button';
		} elseif ( $option['navigation'] ) {
			$elements[] = 'button.slide-nav-link';
		}
		if ( $option['search_button_text'] ) {
			$elements[] = 'button.ssme-search';
		}
		if ( ! empty( $elements ) ) {
			$menu_css .= sprintf( ' %s { flex-grow: 1; }', implode( ',', $elements ) );
		}

		return $menu_css;
	}

	/**
	 * Set the background color for panel/button. Hex, with optional rgba, if opacity is set.
	 *
	 * @param $background_color string background color (hex value)
	 * @param $opacity          string
	 *
	 * @return string
	 * @since 2.0.0
	 */
	protected function background_color( $background_color, $opacity = '' ) {
		$background = sprintf( 'background-color: %s;', $background_color );
		if ( ! $opacity || 100 === $opacity ) {
			return $background;
		}
		$color = $this->hex2rgb( $background_color );
		if ( ! $color ) {
			return $background;
		}
		$converted   = $opacity / 100;
		$background .= sprintf( ' background-color: rgba(%s,%s);', $color, $converted );

		return $background;
	}

	/**
	 * Converts a hex color to rgb values, separated by commas
	 *
	 * @param $hex
	 *
	 * @return bool|string false if input is not a 6 digit hex color; string if converted
	 * @since 2.0.0
	 */
	protected function hex2rgb( $hex ) {
		$hex = '#' === $hex[0] ? substr( $hex, 1 ) : $hex;
		if ( 6 !== strlen( $hex ) ) {
			return false;
		}
		$r   = hexdec( substr( $hex, 0, 2 ) );
		$g   = hexdec( substr( $hex, 2, 2 ) );
		$b   = hexdec( substr( $hex, 4, 2 ) );
		$rgb = array( $r, $g, $b );

		return implode( ',', $rgb ); // returns the rgb values separated by commas
	}
}
