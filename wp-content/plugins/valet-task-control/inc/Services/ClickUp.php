<?php

/**
 * Class ClickUp
 */

namespace ValetTasks\Services;

use WP_REST_Response;

class ClickUp {

	private function api_request( $method, $endpoint, $params = [], $body = [] ) {
		$clickup_api_key = CLICKUP_API_KEY;
		$base_url        = 'https://api.clickup.com/api/v2/';
		$url             = $base_url . ltrim( $endpoint, '/' );

		if ( ! empty( $params ) ) {
			$url .= '?' . http_build_query( $params );
		}

		$args = [
			'method'  => strtoupper( $method ),
			'headers' => [
				'Authorization' => 'Bearer ' . $clickup_api_key,
				'Content-Type'  => 'application/json',
			],
		];

		if ( ! empty( $body ) ) {
			$args['body'] = wp_json_encode( $body );
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( $response->get_error_message() );
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		$body      = json_decode( wp_remote_retrieve_body( $response ), true );

		return [
			'status' => $http_code,
			'body'   => $body,
		];
	}

	public function getTasks($list_id) {
		$endpoint = '/list/' . $list_id . '/task';

		$params = [
			'include_closed' => 'true',
		];

		$response = $this->api_request( 'GET', $endpoint, $params );

		if ($response['status'] === 200) {
			return $response['body'];
		} else {
			return [];
		}
	}

	public function updateTaskStatus($task_id, $status) {
		$endpoint = '/task/' . $task_id;
		$body = [
			'status' => $status,
		];

		$response = $this->api_request( 'PUT', $endpoint, [], $body );

		if ($response['status'] === 200) {
			return $response['body'];
		} else {
			return null;
		}
	}
}
