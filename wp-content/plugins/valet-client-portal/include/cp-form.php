<?php
add_action( 'gform_after_submission_1', 'send_to_api', 10, 2 );
function send_to_api( $entry, $form ) {

	header( 'Access-Control-Allow-Origin: http://cpvaletdev.wpenginepowered.com/' );
	
	if ( 'Problem' == $entry['15'] ) {
		$description = $entry['5'] ? $entry['5'] : null;
		//Problem type
		$type = '79b71cae-8489-46e8-98dc-03ddfda67140';
		$help_links      = isset( $entry['19'] ) ? $entry['19'] : null;
		$additional_info = $entry['20'] ? $entry['20'] : null;

		$url        = isset( $entry['12'] ) ? ( $entry['12'] ) : null;
		$subject    = 'Client portal task - ' . ' ' . $url;
		$full_desc  = 'URL: ';
		$full_desc .= $url . ' ';
		$full_desc .= 'Help-links: ' . $help_links . ' ';
		$full_desc .= 'Description: ' . $description . ' Addtional info: ' . $additional_info;
	}

	if ( 'Request' == $entry['15'] ) {
		$description = $entry['17'] ? $entry['17'] : null;
		//Req type
		$type = '363d58ac-c1a5-42b2-a440-3f231ce51fe6';
		$help_links      = isset( $entry['16'] ) ? $entry['16'] : null;
		$additional_info = $entry['20'] ? $entry['20'] : null;

		$url        = isset( $entry['12'] ) ? ( $entry['12'] ) : null;
		$attachment = isset( $entry['18'] ) ? ( $entry['18'] ) : null;
		$subject    = 'Client portal task - ' . ' ' . $url;
		$full_desc  = 'URL: ';
		$full_desc .= $url . ' ';
		$full_desc .= 'Help-links: ' . $help_links . ' ';
		$full_desc .= 'Description: ' . $description . ' Addtional info: ' . $additional_info . ' ' . $attachment;
	}

	if ( 'Other' == $entry['15'] ) {
		//Other type
		$type = '4e1584e3-7e58-48e9-936a-8c5d977b0cc0';		
		$url             = isset( $entry['12'] ) ? ( $entry['12'] ) : null;
		$additional_info = $entry['20'] ? $entry['20'] : null;

		$subject    = 'Client portal task - ' . ' ' . $url;
		$full_desc  = 'URL: ';
		$full_desc .= $url . ' ';
		$full_desc .= 'Description: ' . $additional_info;
	}

	$time_now = time() * 1000;
	// var_dump($time_now);
	$due_date           = time() + 72 * 60 * 60;
	$due_date_13_digits = $due_date * 1000;

	$listId = '901102957427';
	$query  = array(
		'custom_task_ids' => 'true',
		'team_id'         => '10613545',
	);

	$curl = curl_init();

	$payload = array(
		'name'                         => $subject,
		'description'                  => $full_desc,
		'assignees'                    => array(
			183,
		),
		'tags'                         => array(
			'Client-Portal',
		),
		'status'                       => 'to do',
		'priority'                     => 3,
		'due_date'                     => $due_date_13_digits,
		'due_date_time'                => false,
		'time_estimate'                => 3600000,
		'start_date'                   => $time_now,
		'start_date_time'              => false,
		'notify_all'                   => false,
		'parent'                       => null,
		'links_to'                     => null,
		'check_required_custom_fields' => false,
		'custom_fields'                => array(
			array(
				'id'    => '06b2e0a8-5ded-473b-accb-baa0b5bd88ec',
				'value' => $help_links
			),
			array(
				'id'    => '25846d56-3b6a-4621-8209-6685f3eae368',
				'value' => $additional_info
			),
			array(
				'id'    => 'e890146f-51be-4e10-9bfd-c0e374caa7a7',
				'value' => '0a3377d7-488b-43ed-9cc9-39074771fa74',
			),
			array(
				'id'    => 'dec0bf42-d578-48cf-9b07-619002de65da',
				'value' => $url
			),
			array(
				'id'    => 'b95ed8c3-6a87-431c-b43d-ae10a86d439e',
				'value' => $type
			)												
		)
	);

	curl_setopt_array(
		$curl,
		array(
			CURLOPT_HTTPHEADER     => array(
				'Authorization: pk_57096564_GTODYU5KD1U0D88DUOM38H5NDTFI8DBA',
				'Content-Type: application/json',
			),
			CURLOPT_POSTFIELDS     => json_encode( $payload ),
			CURLOPT_URL            => 'https://api.clickup.com/api/v2/list/' . $listId . '/task?' . http_build_query( $query ),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST  => 'POST',
		)
	);

	$response = curl_exec( $curl );
	$error    = curl_error( $curl );

	curl_close( $curl );
}
