<?php

/**
 *
 * Class to define all the settings for SuperSide Me.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2020 Robin Cornett
 * @license   GPL-2.0+
 */
class SuperSideMeDefineSettings {

	/**
	 * Settings for options screen
	 * @return array for side menu options
	 *
	 * @since 1.0.0
	 */
	public function register_sections() {
		return include 'definitions/sections.php';
	}

	/**
	 * Register settings fields
	 *
	 * @return array $this->fields fields
	 *
	 * @since 1.5.1
	 */
	public function register_fields() {
		$files = array(
			'side',
			'max-width',
			'panel-width',
			'displace',
			'background',
			'opacity',
			'color-link',
			'shrink',
			'navigation',
			'close',
			'search-button',
			'search-button-text',
			'position',
			'location',
			'search',
			'swipe',
			'desktop',
			'speed',
			'hidden',
			'block',
			'outline',
			'fontawesome',
			'widget',
			'unsimplify',
			'custom-buttons',
			'svg',
			'icons',
		);

		$all_fields = array();
		foreach ( $files as $file ) {
			$all_fields[] = include "definitions/{$file}.php";
		}

		return array_merge( $all_fields, include 'definitions/menus.php' );
	}

	/**
	 * Section description
	 *
	 * @since 1.0.0
	 */
	public function do_main_section_description() {
		$description = __( 'Change the default behavior and style for the SuperSide Me panel.', 'superside-me' );
		/* translators: link is to tutorial for filters */
		$description .= sprintf( __( ' Want a little more control over your menu and can handle a bit of coding? Check out the <a href="%s" target="_blank">navigation options filter</a>.', 'superside-me' ), esc_url( 'https://robincornett.com/docs/modify-navigation-options/' ) );

		return $description;
	}

	/**
	 * Description for the icons section.
	 * @return string
	 */
	public function do_icons_section_description() {
		$setting = supersideme_get_settings( 'fontawesome' );
		if ( ! $setting['css'] ) {
			return '';
		}
		$description  = __( 'If you have used SuperSide Me before 2.4.0, SVG icons are turned off by default for backwards compatibility.', 'superside-me' );
		$description .= ' ' . __( 'See the help tab (above) for more information.', 'superside-me' );

		return $description;
	}

	/**
	 * Description for the optional settings section.
	 */
	public function do_optional_section_description() {
		return __( 'Optional settings which may enhance your SuperSide Me experience.', 'superside-me' );
	}

	/**
	 * Description for the registered menus section.
	 *
	 * @since  2.0.0
	 */
	public function do_menus_section_description() {
		$description = __( 'SuperSide Me works automagically by combining every menu assigned to a location on your site and outputting them to your new mobile menu panel.', 'superside-me' );
		/* translators: placeholder is the link to the Menus admin page. */
		$description .= sprintf( ' ' . __( 'You can check which locations actually have menus assigned under <a href="%s">Appearance > Menus</a>.', 'superside-me' ), admin_url( 'nav-menus.php?action=locations' ) );
		$description .= ' ' . __( 'Here, you can set headings for each menu, or remove a certain menu from being added to the panel.', 'superside-me' );

		return $description;
	}

	/**
	 * Description for the custom menu bar section/tab.
	 *
	 * @since 2.4.0
	 * @return string
	 */
	public function do_custom_section_description() {
		$description = __( 'You can now add up to three custom menu buttons to display with your SuperSide Me menu/search button(s). Each button must have a link and label (which will show if you set it, or be readable for users with screen readers).', 'superside-me' );

		return $description;
	}

	/**
	 * Buttons section description.
	 * @since 2.2.0
	 */
	public function do_buttons_section_description() {
		return __( 'Modify the settings for the menu button(s) appearance and location. Although it is a <b>really good idea</b> to have visible labels for your buttons, if you leave the labels blank, they will still have hidden labels for screen readers, so will still be accessible.', 'superside-me' );
	}
}
