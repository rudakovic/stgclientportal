<?php

/**
 * Class Client
 */

namespace ValetTasks\Services;

use WP_Query;
use WP_REST_Response;

class Client {
	public function get_clickup_lists() {
		$post_id = get_the_ID();

		$lists = get_field('client_clickup_list_ids', $post_id);

		return $lists ?? [];
	}

	public function get_id() {
		$post_id = get_the_ID();

		return $post_id;
	}
}