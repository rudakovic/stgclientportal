<?php
/**
 * Add Custom Portal Valet role
 */
add_role(
	'basic_contributor',
	'Valet Client',
	array(
		'read'         => true, // True allows that capability.
		'edit_posts'   => false,
		'delete_posts' => false, // Use false to explicitly deny.
	)
);
