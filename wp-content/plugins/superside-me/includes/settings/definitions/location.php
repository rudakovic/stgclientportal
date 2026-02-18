<?php

/**
 * Define location setting.
 * @return array
 * @since 2.1.0
 */
return array(
	'setting'     => 'location',
	'title'       => __( 'Menu Button(s) Location', 'superside-me' ),
	'section'     => 'buttons',
	'type'        => 'text',
	'description' => __( 'Optional: set the location for the menu button(s). Leave blank for the default. Must be a CSS element (eg. .site-header).', 'superside-me' ),
);
