<?php

/**
 * Define block elements setting.
 * @return array
 * @since 2.1.0
 */
return array(
	'setting'     => 'block',
	'title'       => __( 'Show Elements', 'superside-me' ),
	'type'        => 'text',
	'section'     => 'optional',
	'description' => __( 'Force elements to show using CSS. Separate multiple elements with commas. Must be a CSS element (eg. .site-header)', 'superside-me' ),
	'class'       => 'regular-text',
);
