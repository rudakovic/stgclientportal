<?php
function validate_contact_email($valid, $value, $field, $input_name) {
	// Only run for the contact_email field
	if ($field['name'] !== 'contact_email') {
		return $valid;
	}

	$post_id = $_POST['post_ID'] ?? 0; // Get post ID
	$existing_contact = new WP_Query([
		'post_type'      => 'contact',
		'posts_per_page' => 1,
		'meta_query'     => [
			[
				'key'   => 'contact_email',
				'value' => $value,
			]
		],
		'post__not_in' => [$post_id] // Exclude the current post if updating
	]);

	if ($existing_contact->have_posts()) {
		return __('A contact with this email already exists.', 'text-domain');
	}

	return $valid;
}
add_filter('acf/validate_value/name=contact_email', 'validate_contact_email', 10, 4);

// hash contact password on save
function hash_contact_password($post_id) {
	if (get_post_type($post_id) !== 'contact') return;
	$password = get_field('contact_password', $post_id);
	if (!empty($password) && !str_starts_with($password, '$2y$')) {
		$hashed_password = password_hash($password, PASSWORD_DEFAULT);
		update_field('contact_password', $hashed_password, $post_id);
	}

	$email_sent = get_post_meta($post_id, 'email_sent', true);
	if (!$email_sent) {
		$email = get_field('contact_email', $post_id);

		if (!empty($email) && !empty($password)) {
			$subject = "Your Account Details";
			$message = "<p>Hello,</p><p>Your account has been created. Here are your login details:</p><p>Email:</p><p>$email</p><p>Password:</p><p>$password</p><p>Best regards,<br>The Valet.io Team</p>";
			$headers = ['Content-Type: text/plain; charset=UTF-8'];

			wp_mail($email, $subject, $message, $headers);

			// Mark that the email has been sent so it won't be sent again
			update_post_meta($post_id, 'email_sent', true);
		}
	}
}
add_action('acf/save_post', 'hash_contact_password');

function handle_contact_login() {
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		wp_die('Invalid request.');
	}
	if (!isset($_POST['contact_login_nonce']) || !check_admin_referer('contact_login_action', 'contact_login_nonce')) {
		wp_die('Nonce verification failed. Please try again.');
	}

	// Start the session if it's not already started
	if (!session_id()) {
		session_start();
	}

	$email = sanitize_email($_POST['contact_email']);
	$password = sanitize_text_field($_POST['contact_password']);

	// Query Contact CPT by email
	$args = [
		'post_type'  => 'contact',
		'meta_query' => [
			[
				'key'     => 'contact_email',
				'value'   => $email,
				'compare' => '='
			]
		]
	];
	$query = new WP_Query($args);

	if ($query->have_posts()) {
		$contact = $query->posts[0];
		$stored_password_hash = get_field('contact_password', $contact->ID);
		$contact_permission = get_field('contact_permission', $contact->ID);

		if (password_verify($password, $stored_password_hash)) {
			// Store login status in session instead of cookies
			$_SESSION['alt_logged_in'] = true;
			$_SESSION['contact_id'] = $contact->ID;
			$_SESSION['contact_permission'] = $contact_permission;

			// Redirect after login
			wp_redirect(get_permalink($contact->ID));
			exit;
		} else {
			wp_redirect(home_url('/contact-login/?error=invalid-password'));
			exit;
		}
	} else {
		wp_redirect(home_url('/contact-login/?error=no-contact'));
		exit;
	}
}
add_action('admin_post_nopriv_contact_login', 'handle_contact_login'); // For non-logged-in users
add_action('admin_post_contact_login', 'handle_contact_login');

function handle_contact_logout() {
	// Check nonce for security
	if (!isset($_POST['contact_logout_nonce_field']) || !wp_verify_nonce($_POST['contact_logout_nonce_field'], 'contact_logout_nonce')) {
		wp_die('Security check failed');
	}

	// Start session if not already started
	if (!session_id()) {
		session_start();
	}

	// Unset session variables
	unset($_SESSION['alt_logged_in']);
	unset($_SESSION['contact_id']);
	unset($_SESSION['contact_permission']);

	// Destroy the session
	session_destroy();

	// Redirect to homepage or login page
	wp_redirect(home_url('/contact-login/?logged_out=true'));
	exit;
}
add_action('admin_post_nopriv_contact_logout', 'handle_contact_logout'); // For non-logged-in users
add_action('admin_post_contact_logout', 'handle_contact_logout');

