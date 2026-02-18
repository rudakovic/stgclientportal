<?php

/**
 * Add a filter to modify the default plugin options.
 * @since 2.0.0
 */
return apply_filters(
	'supersideme_default_options',
	array(
		'unsimplify'         => 0,
		'side'               => 'right',
		'navigation'         => __( 'Menu', 'superside-me' ),
		'close'              => __( 'Close', 'superside-me' ),
		'background'         => '#333333',
		'link_color'         => '#fefefe',
		'maxwidth'           => 800,
		'menus'              => array(),
		'search'             => 0,
		'swipe'              => 0,
		'panel_width'        => 260,
		'shrink'             => 0,
		'displace'           => 1,
		'opacity'            => 100,
		'location'           => '',
		'speed'              => 200,
		'hidden'             => '',
		'block'              => '',
		'outline'            => 'dotted',
		'desktop'            => 0,
		'position'           => 'relative',
		'fontawesome'        => array(
			'css'    => 0,
			'glyphs' => 0,
		),
		'svg'                => 1,
		'icons'              => array(
			'menu'    => 'bars',
			'close'   => 'times',
			'submenu' => 'angle-down',
			'search'  => 'search',
		),
		'search_button'      => 0,
		'search_button_text' => __( 'Search', 'superside-me' ),
		'widget'             => 1,
		'custom'             => array(),
	)
);
