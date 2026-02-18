<?php

/**
 * Define array for side setting.
 * @return array
 * @since 2.1.0
 */
return array(
	'setting' => 'side',
	'title'   => __( 'Set Side for Navigation', 'superside-me' ),
	'section' => 'main',
	'type'    => 'radio',
	'choices' => array(
		'left'  => __( 'Left', 'superside-me' ),
		'right' => __( 'Right', 'superside-me' ),
	),
	'label'   => __( 'Set Side for Navigation', 'superside-me' ),
);
