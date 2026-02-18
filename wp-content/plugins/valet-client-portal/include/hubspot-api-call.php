<?php
// We need to make sure we are conistent with handling Addtional URLs, ideally we want it set as for Atlas with prime domain added under Addtional URLs field.
function return_urls_from_hubspot() {

	$curl = curl_init();

	curl_setopt_array(
		$curl,
		array(
			CURLOPT_URL            => 'https://api.hubapi.com/crm/v3/objects/companies?limit=100&properties=additional_urls&properties=domain&properties=name&archived=false',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => '',
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => 'GET',
			CURLOPT_HTTPHEADER     => array(
				'accept: application/json',
				'authorization: Bearer pat-na1-7856c32f-8c08-44ee-95be-afcf595ef62b',
			),
		)
	);

	$response = curl_exec( $curl );
	$err      = curl_error( $curl );

	curl_close( $curl );

	$client = get_valet_client_for_hubspot();

	$response = json_decode( $response, true );

	foreach ( $response as $first_level ) {

		foreach ( $first_level as $second_level ) {
			if ( isset( $second_level['properties'] ) ) {
				// Get Hubspot data for logged in client
				if ( preg_match( '/' . $second_level['properties']['name'] . '/i', $client ) ) {
					$single_client_hubspot_data[] = $second_level;
				}
			}
		}
	}

	// Get URLs
	foreach ( $single_client_hubspot_data as $single_client_hubspot_data_property ) {

		if ( null !== $single_client_hubspot_data_property['properties']['additional_urls'] ) {
			$domains = explode( ',', $single_client_hubspot_data_property['properties']['additional_urls'] );
		} else {
			$domains[] = $single_client_hubspot_data_property['properties']['domain'];
		}
	}

	return $domains;
}


add_filter( 'gform_pre_render_1', 'populate_posts' );
add_filter( 'gform_pre_validation_1', 'populate_posts' );
add_filter( 'gform_pre_submission_filter_1', 'populate_posts' );
add_filter( 'gform_admin_pre_render_1', 'populate_posts' );
function populate_posts( $form ) {

	foreach ( $form['fields'] as &$field ) {

		if ( $field->type != 'select' ) {
			continue;
		}

		// you can add additional parameters here to alter the posts that are retrieved
		// more info: http://codex.wordpress.org/Template_Tags/get_posts
		$posts   = return_urls_from_hubspot();
		$choices = array();
		foreach ( $posts as $post ) {
			$choices[] = array(
				'text'  => $post,
				'value' => $post,
			);
		}

		// update 'Select a Post' to whatever you'd like the instructive option to be
		$field->placeholder = 'Select URL';
		$field->choices     = $choices;

	}

	return $form;
}

function get_valet_client_for_hubspot() {

	$client_user_id = get_current_user_id();

	if ( null == ! $client_user_id ) {
		$acf_user_id  = 'user_' . $client_user_id;
		$valet_client = get_field( 'valet_client', $acf_user_id );
		if ( null == ! $valet_client ) {
			return $valet_client->post_title;
		}
	}
}
