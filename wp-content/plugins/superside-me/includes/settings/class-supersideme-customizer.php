<?php
/**
 * SuperSide Me Customizer class.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2020 Robin Cornett
 * @license   GPL-2.0+
 */

class SuperSide_Me_Customizer extends SuperSideMeGetSetting {

	/**
	 * Panel for the Customizer.
	 * @var string $panel
	 */
	protected $panel = 'supersideme';

	/**
	 * SuperSide Me Settings class.
	 * @var $settings SuperSide_Me_Settings
	 */
	protected $settings;

	/**
	 * Adds the individual sections, settings, and controls to the theme customizer
	 *
	 * @param $wp_customize WP_Customize_Manager
	 *
	 * @uses add_section() adds a section to the customizer
	 */
	public function customizer( $wp_customize ) {

		/**
		 * Add a filter to optionally disable the customizer panel
		 * example: add_filter( 'supersideme_disable_customizer_panel', '__return_true' );
		 *
		 * @since 1.8.0
		 */
		if ( apply_filters( 'supersideme_disable_customizer_panel', false ) ) {
			return;
		}

		$setting = get_option( 'supersideme', false );
		if ( ! $setting ) {
			add_option( 'supersideme', $this->defaults() );
		}

		$this->add_panels( $wp_customize );
		$this->add_sections( $wp_customize );
		$this->add_fields( $wp_customize );

		add_action( 'customize_preview_init', array( $this, 'preview' ) );
	}

	/**
	 * Add panel(s) to the customizer.
	 *
	 * @param object $wp_customize \WP_Customize_Manager
	 */
	private function add_panels( $wp_customize ) {
		$wp_customize->add_panel(
			$this->panel,
			array(
				'title'       => __( 'SuperSide Me', 'superside-me' ),
				'description' => __( 'Only certain styling settings are available in the Customizer; more can be found on the SuperSide Me settings page.', 'superside-me' ),
				'priority'    => 105,
				'capability'  => 'manage_options',
			)
		);
	}

	/**
	 * Add section(s) to the customizer.
	 *
	 * @param object $wp_customize \WP_Customize_Manager
	 */
	private function add_sections( $wp_customize ) {
		$sections = include 'definitions/sections.php';

		foreach ( $sections as $section ) {
			$wp_customize->add_section(
				"{$this->panel}_{$section['id']}",
				array(
					'title'       => $section['label'],
					'panel'       => $this->panel,
					'description' => isset( $section['description'] ) ? $section['description'] : '',
				)
			);
		}
	}

	/**
	 * Build the SuperSide Me Customizer settings panel.
	 *
	 * @param $wp_customize \WP_Customize_Manager
	 */
	protected function add_fields( $wp_customize ) {
		$definitions = new SuperSideMeDefineSettings();
		$this->add_controls( $wp_customize, $definitions->register_fields() );
	}

	/**
	 * @param $wp_customize \WP_Customize_Manager
	 * @param $fields
	 */
	protected function add_controls( $wp_customize, $fields ) {
		foreach ( $fields as $setting ) {
			if ( empty( $setting['type'] ) ) {
				continue;
			}
			if ( ! empty( $setting['skip'] ) ) {
				continue;
			}
			$this->add_setting( $wp_customize, $setting );
			if ( 'color' === $setting['type'] ) {
				$this->do_color_setting( $wp_customize, $setting );
				continue;
			}
			$args          = wp_parse_args( $setting, $this->get_control_defaults() );
			$args['label'] = 'checkbox' === $setting['type'] ? $setting['label'] : $setting['title'];
			$args          = array_merge( $args, $this->get_control_section_settings( $setting ) );
			$wp_customize->add_control(
				"{$this->panel}[{$setting['setting']}]",
				$args
			);
		}
	}

	/**
	 * Get the control defaults.
	 *
	 * @return void
	 */
	private function get_control_defaults() {
		return array(
			'label'       => '',
			'type'        => '',
			'description' => '',
			'choices'     => array(),
			'input_attrs' => array(),
		);
	}

	/**
	 * Get the section and "settings" parameter for a control.
	 *
	 * @param  array $setting
	 * @return array
	 */
	private function get_control_section_settings( $setting ) {
		return array(
			'section'  => "{$this->panel}_{$setting['section']}",
			'settings' => "{$this->panel}[{$setting['setting']}]",
		);
	}

	/**
	 * @param $wp_customize WP_Customize_Manager
	 * @param $setting
	 */
	protected function do_color_setting( $wp_customize, $setting ) {
		$args = array(
			'description' => $setting['description'],
			'label'       => $setting['title'],
		);
		$args = array_merge( $args, $this->get_control_section_settings( $setting ) );

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				"{$this->panel}[{$setting['setting']}]",
				$args
			)
		);
	}

	/**
	 * @param $wp_customize WP_Customize_Manager
	 * @param $setting
	 */
	protected function add_setting( $wp_customize, $setting ) {
		if ( 'color' === $setting['type'] ) {
			$setting['transport'] = 'postMessage';
		}
		$defaults = $this->defaults();
		$wp_customize->add_setting(
			"{$this->panel}[{$setting['setting']}]",
			array(
				'capability'        => 'manage_options',
				'default'           => $defaults[ $setting['setting'] ],
				'sanitize_callback' => $this->sanitize_callback( $setting['type'], $setting['setting'] ),
				'type'              => 'option',
				'transport'         => isset( $setting['transport'] ) ? $setting['transport'] : 'refresh',
			)
		);
	}

	/**
	 * Define which callback to use to sanitize the customizer input
	 *
	 * @param $type    string field type
	 * @param $setting string
	 *
	 * @return array|string
	 */
	protected function sanitize_callback( $type, $setting = '' ) {
		switch ( $type ) {
			case 'checkbox':
				$function = array( $this, 'one_zero' );
				break;

			case 'number':
				$function = 'absint';
				break;

			case 'text':
				$function = 'sanitize_text_field';
				break;

			case 'color':
				$function = 'sanitize_hex_color';
				break;

			case 'radio':
				$function = 'esc_attr';
				if ( 'shrink' === $setting ) {
					$function = array( $this, 'one_zero' );
				}
				break;

			default:
				$function = 'esc_attr';
				break;
		}

		return $function;
	}

	/**
	 * @param $input
	 *
	 * @return int
	 */
	public function one_zero( $input ) {
		return (int) (bool) $input;
	}

	/**
	 * Enqueue javascript for customizer preview.
	 *
	 * @since 1.8.0
	 */
	public function preview() {
		$minify = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'supersideme_customizer', plugins_url( "/js/customizer.me{$minify}.js", dirname( __FILE__ ) ), array( 'jquery' ), SUPERSIDEME_VERSION, true );
	}
}
