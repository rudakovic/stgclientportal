<?php
/**
 * Get data from EH and form arrays
 */
function valet_get_data_from_everhour() {

	$plan                  = '';
	$retainer_refresh_date = '';
	$used_time_hours       = '';
	$retainer_total_hours  = '';
	$everhour_client_id    = get_field( 'everhour_client_id' );

	if ( null !== $everhour_client_id ) {

		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, 'https://api.everhour.com/projects/' . $everhour_client_id );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HEADER, false );

		curl_setopt(
			$ch,
			CURLOPT_HTTPHEADER,
			array(
				'Content-Type: application/json',
				'X-Api-Key: 7619-d4fe-d43bc7-eb619d-a7343bd0',
			)
		);

		$get_valet_client = curl_exec( $ch );
		curl_close( $ch );

		$valet_client_clean = json_decode( $get_valet_client, true );

		if ( isset( $valet_client_clean['budget'] ) ) {

			$retainer_total = $valet_client_clean['budget']['budget'];
			$used_time      = $valet_client_clean['budget']['timeProgress'];

			$retainer_total_hours = $retainer_total / 3600;
			$used_time_hours      = round( $used_time / 3600, 2 );
			// Check when plan refreshes.
			if(isset($valet_client_clean['budget']['monthStartDate'])) {
				if ( 1 === $valet_client_clean['budget']['monthStartDate'] ) {
					$retainer_refresh_date = 'every 1st of month.';
				}
				if ( 2 === $valet_client_clean['budget']['monthStartDate'] ) {
					$retainer_refresh_date = 'every 2nd of month.';
				}
				if ( 3 === $valet_client_clean['budget']['monthStartDate'] ) {
					$retainer_refresh_date = 'every 3rd of month.';
				}
				if ( 3 < $valet_client_clean['budget']['monthStartDate'] ) {
					$retainer_refresh_date = 'every ' . $valet_client_clean['budget']['monthStartDate'] . 'th of month.';
				}
			}
			// todo - annual retainers.

			if ( $retainer_total_hours < 3 ) {
				$plan = 'Basic';

			}
			if ( $retainer_total_hours > 3 && $retainer_total_hours < 8 ) {
				$plan = 'Professional';

			}
			if ( $retainer_total_hours > 8 && $retainer_total_hours < 26 ) {
				$plan = 'Elite';

			}
		} else {
			$plan = 'Ad Hoc';
		}

		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, 'https://api.everhour.com/projects/' . $everhour_client_id . '/tasks' );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HEADER, false );

		curl_setopt(
			$ch,
			CURLOPT_HTTPHEADER,
			array(
				'Content-Type: application/json',
				'X-Api-Key: 7619-d4fe-d43bc7-eb619d-a7343bd0',
			)
		);

		$client_get_tasks = curl_exec( $ch );
		$http_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close( $ch );

		$client_tasks_clean = json_decode( $client_get_tasks, true );

		if ($http_status_code == 200) {
			if ( count( $client_tasks_clean ) > 0 ) {
				foreach ( $client_tasks_clean as $key => $part ) {
					$sort[ $key ] = strtotime( $part['createdAt'] );
				}
				array_multisort( $sort, SORT_DESC, $client_tasks_clean );

				$valet_single_client_closed_tasks = array();
				$valet_single_client_open_tasks   = array();
				foreach ( $client_tasks_clean as $item ) {
					if ( strpos( $item['status'], 'complete' ) !== false ) {
						$closed_tasks[] = $item;
					} else {
						$open_tasks[] = $item;
					}
				}

				if ( ! empty( $open_tasks ) ) {
					global $valet_single_client_open_tasks;
					foreach ( $open_tasks as $single_open_task ) {
						$timestamp = strtotime( $single_open_task['createdAt'] );
						if ( isset( $single_open_task['time'] ) ) {
							$open_task_used_time  = $single_open_task['time']['total'];
							$open_task_hours_used = round( $open_task_used_time / 3600, 2 );
						} else {
							$open_task_hours_used = 0;
						}
						$open_task_date = date( 'M-d', $timestamp );
						if ( $open_task_hours_used > 0 ) {
							$valet_single_client_open_tasks[] = array(
								'open_task_name'       => $single_open_task['name'],
								'open_task_date'       => $open_task_date,
								'open_task_used_hours' => $open_task_hours_used,
							);

						}
					}
				} else {
					global $valet_single_client_open_tasks;
					$valet_single_client_open_tasks[] = array(
						'open_task_name'       => 'No open tasks',
						'open_task_date'       => '',
						'open_task_used_hours' => '',
					);
				}
				if ( ! empty( $closed_tasks ) ) {
					global $valet_single_client_closed_tasks;
					foreach ( $closed_tasks as $single_closed_task ) {
						$closed_task_timestamp = strtotime( $single_closed_task['createdAt'] );
						if ( isset( $single_closed_task['time'] ) ) {
							$closed_task_date       = date( 'M-d', $closed_task_timestamp );
							$closed_task_used_time  = $single_closed_task['time']['total'];
							$closed_task_used_hours = round( $closed_task_used_time / 3600, 2 );
						} else {
							$closed_task_used_hours = 0;
						}
						if ( ! str_contains( $single_closed_task['name'], 'Balance Transfer' ) && $closed_task_used_hours > 0 ) {
							$valet_single_client_closed_tasks[] = array(
								'closed_task_name'       => $single_closed_task['name'],
								'closed_task_date'       => $closed_task_date,
								'closed_task_used_hours' => $closed_task_used_hours,
							);
						}
					}
				} else {
					global $valet_single_client_closed_tasks;
					$valet_single_client_closed_tasks[] = array(
						'closed_task_name'       => 'No closed tasks',
						'closed_task_date'       => '',
						'closed_task_used_hours' => '',
					);
				}

				global $valet_get_client_data;

				$valet_get_client_data = array(
					'plan'                  => $plan,
					'retainer_refresh_date' => $retainer_refresh_date,
					'used_time_hours'       => $used_time_hours,
					'retainer_total_hours'  => $retainer_total_hours,
				);
			} else {
				global $valet_get_client_data;
				$valet_get_client_data = array(
					'plan'                  => $plan,
					'retainer_refresh_date' => [],
					'used_time_hours'       => [],
					'retainer_total_hours'  => [],
				);
			}
		} elseif ( $http_status_code == 403 ) {
			global $valet_get_client_data;
			$valet_get_client_data = array(
				'plan'                  => 'Everhour issue',
				'retainer_refresh_date' => [],
				'used_time_hours'       => [],
				'retainer_total_hours'  => [],
			);
		} else {
			global $valet_get_client_data;
			$valet_get_client_data = array(
				'plan'                  => 'Error occurred',
				'retainer_refresh_date' => [],
				'used_time_hours'       => [],
				'retainer_total_hours'  => [],
			);
		}
	}
}

add_shortcode( 'valet_get_data_from_everhour', 'valet_get_data_from_everhour' );
