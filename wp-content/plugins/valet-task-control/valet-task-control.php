<?php
/**
 * Plugin Name: Valet Task Control
 * Description: Gets tasks, their times and present them
 * Version: 1.0.0
 * Author: Valet
 * Text Domain: valet-task-control-plugin
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

// Define constants.
define( 'VALET_TASK_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'VALET_TASK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define('VALET_TASK_PLUGIN_VERSION', '1.0.0');
define('CLICKUP_API_KEY', '81398205_2971fdf1bc433e7d913789a9b809fa680f6052c1b5969c0fcb1a4a931cdf3e6c');
define('CLICKUP_TEAM_ID', 10613545);
define('HARVEST_ACCESS_TOKEN', '4182074.pt.9G8NorMk-RW1FwEucQ9Ywf6xTnY_RbRdEtqBfl6gGbk3FbVKhp4smi10yrwiR65qkn8KxtmxiwwuvS0sY09n1g');
define('HARVEST_ACCOUNT_ID', 2102456);
define('HARVEST_ACCOUNT_NAME', 'Valet');

// === Autoloader ===
require_once VALET_TASK_PLUGIN_PATH . 'inc/functions/autoloader.php';
require_once VALET_TASK_PLUGIN_PATH . 'inc/functions/shortcodes.php';
require VALET_TASK_PLUGIN_PATH . '/inc/Services/class-custom-project-status.php';
require_once VALET_TASK_PLUGIN_PATH . 'inc/ajax/tasks.php';

function valet_enqueue_tasks_script() {
	wp_enqueue_script(
		'valet-tasks',
		VALET_TASK_PLUGIN_URL . 'assets/js/valet-tasks.js',
		[],
		'1.0',
		true
	);

	wp_localize_script( 'valet-tasks', 'valetTasks', [
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'valet_tasks_nonce' ),
	] );
}

add_action( 'admin_init', function () {

	if ( empty( $_GET['get_entries'] ) ) {
		return;
	}

	// Optional: basic protection
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}

	$harvest = new Harvest();
	$results = $harvest->get_time_entries(['project_id' => 46839438]);

	echo '<pre>';
	var_dump( $results );
	echo '</pre>';

	exit;
});