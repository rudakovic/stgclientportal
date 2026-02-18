<?php

/**
 * Class SuperSideMeGetter
 */
class SuperSideMeGetter {

	/**
	 * @var array
	 */
	protected $setting;

	/**
	 * Custom buttons. Set to instance variable for efficiency.
	 *
	 * @var array
	 */
	protected $custom;

	/**
	 * Set options from settings page/filter
	 * @return array options for inline styles and javascript
	 */
	public function options() {
		$button_options    = $this->button_options();
		$panel_options     = $this->panel_options();
		$options           = array_merge( $button_options, $panel_options );
		$options['custom'] = $this->get_custom_buttons();

		return apply_filters( 'supersideme_navigation_options', $options );
	}

	/**
	 * Allow developers to add custom button(s).
	 * @return mixed
	 */
	protected function get_custom_buttons() {
		if ( isset( $this->custom ) ) {
			return $this->custom;
		}
		$buttons = $this->get_setting( 'custom' );
		if ( ! $buttons ) {
			return array();
		}
		foreach ( $buttons as $key => &$value ) {
			if ( ! $value['link'] || ! $value['label'] ) {
				unset( $buttons[ $key ] );
			}
			$value['icon'] = $this->get_svg_markup( $value['icon'] );
		}
		$this->custom = $buttons;

		return apply_filters( 'supersideme_custom_buttons', $this->custom );
	}

	/**
	 * Check if there will be more than one button.
	 * @return boolean
	 */
	protected function extra_buttons() {
		$buttons = false;
		$option  = $this->get_setting( 'search_button' );
		if ( $option || $this->get_custom_buttons() ) {
			return true;
		}

		return $buttons;
	}

	/**
	 * @return array
	 */
	protected function button_options() {
		$option = $this->get_setting();

		return apply_filters(
			'supersideme_button_options',
			array(
				'function'     => 'prepend',
				'button_color' => '',
				'location'     => $option['location'],
				'position'     => $option['position'],
				'width'        => $option['shrink'] ? 'auto' : '100%',
			)
		);
	}

	/**
	 * Options for the menu panel.
	 * @return array
	 */
	protected function panel_options() {
		$option = $this->get_setting();

		return apply_filters(
			'supersideme_panel_options',
			array(
				'background'  => $option['background'],
				'close'       => array(
					'closeText' => $option['close'],
					'closeAria' => __( 'Close Navigation', 'superside-me' ),
				),
				'closeevent'  => '.menu-close',
				'desktop'     => $option['desktop'],
				'displace'    => $option['displace'],
				'link_color'  => $option['link_color'],
				'maxwidth'    => (int) $option['maxwidth'] . 'px',
				'outline'     => $option['outline'],
				'panel_width' => $option['panel_width'] . 'px',
				'side'        => $option['side'],
				'source'      => null,
				'speed'       => $option['speed'],
			)
		);
	}

	/**
	 * Build the close button.
	 * @param $setting
	 *
	 * @return string
	 */
	protected function get_close_button( $setting ) {
		_deprecated_function( __FUNCTION__, '2.6.1' );
		$close = sprintf( '<button class="menu-close" role="button" aria-pressed="false" aria-label="%1$s">%2$s</button>', __( 'Close Navigation', 'superside-me' ), $setting['close'] );
		if ( $setting['svg'] ) {
			$list  = $this->get_menu_svg();
			$close = sprintf(
				'<button class="menu-close" role="button" aria-pressed="false" aria-label="%1$s">%3$s %2$s</button>',
				__( 'Close Navigation', 'superside-me' ),
				$setting['close'],
				$list['close']
			);
		}

		return $close;
	}

	/**
	 * Get the plugin setting.
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	protected function get_setting( $key = '' ) {
		if ( isset( $this->setting ) ) {
			return $key ? $this->setting[ $key ] : $this->setting;
		}
		$this->setting = supersideme_get_settings();

		return $key ? $this->setting[ $key ] : $this->setting;
	}

	/**
	 * Get the SVG markup for localization.
	 * @return array|bool
	 */
	protected function get_menu_svg() {
		$setting = $this->get_setting( 'svg' );
		if ( ! $setting ) {
			return false;
		}

		$svg = array();
		foreach ( $this->get_svg_icons_list() as $key => $value ) {
			$svg[ $key ] = $this->get_svg_markup( $value );
		}

		return $svg;
	}

	/**
	 * Get the list of SVG icons.
	 * @return array
	 */
	protected function get_svg_icons_list() {
		$setting = $this->get_setting( 'icons' );
		$icons   = array();
		foreach ( $setting as $key => $value ) {
			$icons[ $key ] = $value;
		}

		return $icons;
	}

	/**
	 * Return SVG markup.
	 *
	 * @param string $icon
	 * @param array  $args     {
	 *                        Optional parameters needed to display an SVG.
	 *
	 * @return string SVG markup.
	 */
	protected function get_svg_markup( $icon, $args = array() ) {
		if ( is_array( $icon ) ) {
			$args = $icon;
			$icon = $args['icon'];
		}
		if ( ! $icon ) {
			return '';
		}
		if ( $this->use_new_svg() ) {
			include_once 'class-supersideme-svg.php';
			$svg = new SuperSideMeSVG();

			return $svg->svg( $icon );
		}
		$defaults        = array(
			'title'    => '',
			'desc'     => '',
			'fallback' => false,
		);
		$args            = wp_parse_args( $args, $defaults );
		$aria_hidden     = ' aria-hidden="true"';
		$aria_labelledby = '';
		$title           = '';
		$fallback        = '';
		$xlink           = apply_filters( 'supersideme_svg_xlink', "#{$icon}" );

		if ( $args['title'] ) {
			$aria_hidden     = '';
			$unique_id       = uniqid();
			$aria_labelledby = ' aria-labelledby="title-' . $unique_id . '"';
			$title           = sprintf(
				'<title id="title-%s">%s</title>',
				$unique_id,
				esc_html( $args['title'] )
			);
			if ( $args['desc'] ) {
				$aria_labelledby = ' aria-labelledby="title-' . $unique_id . ' desc-' . $unique_id . '"';
				$title          .= sprintf(
					'<desc id="desc-%s">%s</desc>',
					$unique_id,
					esc_html( $args['desc'] )
				);
			}
		}

		if ( $args['fallback'] ) {
			$fallback = '<span class="svg-fallback icon-' . esc_attr( $icon ) . '"></span>';
		}

		return apply_filters(
			'supersideme_svg_icon',
			sprintf(
				'<svg class="icon ssme-icon %1$s" %2$s%3$s>%4$s <use href="#%1$s" xlink:href="%6$s"></use> %5$s</svg>',
				esc_attr( $icon ),
				$aria_hidden,
				$aria_labelledby,
				$title,
				$fallback,
				$xlink
			),
			$icon,
			$args,
			$aria_hidden,
			$aria_labelledby,
			$title,
			$fallback,
			$xlink
		);
	}

	/**
	 * Whether to use the new SVG (direct SVG) rather than sprites.
	 * If the original sprite path was customized, that should take precedence over the new icons.
	 * The original filter could have returned a populated array with style and path, or
	 * simply returned `false` to short-circuit the paths. Checking specifically for an
	 * empty array should not conflict with the original filter.
	 *
	 * @since 2.8.0
	 * @return bool
	 */
	protected function use_new_svg() {
		$use_new_svg = apply_filters( 'supersideme_svg', array() );

		return empty( $use_new_svg ) && is_array( $use_new_svg );
	}
}