function handle_new_contact() {
	// Check nonce for security
	if (!isset($_POST['new_contact_nonce']) || !wp_verify_nonce($_POST['new_contact_nonce'], 'new_contact_action')) {
		wp_die('Security check failed');
	}

	// Sanitize inputs
	$first_name = sanitize_text_field($_POST['contact_first_name']);
	$last_name = sanitize_text_field($_POST['contact_last_name']);
	$email = sanitize_email($_POST['contact_email']);
	$password = sanitize_text_field($_POST['contact_password']);
	$client_id = sanitize_text_field($_POST['client_id']);
	$contact_id = sanitize_text_field($_POST['contact_id']);

	// Check if the email already exists in another contact post
	$existing_contact = new WP_Query([
		'post_type'      => 'contact',
		'posts_per_page' => 1,
		'meta_query'     => [
			[
				'key'   => 'contact_email',
				'value' => $email,
			]
		],
		'post__not_in' => [$contact_id] // Exclude the current post if updating
	]);

	if ($existing_contact->have_posts()) {
		// Redirect back with error message
		$redirect_url = add_query_arg('status', 'error', wp_get_referer());
		wp_redirect($redirect_url);
		exit;
	}

	if ($contact_id) {
		$post_id = $contact_id;
		$post_data = array(
			'ID'         => $post_id,
		);
		// Update the post
		wp_update_post($post_data);
	} else {
		// Insert a new Contact CPT
		$post_id = wp_insert_post([
			'post_title'   => $first_name,
			'post_type'    => 'contact',
			'post_status'  => 'publish'
		]);
	}

	if ($post_id) {
		// Store ACF fields
		update_field('contact_first_name', $first_name, $post_id);
		update_field('contact_last_name', $last_name, $post_id);
		update_field('contact_email', $email, $post_id);

		// Hash and store password
		if (!empty($password) && !str_starts_with($password, '$2y$')) {
			$hashed_password = password_hash($password, PASSWORD_DEFAULT);
			update_field('contact_password', $hashed_password, $post_id);
		}

		// Store client in ACF field
		if ($client_id > 0) {
			update_field('contact_client', $client_id, $post_id);
		}

		// Store role in ACF field
		update_field('contact_permission', 'member', $post_id);

		$email_sent = get_post_meta($post_id, 'email_sent', true);
		if (!$email_sent) {
			$email = get_field('contact_email', $post_id);

			if (!empty($email) && !empty($password)) {
				$subject = "Your Account Details";
				$message = "<p>Hello,</p><p>Your account has been created. Here are your login details:</p><p>Email:</p><p>$email</p><p>Password:</p><p>$password</p><p>Best regards,<br>The Valet.io Team</p>";
				$headers = ['Content-Type: text/plain; charset=UTF-8'];

				wp_mail($email, $subject, $message, $headers);

				// Mark that the email has been sent so it won't be sent again
				update_post_meta($post_id, 'email_sent', true);
			}
		}

		// Redirect back to the same page with success message
		$redirect_url = add_query_arg('status', 'success', wp_get_referer());
		wp_redirect($redirect_url);
		exit;
	} else {
		wp_die('Error creating contact');
	}
}
add_action('admin_post_nopriv_new_contact', 'handle_new_contact'); // For non-logged-in users
add_action('admin_post_new_contact', 'handle_new_contact');

function handle_delete_contact() {
	// Check nonce for security
	if (!isset($_POST['delete_contact_nonce']) || !wp_verify_nonce($_POST['delete_contact_nonce'], 'delete_contact_action')) {
		wp_die('Security check failed');
	}

	// Sanitize inputs
	$contact_id = sanitize_text_field($_POST['contact_id']);

	if ($contact_id) {
		wp_delete_post($contact_id, false);
		// Redirect back to the same page with success message
		$redirect_url = add_query_arg('status', 'success', wp_get_referer());
		wp_redirect($redirect_url);
		exit;
	} else {
		wp_die('Error deleting contact');
	}
}
add_action('admin_post_nopriv_delete_contact', 'handle_delete_contact'); // For non-logged-in users
add_action('admin_post_delete_contact', 'handle_delete_contact');

