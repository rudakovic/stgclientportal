<?php

/**
 * Options for radio buttons for outline style.
 * @return array
 */
/**
 * Define outline setting.
 * @return array
 * @since 2.1.0
 */
return array(
	'setting'     => 'outline',
	'title'       => __( 'Outline Style', 'superside-me' ),
	'section'     => 'optional',
	'type'        => 'radio',
	'choices'     => array(
		'dotted' => __( 'dotted', 'superside-me' ),
		'dashed' => __( 'dashed', 'superside-me' ),
		'solid'  => __( 'solid', 'superside-me' ),
	),
	'label'       => __( 'Set outline style', 'superside-me' ),
	'description' => __( 'The outline provides a visual reference for when a menu element has focus.', 'superside-me' ),
);
