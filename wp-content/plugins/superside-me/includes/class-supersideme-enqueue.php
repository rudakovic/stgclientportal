<?php

/**
 * Class to output scripts/styles.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2020 Robin Cornett
 * @license   GPL-2.0+
 */
class SuperSideMeEnqueue extends SuperSide_Me_CSS {

	/**
	 * @var $builder SuperSide_Me_Builder
	 */
	protected $builder;

	/**
	 * decide whether to load front end scripts based on whether panel can be built
	 *
	 * @since 1.4.0
	 */
	public function maybe_enqueue() {
		if ( ! $this->panel_has_content() ) {
			return;
		}
		$this->load_styles();
		$this->load_fontawesome();
		$this->load_scripts();
		$builder = $this->get_builder();
		add_action( 'wp_footer', array( $builder, 'localize_scripts' ) );
		add_action( 'wp_footer', array( $builder, 'skip_links' ) );
		add_action( 'wp_footer', array( $this, 'maybe_load_svg' ) );
	}

	/**
	 * Maybe load up our SVG icons.
	 */
	public function maybe_load_svg() {
		if ( $this->use_new_svg() ) {
			return;
		}
		$setting = supersideme_get_settings();
		if ( ! $setting['svg'] && ! $setting['custom'] ) {
			return;
		}
		include_once plugin_dir_path( __FILE__ ) . 'class-supersideme-svg.php';
		$svg = new SuperSideMeSVG();
		$svg->load_svg();
	}

	/**
	 * determine if the superside panel has any content
	 *
	 * @return true/false return false if empty; true if populated. Can be overridden via filter.
	 *
	 * @since 1.4.0
	 */
	public function panel_has_content() {
		if ( apply_filters( 'supersideme_disable', false ) ) {
			return false;
		}
		$builder     = $this->get_builder();
		$search      = (bool) $this->get_setting( 'search' );
		$sidebar     = (bool) is_active_sidebar( 'superside' );
		$menus       = $builder->get_menus();
		$has_content = (bool) ( $search || $sidebar || $menus );

		return (bool) apply_filters( 'supersideme_override_output', $has_content );
	}

	/**
	 * Load CSS styles
	 *
	 * @since 1.6.0
	 */
	public function load_styles() {
		$css_file = apply_filters( 'supersideme_default_css', plugin_dir_url( __FILE__ ) . 'css/supersideme-style.css' );
		$main_css = 'supersideme-style';
		wp_register_style( $main_css, esc_url( $css_file ), array(), SUPERSIDEME_VERSION, 'screen' );
		wp_enqueue_style( $main_css );
		wp_add_inline_style( $main_css, sanitize_text_field( $this->css() ) );
	}

	/**
	 * Load FontAwesome, if it's allowed and if Better Font Awesome is not active.
	 *
	 * @since 2.5.0
	 */
	protected function load_fontawesome() {
		if ( class_exists( 'Better_Font_Awesome_Library' ) ) {
			return;
		}
		$setting = $this->get_setting( 'fontawesome' );
		if ( ! $setting['css'] ) {
			return;
		}
		$fa_handle  = 'font-awesome';
		$fa_version = apply_filters( 'supersideme_fontawesome_version', '5.15.4' );
		$fa_url     = apply_filters( 'supersideme_fontawesome_css', "https://use.fontawesome.com/releases/v{$fa_version}/css/all.css", $fa_version );
		wp_enqueue_style( $fa_handle, $fa_url, array(), $fa_version, 'screen' );
	}

	/**
	 * Enqueue scripts for plugin
	 *
	 * @since 1.0.0
	 */
	public function load_scripts() {

		$minify = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'supersideme-sidr', plugin_dir_url( __FILE__ ) . "js/sidr.me{$minify}.js", array( 'jquery' ), '2.2.1', true );
		$dependent_scripts = array( 'supersideme-sidr' );
		$swipe             = $this->get_setting( 'swipe' );
		if ( $swipe ) {
			wp_register_script( 'supersideme-touchswipe', plugin_dir_url( __FILE__ ) . "js/touchswipe{$minify}.js", $dependent_scripts, '1.6.19', true );
			$dependent_scripts[] = 'supersideme-touchswipe';
		}
		wp_register_script( 'superside-init', plugin_dir_url( __FILE__ ) . "js/supersideme{$minify}.js", $dependent_scripts, SUPERSIDEME_VERSION, true );
		wp_enqueue_script( 'superside-init' );
	}

	/**
	 * Maybe instantiate the builder class.
	 *
	 * @since 2.5.0
	 *
	 * @return \SuperSide_Me_Builder
	 */
	private function get_builder() {
		if ( isset( $this->builder ) ) {
			return $this->builder;
		}
		$this->builder = new SuperSide_Me_Builder();

		return $this->builder;
	}
}
