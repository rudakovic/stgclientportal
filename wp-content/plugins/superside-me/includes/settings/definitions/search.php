<?php

/**
 * Define search setting.
 * @return array
 * @since 2.1.0
 */
return array(
	'setting'   => 'search',
	'title'     => __( 'Search Input', 'superside-me' ),
	'section'   => 'optional',
	'transport' => 'postMessage',
	'label'     => __( 'Add a search input to the beginning of the side panel.', 'superside-me' ),
	'type'      => 'checkbox',
);
