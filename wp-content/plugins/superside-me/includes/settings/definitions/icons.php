<?php

/**
 * Define the custom icons setting.
 */
$svg = supersideme_get_settings( 'svg' );
if ( ! $svg ) {
	return false;
}

return array(
	'setting'  => 'icons',
	'title'    => __( 'Custom Icons', 'superside-me' ),
	'section'  => 'icons',
	'skip'     => true,
	'callback' => 'set_icons',
	'type'     => 'custom',
);
