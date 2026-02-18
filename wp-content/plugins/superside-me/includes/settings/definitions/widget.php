<?php

/**
 * Define the widget location setting.
 * @return array
 *
 * @since 2.3.0
 */
return array(
	'setting' => 'widget',
	'title'   => __( 'Widget Location', 'superside-me' ),
	'section' => 'optional',
	'type'    => 'radio',
	'choices' => array(
		1 => __( 'After Menus/End of Panel', 'superside-me' ),
		0 => __( 'Before Menus/After Search Input', 'superside-me' ),
	),
	'label'   => __( 'Set widget location', 'superside-me' ),
);
