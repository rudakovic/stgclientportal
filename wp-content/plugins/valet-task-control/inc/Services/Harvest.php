<?php

/**
 * Class Harvest
 */

namespace ValetTasks\Services;

use WP_REST_Response;

class Harvest {
	private function harvest_api_request( string $method, string $endpoint, array $params = [], array $body = [] ) {
		$access_token = HARVEST_ACCESS_TOKEN;
		$account_id   = HARVEST_ACCOUNT_ID;
		$account_name   = HARVEST_ACCOUNT_NAME;


		$url = 'https://api.harvestapp.com/v2/' . ltrim( $endpoint, '/' );

		if ( ! empty( $params ) ) {
			$url .= '?' . http_build_query( $params );
		}

		$args = [
			'method'  => strtoupper( $method ),
			'headers' => [
				'Authorization'       => 'Bearer ' . $access_token,
				'Harvest-Account-Id'  => $account_id,
				'User-Agent'          => $account_name,
				'Content-Type'        => 'application/json',
			],
		];

		if ( ! empty( $body ) ) {
			$args['body'] = wp_json_encode( $body );
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			throw new RuntimeException( $response->get_error_message() );
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		$body      = json_decode( wp_remote_retrieve_body( $response ), true );

		return [
			'status' => $http_code,
			'body'   => $body,
		];
	}

	public function get_time_entries(array $params = []) {
		var_dump('here');
		$response = $this->harvest_api_request( 'GET', 'time_entries', $params);

		return $response;
	}
}