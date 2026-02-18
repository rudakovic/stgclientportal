<?php

header( 'Access-Control-Allow-Origin: https://cpvaletdev.wpenginepowered.com' );
// List all Clikup folders to pull clients from.

$query = array(
  "archived" => "false"
);

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_HTTPHEADER => [
    "Authorization: pk_57096564_GTODYU5KD1U0D88DUOM38H5NDTFI8DBA"
  ],
  CURLOPT_URL => "https://api.clickup.com/api/v2/space/32263954/folder?" . http_build_query($query),
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "GET",
]);

$folders[] = curl_exec($curl);
$error = curl_error($curl);



if ( $error ) {
	echo 'cURL Error #:' . htmlspecialchars( $error );
} else {

	$all_clients = array();
	foreach ( $folders as $single_response ) {

		$decode_response = json_decode( $single_response, true );

		foreach ( $decode_response as $first_array ) {

			foreach ( $first_array as $single_client_value ) {

 				$all_clients[$single_client_value['name']] = ( $single_client_value['lists'][0]['id'] ); 
			}
		}
	}
}

/*
// Assuming you already have the post IDs or you want to loop through all posts
$posts = get_posts(array(
    'post_type' => 'valet-client', // Change 'post' to your custom post type if needed
    'posts_per_page' => -1, // Get all posts
));

foreach ($posts as $post) {
    // Assuming 'your_acf_field_name' is the name of your ACF text field
    $field_value = get_field('clikup_client_support_list_id', $post->ID); // Retrieve current field value

    // Modify $field_value as per your requirement
    // For example, concatenate post title with some text
    //if ( $post->post_title ==  ){
    //if (in_array($post->post_title, $all_clients)){
    $clikup_client_id = $all_clients[$post->post_title];
    $modified_value = $clikup_client_id ? $clikup_client_id : 'Client not found in CU';
    // Update the ACF field with the modified value
    update_field('clikup_client_support_list_id', $modified_value, $post->ID);
	//}
}
*/