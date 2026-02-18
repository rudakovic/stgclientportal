<?php

/**
 * Class SuperSideMeSettingsEnqueue
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2019-2020 Robin Cornett
 * @license   GPL-2.0+
 */
class SuperSideMeSettingsEnqueue {

	/**
	 * The active settings tab.
	 * @var string
	 */
	protected $tab;

	/**
	 * SuperSideMeSettingsEnqueue constructor.
	 *
	 * @param $tab string
	 */
	public function __construct( $tab ) {
		$this->tab = $tab;
	}

	/**
	 * Add color picker to SuperSide Me settings
	 * @since 1.0.0
	 */
	public function add_color_picker() {

		if ( 'appearance' !== $this->tab ) {
			return;
		}

		// Add the color picker css file
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );

		if ( ! function_exists( 'wp_add_inline_script' ) ) {
			return;
		}
		$code = '( function( $ ) { \'use strict\'; $( function() { $( \'.color-field\' ).wpColorPicker(); }); })( jQuery );';
		wp_add_inline_script( 'wp-color-picker', $code );
	}

	/**
	 * Enqueue the styles/scripts needed for our custom buttons.
	 *
	 * @since 2.4.0
	 */
	public function enqueue_custom_buttons_assets() {
		if ( 'custom' !== $this->tab ) {
			return;
		}
		wp_enqueue_script( 'supersideme-admin', plugins_url( '/js/settings.js', dirname( __FILE__ ) ), array(
			'jquery',
			'jquery-ui-sortable',
		), SUPERSIDEME_VERSION, true );
		wp_enqueue_style( 'supersideme-admin', plugins_url( '/css/admin.css', dirname( __FILE__ ) ), array(), SUPERSIDEME_VERSION, 'all' );
		wp_localize_script(
			'supersideme-admin',
			'SuperSideMeSettings',
			apply_filters(
				'supersideme_settings_menu_bar_localization',
				array(
					'addButtonText'    => __( 'Add Button,', 'superside-me' ),
					'removeButtonText' => __( 'Remove Button,', 'superside-me' ),
					'removeMessage'    => __( 'Do you really want to remove this button?', 'superside-me' ),
					'maxButtons'       => 3,
				)
			)
		);
	}

	/**
	 * Scripts/styles needed for Font Awesome.
	 *
	 * @since 2.4.0
	 */
	public function enqueue_fontawesome() {
		wp_enqueue_style( 'supersideme-fontawesome-picker', plugins_url( '/css/fontawesome-iconpicker.css', dirname( __FILE__ ) ), array(), '1.3.0' );
		$fontawesome = '5.11.2';
		wp_enqueue_style( 'font-awesome', "https://use.fontawesome.com/releases/v{$fontawesome}/css/all.css", array(), $fontawesome );
		wp_enqueue_script( 'supersideme-fontawesome-picker', plugins_url( '/js/fontawesome-iconpicker.js', dirname( __FILE__ ) ), array(), SUPERSIDEME_VERSION, true );
		wp_enqueue_script(
			'supersideme-icon-swap',
			plugins_url( '/js/icon-swap.js', dirname( __FILE__ ) ),
			array(
				'jquery',
				'supersideme-fontawesome-picker',
			),
			SUPERSIDEME_VERSION,
			true
		);
		wp_localize_script(
			'supersideme-icon-swap',
			'SuperSideMeIcons',
			array(
				'icons'  => include plugin_dir_path( dirname( __FILE__ ) ) . 'svg/icons.php',
				'brands' => include plugin_dir_path( dirname( __FILE__ ) ) . 'svg/brands.php',
			)
		);
	}

	/**
	 * On our settings tabs, dequeue the Better Font Awesome scripts/styles which conflict with ours.
	 */
	public function dequeue_conflicts() {
		$conflicting = array(
			'bfa-admin',
			'fontawesome-iconpicker',
		);
		foreach ( $conflicting as $item ) {
			wp_dequeue_style( $item );
			wp_dequeue_script( $item );
		}
	}
}