function handle_forgot_password_contact() {
	// Check nonce for security
	if (!isset($_POST['contact_forgot_password_nonce']) || !wp_verify_nonce($_POST['contact_forgot_password_nonce'], 'contact_forgot_password_action')) {
		wp_die('Security check failed');
	}

	// Sanitize inputs
	$email = sanitize_email($_POST['contact_email']);

	// Search for Contact CPT by email
	$args = [
		'post_type'  => 'contact',
		'meta_query' => [
			[
				'key'   => 'contact_email',
				'value' => $email,
				'compare' => '='
			]
		]
	];

	$query = new WP_Query($args);

	if ($query->have_posts()) {
		$contact_post = $query->posts[0];
		$contact_id = $contact_post->ID;

		// Generate a secure reset token
		$token = wp_generate_password(32, false, false);
		$token_hash = password_hash($token, PASSWORD_BCRYPT);
		$expires = time() + (30 * 60); // Token expires in 30 minutes

		// Store token and expiration in meta fields
		update_post_meta($contact_id, 'contact_reset_token', $token_hash);
		update_post_meta($contact_id, 'contact_reset_expires', $expires);

		// Create reset URL
		$reset_url = home_url("/contact-reset-password/?token=" . urlencode($token));

		// Send email
		$subject = 'Password Reset Request';
		$message = '<p>Hello,</p><p>Click the link below to reset your password:</p><p><a target="_blank" href="' . $reset_url . '">' . $reset_url . '</a></p><p>This link will expire in 30 minutes.</p><p>Best regards,<br>The Valet.io Team</p>';
		$headers = ['Content-Type: text/html; charset=UTF-8'];

		wp_mail($email, $subject, $message, $headers);
		wp_redirect(home_url('/contact-login/?reset_password=true'));
	} else {
		echo "If the email exists, a reset link has been sent.";
	}
}
add_action('admin_post_nopriv_contact_forgot_password', 'handle_forgot_password_contact'); // For non-logged-in users
add_action('admin_post_contact_forgot_password', 'handle_forgot_password_contact');

function handle_reset_password_contact() {
	// Check nonce for security
	if (!isset($_POST['contact_reset_password_nonce']) || !wp_verify_nonce($_POST['contact_reset_password_nonce'], 'contact_reset_password_action')) {
		wp_die('Security check failed');
	}

	// Sanitize inputs
	$contact_id = isset($_POST['contact_id']) ? (int) $_POST['contact_id'] : 0;
	$new_password = sanitize_text_field($_POST['new_password']);

	// Save the new password (you may want to use password hashing)
	$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
	update_field('contact_password', $hashed_password, $contact_id);

	// Invalidate the token
	delete_post_meta($contact_id, 'contact_reset_token');
	delete_post_meta($contact_id, 'contact_reset_expires');

	echo "Password has been reset successfully.";
}
add_action('admin_post_nopriv_contact_reset_password', 'handle_reset_password_contact'); // For non-logged-in users
add_action('admin_post_contact_reset_password', 'handle_reset_password_contact');


// Functions for frontend
function is_contact_logged_in() {
	if (!session_id()) {
		session_start();
	}
	if(!empty($_SESSION['contact_id'])) {
		return 1;
	}
	return 0;
}

function get_logged_in_contact_name() {
	if (!session_id()) {
		session_start();
	}
	if(!empty($_SESSION['contact_id'])) {
		$contact_id = $_SESSION['contact_id'];
		$contact_first_name = get_field('contact_first_name', $contact_id);

		if ($contact_first_name) {
			return $contact_first_name;
		} else {
			return get_the_title($contact_id);
		}
	}
	return "";
}

function get_logged_in_contact_avatar() {
	if (!session_id()) {
		session_start();
	}
	if(!empty($_SESSION['contact_id'])) {
		$contact_id = $_SESSION['contact_id'];
		$contact_first_avatar = get_field('contact_avatar', $contact_id);

		if ($contact_first_avatar) {
			return $contact_first_avatar;
		} else {
			return "";
		}
	}
	return "";
}

function is_contact_logged_in_admin() {
	if (!session_id()) {
		session_start();
	}
	if(isset($_SESSION['contact_permission']) && $_SESSION['contact_permission'] === 'admin') {
		return 1;
	}
	return 0;
}