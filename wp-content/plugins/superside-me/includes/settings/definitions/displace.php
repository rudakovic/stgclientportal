<?php

/**
 * Define displace setting.
 * @return array
 * @since 2.1.0
 */
return array(
	'setting' => 'displace',
	'title'   => __( 'Panel Behavior', 'superside-me' ),
	'section' => 'main',
	'type'    => 'radio',
	'choices' => array(
		1 => __( 'Push Site', 'superside-me' ),
		0 => __( 'Slide Over Site', 'superside-me' ),
	),
	'id'      => 'displace',
	'label'   => __( 'If enabled, the panel will push the site upon opening. Disable and the panel will slide over the site.', 'superside-me' ),
);
