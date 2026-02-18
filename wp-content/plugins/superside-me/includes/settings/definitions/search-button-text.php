<?php

/**
 * Define the setting for the search button text.
 * @return array
 * @since 2.2.0
 */
return array(
	'setting'     => 'search_button_text',
	'title'       => __( 'Search Button Text', 'superside-me' ),
	'section'     => 'buttons',
	'transport'   => 'postMessage',
	'type'        => 'text',
	'description' => __( '[Visible] label for search button.', 'superside-me' ),
);
