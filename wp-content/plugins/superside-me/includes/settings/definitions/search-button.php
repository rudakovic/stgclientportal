<?php

/**
 * Define the search button setting.
 * @return array
 * @since 2.2.0
 */
return array(
	'setting'   => 'search_button',
	'title'     => __( 'Search Button', 'superside-me' ),
	'section'   => 'buttons',
	'label'     => __( 'Add a search button next to the menu button.', 'superside-me' ),
	'transport' => 'postMessage',
	'type'      => 'checkbox',
);
