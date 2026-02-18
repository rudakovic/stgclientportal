<?php

/**
 * Define panel width setting.
 * @return array
 * @since 2.1.0
 */
return array(
	'setting'     => 'panel_width',
	'title'       => __( 'Panel Width', 'superside-me' ),
	'section'     => 'main',
	'value'       => __( 'pixels', 'superside-me' ),
	'type'        => 'number',
	'input_attrs' => array(
		'min' => 150,
		'max' => 400,
	),
);
