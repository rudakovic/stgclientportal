<?php
header( 'Access-Control-Allow-Origin: https://cpvaletdev.wpenginepowered.com' );

$query = array(
  "archived" => "false",
  "include_closed" => "false"
);

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_HTTPHEADER => [
    "Authorization: pk_57096564_GTODYU5KD1U0D88DUOM38H5NDTFI8DBA"
  ],
  CURLOPT_URL => "https://api.clickup.com/api/v2/list/211218773/task?" . http_build_query($query),
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "GET",
]);

$response = curl_exec($curl);
$error = curl_error($curl);

curl_close($curl);

if ($error) {
  echo "cURL Error #:" . $error;
} else {
    $response_decode = json_decode( $response, true );

    foreach ( $response_decode as $list_client_tasks ) {
      if (is_array($list_client_tasks) || is_object($list_client_tasks)){

      foreach ( $list_client_tasks as $single_task ) {

        $custom_id         = $single_task['custom_id'];
        $clickup_task_url  = $single_task ['url'];
        $clickup_task_name = $single_task ['name'];

        echo '<a href="' . htmlspecialchars( $clickup_task_url ) . '" target="_blank">' . htmlspecialchars( $clickup_task_name ) . '</a><br>';

      }
    }
    }
  }

