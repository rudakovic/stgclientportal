<?php

/**
 * Define position setting.
 * @return array
 * @since 2.1.0
 */
return array(
	'setting'     => 'position',
	'title'       => __( 'Menu Button(s) Position', 'superside-me' ),
	'section'     => 'buttons',
	'type'        => 'radio',
	'description' => __( 'Change the CSS value of the button\'s position.', 'superside-me' ),
	'choices'     => array(
		'relative' => __( 'Relative', 'superside-me' ),
		'absolute' => __( 'Absolute', 'superside-me' ),
		'fixed'    => __( 'Fixed', 'superside-me' ),
		'sticky'   => __( 'Stick to top', 'superside-me' ),
		'bottom'   => __( 'Stick to bottom', 'superside-me' ),
	),
	'label'       => __( 'Change the CSS value of the button\'s position.', 'superside-me' ),
	'transport'   => 'postMessage',
);
