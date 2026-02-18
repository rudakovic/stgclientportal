<?php
/**
 * Plugin Name: Valet Contacts
 * Description: Adds an AJAX-powered search input the Contacts CPT for Valet Vortal.
 * Version: 1.0
 * Author: Filip Rudaković
 * Author URI: https://valet.io
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

// Include the file that contains the AJAX handler function
require_once plugin_dir_path( __FILE__ ) . 'inc/ajax-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/shortcodes.php';

// ** Enqueue JavaScript **
function asc_enqueue_scripts() {
	wp_enqueue_script(
		'asc-ajax-search',
		plugin_dir_url(__FILE__) . 'assets/ajax-search.js',
		[],
		null,
		true
	);

	wp_enqueue_script(
		'asc-functions',
		plugin_dir_url(__FILE__) . 'assets/functions.js',
		[],
		null,
		true
	);

	wp_localize_script('asc-ajax-search', 'ajax_search_params', [
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce'    => wp_create_nonce('search_contacts_nonce'),
	]);
}
add_action('wp_enqueue_scripts', 'asc_enqueue_scripts');

function asc_render_search_input() {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
	ob_start(); ?>
	<input type="text" id="valet-ajax-search" placeholder="Search Contacts" value="<?php echo $search; ?>">
	<?php return ob_get_clean();
}
add_shortcode('valet_ajax_search_input', 'asc_render_search_input');

//function custom_search_query($where, $wp_query) {
//	global $wpdb;
//
//	// Ensure this runs only for frontend searches in 'contact' post type
//	if (is_admin() || $wp_query->get('post_type') !== 'contact') {
//		return $where;
//	}
//
//	// Get the search term from custom 'search' parameter
//	$search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
//	$search_term = preg_replace('/[^a-zA-Z0-9\s]/', '', $search_term);
//	// If search term is not empty, modify the WHERE clause
//	if (!empty($search_term)) {
//		// Adding the EXISTS query to search ACF fields (first name, last name, email)
//		$where .= " OR EXISTS (
//            SELECT 1 FROM {$wpdb->postmeta}
//            WHERE {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id
//            AND (
//                {$wpdb->postmeta}.meta_key = 'contact_first_name'
//                OR {$wpdb->postmeta}.meta_key = 'contact_last_name'
//                OR {$wpdb->postmeta}.meta_key = 'contact_email'
//            )
//            AND {$wpdb->postmeta}.meta_value LIKE '%$search_term%'
//        )";
//	}
//	error_log($where);
//	return $where;
//}
//
//// Hook only for frontend queries (main query)
//add_filter('posts_where', 'custom_search_query', 10, 2);