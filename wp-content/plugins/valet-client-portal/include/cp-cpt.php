<?php
/**
 * Register Custom Post Type
 */
function valet_portal_clients() {

	$labels = array(
		'name'                  => _x( 'Clients', 'Post Type General Name', 'clientportal' ),
		'singular_name'         => _x( 'Client', 'Post Type Singular Name', 'clientportal' ),
		'menu_name'             => __( 'Clients', 'clientportal' ),
		'name_admin_bar'        => __( 'Post Type', 'clientportal' ),
		'archives'              => __( 'Clients Archives', 'clientportal' ),
		'attributes'            => __( 'Clients Attributes', 'clientportal' ),
		'parent_item_colon'     => __( 'Client Item:', 'clientportal' ),
		'all_items'             => __( 'All Clients', 'clientportal' ),
		'add_new_item'          => __( 'Add New Client', 'clientportal' ),
		'add_new'               => __( 'Add New', 'clientportal' ),
		'new_item'              => __( 'New Client', 'clientportal' ),
		'edit_item'             => __( 'Edit Client', 'clientportal' ),
		'update_item'           => __( 'Update Client', 'clientportal' ),
		'view_item'             => __( 'View Client', 'clientportal' ),
		'view_items'            => __( 'View Clients', 'clientportal' ),
		'search_items'          => __( 'Search Clients', 'clientportal' ),
		'not_found'             => __( 'Not found', 'clientportal' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'clientportal' ),
		'featured_image'        => __( 'Featured Image', 'clientportal' ),
		'set_featured_image'    => __( 'Set featured image', 'clientportal' ),
		'remove_featured_image' => __( 'Remove featured image', 'clientportal' ),
		'use_featured_image'    => __( 'Use as featured image', 'clientportal' ),
		'insert_into_item'      => __( 'Insert into item', 'clientportal' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'clientportal' ),
		'items_list'            => __( 'Items list', 'clientportal' ),
		'items_list_navigation' => __( 'Items list navigation', 'clientportal' ),
		'filter_items_list'     => __( 'Filter items list', 'clientportal' ),
	);
	$args   = array(
		'label'               => __( 'Post Type', 'clientportal' ),
		'description'         => __( 'Post Type Description', 'clientportal' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor' ),
		'taxonomies'          => array( 'category', 'post_tag' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'valet-client', $args );
}
add_action( 'init', 'valet_portal_clients', 0 );
