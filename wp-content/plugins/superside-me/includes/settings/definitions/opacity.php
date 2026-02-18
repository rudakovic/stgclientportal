<?php

/**
 * Define opacity setting.
 * @return array
 * @since 2.1.0
 */
return array(
	'setting'     => 'opacity',
	'title'       => __( 'Background Opacity', 'superside-me' ),
	'section'     => 'colors',
	'type'        => 'number',
	'input_attrs' => array(
		'min' => 75,
		'max' => 100,
	),
	'value'       => '%',
	'description' => __( 'Transparency is great, but more effective if your panel slides out over your site.', 'superside-me' ),
);
