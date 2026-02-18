<?php

/**
 * Define array for the shrink setting.
 * @return array
 * @since 2.1.0
 */
return array(
	'setting'     => 'shrink',
	'title'       => __( 'Main Menu Button Size', 'superside-me' ),
	'section'     => 'buttons',
	'label'       => __( 'Set the button to only be as wide as its contents/text.', 'superside-me' ),
	'description' => __( 'Set the width for the main menu button or the container for the menu and search buttons.', 'superside-me' ),
	'type'        => 'radio',
	'choices'     => array(
		0 => __( 'Full Width/100%', 'superside-me' ),
		1 => __( 'Auto', 'superside-me' ),
	),
	'transport'   => 'postMessage',
);
