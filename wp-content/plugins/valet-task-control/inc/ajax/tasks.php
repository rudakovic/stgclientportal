<?php
use ValetTasks\Services\ClickUp;

add_action( 'wp_ajax_valet_update_task_status', 'valet_update_task_status' );
add_action( 'wp_ajax_nopriv_valet_update_task_status', 'valet_update_task_status' );

function valet_update_task_status() {
	check_ajax_referer( 'valet_tasks_nonce', 'nonce' );

	$task_id   = sanitize_text_field( $_POST['task_id'] ?? '' );
	$completed = intval( $_POST['completed'] ?? 0 );
	$post_id   = intval( $_POST['post_id'] ?? '' );
	$transient_key = 'clickup_tasks_' . $post_id;

	if ( ! $task_id ) {
		wp_send_json_error([ 'message' => 'Missing task ID' ]);
	}

	$status = $completed ? 'complete' : 'to do';

	$clickup = new ClickUp();
	$response = $clickup->updateTaskStatus( $task_id, $status );

	if ( $response !== null ) {
		delete_transient( $transient_key );
		wp_send_json_success([
			'clickup_response' => $response
		]);
	} else {
		wp_send_json_error([
			'message' => 'ClickUp update failed'
		], 500);
	}
}