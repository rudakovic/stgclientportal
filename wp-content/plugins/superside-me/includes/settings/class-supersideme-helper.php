<?php

/**
 * Helper class for SuperSide Me.
 *
 * @package   SuperSideMe_Helper
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2020 Robin Cornett
 * @license   GPL-2.0+
 */
class SuperSide_Me_Helper extends SuperSideMeGetSetting {

	/**
	 * Page/setting string
	 * @var string
	 */
	protected $page = 'supersideme';

	/**
	 * The class which defines the plugin settings.
	 * @var $definitions SuperSideMeDefineSettings
	 */
	protected $definitions;

	/**
	 * Generic function to add settings sections
	 * @param $sections array
	 *
	 * @since 1.8.0
	 */
	protected function add_sections( $sections ) {

		foreach ( $sections as $section ) {
			$page        = empty( $section['tab'] ) ? $this->page . '_' . $section['id'] : $this->page . '_' . $section['tab'];
			$description = array( $this, 'section_description' );
			if ( isset( $section['description'] ) && is_callable( $section['description'] ) ) {
				$description = $section['description'];
			}
			add_settings_section(
				$this->page . '_' . $section['id'],
				$section['label'],
				$description,
				$page
			);
		}
	}

	/**
	 * Echo the section description.
	 *
	 * @param $args
	 */
	public function section_description( $args ) {
		$definitions = $this->get_definitions();
		$id          = str_replace( "{$this->page}_", '', $args['id'] );
		$method      = "do_{$id}_section_description";
		if ( method_exists( $definitions, $method ) ) {
			echo wp_kses_post( wpautop( $definitions->$method() ) );
		}
	}

	/**
	 * Get the definitions class.
	 *
	 * @return \SuperSideMeDefineSettings
	 */
	protected function get_definitions() {
		if ( isset( $this->definitions ) ) {
			return $this->definitions;
		}
		$this->definitions = new SuperSideMeDefineSettings();

		return $this->definitions;
	}

	/**
	 * Generic function to add settings fields
	 * @param $fields array
	 * @param  array $sections registered sections
	 *
	 * @since 1.8.0
	 */
	protected function add_fields( $fields, $sections ) {
		include_once 'fields/class-supersideme-field-base.php';
		foreach ( $fields as $field ) {
			if ( empty( $field['setting'] ) ) {
				continue;
			}
			$args = ! empty( $field['args'] ) ? array_merge( $field, $field['args'] ) : $field;
			$page = $field['section'];
			if ( ! empty( $sections[ $field['section'] ]['tab'] ) ) {
				$page = $sections[ $field['section'] ]['tab'];
			}
			$setting = "{$this->page}[ {$field['setting']} ]";
			if ( ! empty( $field['parent'] ) ) {
				$setting = "{$this->page}[ {$field['parent']} ][ {$field['setting']} ]";
			}
			add_settings_field(
				$setting,
				sprintf( '<label for="%s-%s">%s</label>', $this->page, $setting, $field['title'] ),
				array( $this, 'do_field' ),
				"{$this->page}_{$page}",
				"{$this->page}_{$field['section']}",
				$args
			);
		}
	}

	/**
	 * Do the settings field: select type and pick the matching class.
	 *
	 * @param array $field
	 */
	public function do_field( $field ) {
		$class = $this->initialize_field_class( $field );
		if ( $class ) {
			return;
		}
		$callback = $this->get_callback( $field );
		if ( is_callable( $callback ) ) {
			call_user_func( $callback, $field );
		}
	}

	/**
	 * Maybe initialize the field output class.
	 *
	 * @param  array $field
	 * @return boolean
	 * @since 2.7.0
	 */
	private function initialize_field_class( $field ) {
		if ( empty( $field['type'] ) ) {
			return false;
		}
		$slug_name = $this->get_class_slug_name( $field );
		$file      = plugin_dir_path( __FILE__ ) . "fields/class-supersideme-field-{$slug_name['slug']}.php";
		if ( ! file_exists( $file ) ) {
			return false;
		}
		include_once $file;
		$class_name = "SuperSideMeField{$slug_name['proper_name']}";
		if ( class_exists( $class_name ) ) {
			$do_field = new $class_name( $field );
			$do_field->do_field();
			$this->do_description( $field );

			return true;
		}

		return false;
	}

	/**
	 * Get the field class slug and proper name.
	 *
	 * @param  array $field
	 * @return array
	 * @since 2.7.0
	 */
	private function get_class_slug_name( $field ) {
		$proper_name = ucfirst( $field['type'] );
		$slug_name   = array(
			'slug'        => $field['type'],
			'proper_name' => $proper_name,
		);
		if ( ! empty( $field['parent'] ) ) {
			return array(
				'slug'        => "{$field['type']}-{$field['parent']}",
				'proper_name' => ucfirst( $field['type'] ) . ucfirst( $field['parent'] ),
			);
		}
		if ( 'custom' === $field['type'] ) {
			return array(
				'slug'        => "{$field['type']}-{$field['setting']}",
				'proper_name' => $proper_name . ucfirst( $field['setting'] ),
			);
		}

		return $slug_name;
	}

	/**
	 * Get the correct callback method for the field.
	 * @param $field
	 *
	 * @since 2.4.0
	 * @return array|bool
	 */
	protected function get_callback( $field ) {
		$callback = false;
		if ( isset( $field['callback'] ) ) {
			return $field['callback'];
		} elseif ( isset( $field['type'] ) ) {
			return array( $this, "do_{$field['type']}" );
		}

		return $callback;
	}

	/**
	 * Set which tab is considered active.
	 * @return string
	 * @since 2.0.0
	 */
	protected function get_active_tab() {
		$tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
		if ( supersideme_disable_settings_page() ) {
			$tab = 'licensing';
		}

		return $tab ? $tab : 'main';
	}

	/**
	 * Generic callback to display a field description.
	 *
	 * @since 1.6.0
	 *
	 * @param $args array
	 */
	protected function do_description( $args ) {
		$description = isset( $args['description'] ) ? $args['description'] : '';
		if ( ! $description ) {
			return;
		}
		printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
	}

	/**
	 * Determines if the user has permission to save the information from the submenu
	 * page.
	 *
	 * @since    2.3.0
	 * @access   protected
	 *
	 * @param    string    $action   The name of the action specified on the submenu page
	 * @param    string    $nonce    The nonce specified on the submenu page
	 *
	 * @return   bool                True if the user has permission to save; false, otherwise.
	 * @author   Tom McFarlin (https://tommcfarlin.com/save-wordpress-submenu-page-options/)
	 */
	protected function user_can_save( $action, $nonce ) {
		$is_nonce_set   = isset( $_POST[ $nonce ] );
		$is_valid_nonce = false;

		if ( $is_nonce_set ) {
			$is_valid_nonce = wp_verify_nonce( $_POST[ $nonce ], $action );
		}
		return ( $is_nonce_set && $is_valid_nonce );
	}
}
