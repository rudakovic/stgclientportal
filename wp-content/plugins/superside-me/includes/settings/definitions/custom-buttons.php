<?php

/**
 * Define fields for custom buttons.
 *
 * @since 2.4.0
 * @return array
 */
return array(
	'setting'  => 'custom',
	'title'    => __( 'Custom Buttons', 'superside-me' ),
	'section'  => 'custom',
	'skip'     => true,
	'callback' => 'set_custom_buttons',
	'type' => 'custom',
);
