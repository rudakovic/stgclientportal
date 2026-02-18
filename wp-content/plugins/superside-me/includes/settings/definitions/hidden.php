<?php

/**
 * Define hidden elements setting.
 * @return array
 * @since 2.1.0
 */
return array(
	'setting'     => 'hidden',
	'title'       => __( 'Hide Elements', 'superside-me' ),
	'type'        => 'text',
	'section'     => 'optional',
	'description' => __( 'Force elements to hide using CSS. Separate multiple elements with commas. Must be a CSS element (eg. .site-header)', 'superside-me' ),
	'class'       => 'regular-text',
);
