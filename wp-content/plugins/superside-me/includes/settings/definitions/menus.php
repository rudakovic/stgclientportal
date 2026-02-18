<?php

/**
 * Define the fields for the menus tab.
 * @return array
 * @since 2.1.0
 */
$fields = array();
$menus  = get_registered_nav_menus();
if ( $menus ) {

	foreach ( $menus as $location => $description ) {
		$fields[] = array(
			'setting'  => esc_attr( $location ),
			'parent'   => 'menus',
			'title'    => $description,
			'label'    => esc_attr( $description ),
			'callback' => 'set_menu_options',
			'section'  => 'menus',
			'location' => $location,
			'type'     => 'custom',
			'skip'     => true,
		);
	}
}

return $fields;
