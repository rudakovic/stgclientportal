<?php

/**
 * Define the fontawesome setting.
 * @return array
 */
$description = __( 'SuperSide Me uses Font Awesome for menu icons. If you are already loading Font Awesome another way, you can disable the font from loading; if you want to replace the icons completely, disable them, too.', 'superside-me' );
$setting     = supersideme_get_settings();
if ( $setting['svg'] && $setting['fontawesome']['css'] ) {
	$description = __( 'It looks like you have SVG icons enabled, so you may be able to disable Font Awesome here.', 'superside-me' ) . ' ' . $description;
}

return array(
	'setting'     => 'fontawesome',
	'title'       => __( 'Load Font Awesome', 'superside-me' ),
	'type'        => 'checkbox_array',
	'section'     => 'optional',
	'choices'     => array(
		'css'    => __( 'Load Fonts', 'superside-me' ),
		'glyphs' => __( 'Use Icons', 'superside-me' ),
	),
	'description' => $description,
	'skip'        => true,
);
