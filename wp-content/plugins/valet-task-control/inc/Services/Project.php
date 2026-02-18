<?php

/**
 * Class Project
 */

namespace ValetTasks\Services;

use WP_Query;
use WP_REST_Response;

class Project {
	public function get_clickup_list_id() {
		$post_id = get_the_ID();

		$list_id = get_field('clickup_list_id', $post_id);

		return $list_id;
	}

	public function get_id() {
		$post_id = get_the_ID();

		return $post_id;
	}
}