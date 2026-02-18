<?php
/**
 * Plugin Name: Valet Client Portal
 * Description: Everhour API, Clikup API, Hubspot API - reatiners, projects, list of tasks, submitting tasks to CU
 * Author: Valet - Milos Milosevic
 * Author URI: https://valet.io
 * Version: 1.0
 */


require_once plugin_dir_path( __FILE__ ) . 'include/cp-cpt.php';
require_once plugin_dir_path( __FILE__ ) . 'include/cp-role.php';
//require_once plugin_dir_path( __FILE__ ) . 'include/cu-open-tasks.php';



/**
 * Redirect upon login
 *
 * @param redirect_to $redirect_to get from login_redirect.
 * @param request     $request get from login_redirect.
 * @param user        $user get from login_redirect.
 */
function valet_client_redirect( $redirect_to, $request, $user ) {
    if($user instanceof WP_User && isset($user->ID)) {
	    $valet_client_id = get_user_meta( $user->ID, 'valet_client' );
    } else {
	    $valet_client_id = null;
    }

    if ( ! is_admin() && $valet_client_id ) {

        $post_id = $valet_client_id[0];
        $post    = get_post( $post_id );
        $slug    = $post->post_name;

	    return '/valet-client/' . $slug;
    }
}

add_filter( 'login_redirect', 'valet_client_redirect', 10, 3 );

function limit_user_access() {

	global $post;

	$client_id = get_current_user_id();
	$current_url = home_url( add_query_arg( null, null ) );

	$valet_client_post_id = get_field( 'valet_client', 'user_' . $client_id );

	if (!session_id()) {
		session_start();
	}
	$is_alternative_logged_in = isset($_SESSION['alt_logged_in']) && $_SESSION['alt_logged_in'] === true;

	if(is_user_logged_in()) {
		if ( current_user_can( 'manage_options' ) ) {
		    return;
		} else {
			if ( strpos( $current_url, $valet_client_post_id->guid ) !== false  || strpos($_SERVER['REQUEST_URI'], '/checkout' ) !== false || strpos($_SERVER['REQUEST_URI'], '/share-cart-url' ) !== false || strpos($_SERVER['REQUEST_URI'], '/my-account' ) !== false) {
				return;
			} else {
				wp_redirect( esc_url( $valet_client_post_id->guid ), 307 );
			}
		}
	}  else {
		if ($is_alternative_logged_in || strpos( $_SERVER['REQUEST_URI'], '/contact-login' ) !== false || strpos( $_SERVER['REQUEST_URI'], '/contact-forgot-password' ) !== false) {
			$contact_id = isset($_SESSION['contact_id']) ? $_SESSION['contact_id'] : null;
			if ($contact_id) {
				// Query the Contact CPT using the contact_id
				$contact = get_post($contact_id);
				if ($contact && $contact->post_type === 'contact') {
					$client = get_field( 'contact_client', $contact->ID );
				}
				$valet_client_permalink = get_permalink($client->ID);

                if ($valet_client_permalink) {
	                if ( strpos( $current_url, $valet_client_permalink ) !== false) {
		                return;
	                } else {
		                wp_redirect( esc_url( $valet_client_permalink ), 307 );
	                }
                }
			}

			return;
		} else if ( strpos( $_SERVER['REQUEST_URI'], '/login' ) === false) {
			wp_redirect('/login');
			exit;
		}
	}
}

add_action( 'wp', 'limit_user_access' );

//add_filter('login_redirect', function ($redirect_to, $request, $user) {
//	// Check if a redirect URL is stored in the session
//	if (!session_id()) {
//		session_start();
//	}
//
//	if (!empty($_SESSION['redirect_to_after_login'])) {
//		$redirect_to = $_SESSION['redirect_to_after_login'];
//		unset($_SESSION['redirect_to_after_login']); // Clear the session
//	}
//
//	return $redirect_to;
//}, 10, 3);

function remove_admin_bar() {
	if ( current_user_can( 'administrator' ) ) {
		return true;
	}
	return false;
}

add_filter( 'show_admin_bar', 'remove_admin_bar', PHP_INT_MAX );

function valet_logout() {
	?>
	<a href="<?php echo wp_logout_url( home_url() ); ?>">Log out</a>
	<?php
}

add_shortcode( 'valet_logout', 'valet_logout' );


require_once plugin_dir_path( __FILE__ ) . 'include/eh-api-call.php';
require_once plugin_dir_path( __FILE__ ) . 'include/hubspot-api-call.php';
require_once plugin_dir_path( __FILE__ ) . 'include/cp-form.php';


function cu_get_tasks(){

	$everhour_client_id    = get_field( 'everhour_client_id' );
	$everhour_client_id_clean = ltrim($everhour_client_id, 'cl:1');
	$everhour_client_id_clean = ltrim($everhour_client_id_clean, '0');

$query = array(
  "archived" => "false",
);

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_HTTPHEADER => [
    "Authorization: pk_57096564_GTODYU5KD1U0D88DUOM38H5NDTFI8DBA"
  ],
  CURLOPT_URL => "https://api.clickup.com/api/v2/list/" . $everhour_client_id_clean . "/task?" . http_build_query($query),
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "GET",
]);

$response = curl_exec($curl);
$error = curl_error($curl);

curl_close($curl);

if ($error) {
  echo "cURL Error #:" . $error;
} else {

  	$clean_response = json_decode($response);
  	foreach ($clean_response as $single_task){
  		foreach ($single_task as $value) {
  			echo($value->name) . '<br>';
  		}
  	}

}


}


add_shortcode('cu_get_tasks', 'cu_get_tasks');