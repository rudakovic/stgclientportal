<?php

/**
 * Define maxwidth setting.
 * @return array
 * @since 2.1.0
 */
return array(
	'setting'     => 'maxwidth',
	'title'       => __( 'SuperSide Me Appears At', 'superside-me' ),
	'section'     => 'main',
	'value'       => __( 'pixels', 'superside-me' ),
	'type'        => 'number',
	'input_attrs' => array(
		'min' => 0,
		'max' => 4000,
	),
	'description' => __( 'This is the largest screen/browser width at which the SuperSide Me navigation becomes active.', 'superside-me' ),
);
