<?php

/**
 * Define the custom buttons fields.
 *
 * @since 2.4.0
 * @return array
 */
return array(
	array(
		'setting' => 'link',
		'label'   => __( 'Link', 'superside-me' ),
		'type'    => 'text',
	),
	array(
		'setting' => 'label',
		'label'   => __( 'Label', 'superside-me' ),
		'type'    => 'text',
	),
	array(
		'setting' => 'icon',
		'label'   => __( 'Icon', 'superside-me' ),
		'type'    => 'text',
		'class'   => 'ssme-iconpicker',
	),
	array(
		'setting' => 'show',
		'label'   => __( 'Show the text label in addition to the icon', 'superside-me' ),
		'type'    => 'checkbox',
		'heading' => __( 'Show Label', 'superside-me' ),
	),
	array(
		'setting' => 'new',
		'label'   => __( 'Open link in a new window', 'superside-me' ),
		'type'    => 'checkbox',
		'heading' => __( 'Link Behavior', 'superside-me' ),
	),
);
