<?php

/**
 * Define speed setting.
 * @return array
 * @since 2.1.0
 */
return array(
	'setting'     => 'speed',
	'title'       => __( 'Panel Speed', 'superside-me' ),
	'section'     => 'optional',
	'value'       => __( 'milliseconds', 'superside-me' ),
	'type'        => 'number',
	'input_attrs' => array(
		'min' => 100,
		'max' => 10000,
	),
	'description' => __( 'The amount of time it takes the panel to slide open.', 'superside-me' ),
);
