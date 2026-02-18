<?php

/**
 * Class SuperSideMeSVG
 */
class SuperSideMeSVG {

	public function svg( $icon ) {
		$contents = $this->get_icon_file_contents( $icon );
		if ( $contents ) {
			return $this->update_svg(
				$contents,
				$this->get_icon_args( $icon )
			);
		}

		return '';
	}

	/**
	 * Get the icon args, merged with defaults.
	 *
	 * @since 3.2.0
	 * @param array $args
	 * @return array
	 */
	private function get_icon_args( $icon, $args = array() ) {
		$defaults = array(
			'class' => "supersideme__icon ssme-icon {$icon}",
		);

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * Get the icon file to include.
	 *
	 * @since 3.2.0
	 * @param string $icon
	 * @return string
	 */
	private function get_icon_file_contents( $icon ) {
		$located_icon = $this->locate_icon( $icon );

		return $located_icon && file_exists( $located_icon ) ? file_get_contents( $located_icon ) : false;
	}

	/**
	 * Gets the path for the icons. To use a custom icon, add the svg files
	 * to the theme, in an `assets/svg` directory. Theme icons will take
	 * precedence over the plugin icons.
	 *
	 * @since 3.2.0
	 * @return array
	 */
	public function get_icon_paths() {
		return apply_filters(
			'supersideme_svg_paths',
			array(
				trailingslashit( get_stylesheet_directory() ) . 'assets/svg',
				trailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) . 'assets/svg',
			)
		);
	}

	/**
	 * Locate a specific icon.
	 *
	 * @since 3.2.0
	 * @param string $icon
	 * @return string|boolean
	 */
	private function locate_icon( $icon ) {
		$located   = false;
		$locations = $this->get_icon_paths();
		foreach ( $locations as $location ) {
			$file = trailingslashit( $location ) . "{$icon}.svg";
			if ( file_exists( $file ) ) {
				$located = $file;
				break;
			}
		}

		return $located;
	}

	/**
	 * Update the SVG icon with class, style, a11y attributes.
	 *
	 * @since 3.2.0
	 * @param string $svg  The SVG.
	 * @param array  $args
	 * @return string
	 */
	private function update_svg( $svg, $args ) {
		$html = '';
		if ( ! $svg ) {
			return $html;
		}
		$dom = $this->get_document( $svg );

		foreach ( $dom->getElementsByTagName( 'svg' ) as $item ) {
			foreach ( $this->svg_attributes( $args ) as $key => $value ) {
				if ( $value ) {
					$item->setAttribute( $key, $value );
				}
			}

			return $dom->saveHTML();
		}

		return $html;
	}

	/**
	 * Get all of the attributes to be added to the SVG.
	 *
	 * @since 3.2.0
	 * @param array $args
	 * @return array
	 */
	private function svg_attributes( $args ) {
		return array(
			'class'       => $args['class'],
			'fill'        => 'currentcolor',
			'height'      => '1em',
			'width'       => '1em',
			'aria-hidden' => 'true',
			'focusable'   => 'false',
			'role'        => 'img',
		);
	}

	/**
	 * Get the SVG content as an object.
	 *
	 * @since 3.2.0
	 * @param string $svg The SVG.
	 * @return object
	 */
	private function get_document( $svg ) {
		$doc = new DOMDocument();

		libxml_use_internal_errors( true ); // turn off errors for HTML5
		if ( function_exists( 'mb_convert_encoding' ) ) {
			$currentencoding = mb_internal_encoding();
			$content         = mb_convert_encoding( $svg, 'HTML-ENTITIES', $currentencoding ); // convert the feed from XML to HTML
		} elseif ( function_exists( 'iconv' ) ) {
			// not sure this is an improvement over straight load (for special characters)
			$currentencoding = iconv_get_encoding( 'internal_encoding' );
			$content         = iconv( $currentencoding, 'ISO-8859-1//IGNORE', $svg );
		} else {
			$content = $svg;
		}
		if ( defined( 'LIBXML_HTML_NOIMPLIED' ) && defined( 'LIBXML_HTML_NODEFDTD' ) ) {
			$doc->LoadHTML( $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		} else {
			$doc->LoadHTML( $content );
		}
		libxml_clear_errors(); // now that it's loaded, go ahead

		return $doc;
	}

	/**
	 * Add SVG definitions to the footer.
	 *
	 * @since 2.4.0
	 */
	public function load_svg() {
		$svg = $this->get_svg();
		if ( empty( $svg['styles'] ) ) {
			return;
		}
		foreach ( (array) $svg['styles'] as $style ) {
			$file = trailingslashit( $svg['path'] ) . $style . '.svg';
			if ( file_exists( $file ) ) {
				include_once $file;
			}
		}
	}

	/**
	 * Get the correct path for the SVG icons.
	 * @return mixed|array
	 */
	protected function get_svg() {

		return apply_filters(
			'supersideme_svg',
			array(
				'styles' => $this->get_svg_styles(),
				'path'   => plugin_dir_path( __FILE__ ) . 'sprites',
			)
		);
	}

	/**
	 * Define the list of SVG style filenames.
	 * @return array
	 */
	protected function get_svg_styles() {
		$icons  = $this->get_all_icons();
		$styles = array( $this->get_base_icons( $icons ) );
		$brands = $this->get_brand_icons( $icons );
		if ( $brands ) {
			$styles[] = $brands;
		}

		return $styles;
	}

	/**
	 * Get the base set of icons. Use the minimal SVG list if possible.
	 *
	 * @param array $icons
	 * @return string
	 */
	protected function get_base_icons( $icons ) {
		$icons_not_in_minimal = $this->icons_not_in_minimal( $icons );

		return empty( $icons_not_in_minimal ) ? 'minimal' : 'solid';
	}

	/**
	 * Get all icons, default or custom, used in the plugin output.
	 *
	 * @return array
	 */
	private function get_all_icons() {
		$icons        = supersideme_get_settings( 'icons' );
		$buttons      = supersideme_get_settings( 'custom' );
		$button_icons = wp_list_pluck( $buttons, 'icon' );

		return array_merge( $icons, $button_icons );
	}

	/**
	 * Get the brand icons, still using the minimal SVG if possible.
	 *
	 * @param array $icons
	 * @return bool|string
	 */
	protected function get_brand_icons( $icons ) {
		if ( array_intersect( $this->brands(), $icons ) ) {
			return 'brands';
		}

		return false;
	}

	/**
	 * Define the standard/default icons--if they're not changed, load the minimal
	 * SVG icon set instead of ALL the Font Awesome icons.
	 *
	 * @return array
	 */
	protected function minimal() {
		return include plugin_dir_path( __FILE__ ) . 'svg/minimal.php';
	}

	/**
	 * See if the icons provided are not in the minimal set.
	 *
	 * @param $icons
	 *
	 * @return array
	 */
	protected function icons_not_in_minimal( $icons ) {
		return array_diff( array_values( $icons ), $this->minimal() );
	}

	/**
	 * Get the list of brand icons in Font Awesome.
	 * @return array
	 */
	protected function brands() {
		return include plugin_dir_path( __FILE__ ) . 'svg/brands.php';
	}
}
