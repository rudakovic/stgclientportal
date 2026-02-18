<?php

function contact_pagination() {
	// Get search term and pagination info
	$search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
	$paged = isset($_GET['pg']) ? intval($_GET['pg']) : 1;
	$posts_per_page = 1;

	// Get Client IDs that match the search term
	$client_ids = [];
	if (!empty($search)) {
		$client_query = new WP_Query([
			'post_type'      => 'valet-client', // Make sure this is the correct CPT for clients
			'posts_per_page' => -1,
			's'              => $search, // Search in client title
			'fields'         => 'ids'
		]);

		$client_ids = $client_query->posts;
	}

	// Meta query to search in ACF fields
	$meta_query = [
		'relation' => 'OR',
		[
			'key' => 'contact_email',
			'value' => $search,
			'compare' => 'LIKE'
		],
		[
			'key' => 'contact_first_name',
			'value' => $search,
			'compare' => 'LIKE'
		],
		[
			'key' => 'contact_last_name',
			'value' => $search,
			'compare' => 'LIKE'
		]
	];

	// If clients were found, add them to the query
	if (!empty($client_ids)) {
		$meta_query[] = [
			'key'     => 'contact_client',
			'value'   => $client_ids,
			'compare' => 'IN',
			'type'    => 'NUMERIC'
		];
	}

	$args = [
		'post_type'      => 'contact',
		'posts_per_page' => $posts_per_page,
		'paged'          => $paged,
		'meta_query'     => $meta_query,
	];

	$query = new WP_Query($args);

	// Generate pagination
	$total_pages = $query->max_num_pages;
	$pagination = '';

	if ($total_pages > 1) {
		$pagination .= '<ul class="page-numbers">';

		// Previous button
		if ($paged > 1) {
			$pagination .= '<li><p class="prev page-numbers" aria-label="Previous page" onclick="changePage(event,' . ($paged - 1) . ')">←</p></li>';
		}

		// First page
		if ($paged > 3) {
			$pagination .= '<li><p aria-label="Page 1" class="page-numbers" onclick=changePage(event,1)>1</p></li>';
			if ($paged > 4) {
				$pagination .= '<li><span class="page-numbers dots">…</span></li>';
			}
		}

		// Pages around current
		$start = max(1, $paged - 2);
		$end = min($total_pages, $paged + 2);

		for ($i = $start; $i <= $end; $i++) {
			if ($i == $paged) {
				$pagination .= '<li><span aria-label="Page ' . $i . '" aria-current="page" class="page-numbers current">' . $i . '</span></li>';
			} else {
				$pagination .= '<li><p aria-label="Page ' . $i . '" class="page-numbers" onclick="changePage(event,' . $i . ')">' . $i . '</p></li>';
			}
		}

		// Last page
		if ($paged < $total_pages - 2) {
			if ($paged < $total_pages - 3) {
				$pagination .= '<li><span class="page-numbers dots">…</span></li>';
			}
			$pagination .= '<li><p aria-label="Page ' . $total_pages . '" class="page-numbers" onclick="changePage(event,' . $total_pages . ')">' . $total_pages . '</p></li>';
		}

		// Next button
		if ($paged < $total_pages) {
			$pagination .= '<li><p class="next page-numbers" aria-label="Next page" onclick="changePage(event,' . ($paged + 1) . ')">→</p></li>';
		}

		$pagination .= '</ul>';
	}

	// Echo or return pagination HTML
	return $pagination;
}
add_shortcode('contact_pagination', 'contact_pagination');

function edit_new_contact_form_shortcode() {
	ob_start(); // Start output buffering

	?>
	<form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
		<?php wp_nonce_field('new_contact_action', 'new_contact_nonce'); ?>
		<input type="hidden" name="action" value="new_contact">
		<input type="hidden" name="client_id" value="<?php echo get_queried_object_id(); ?>">
		<input id="edit_contact_id" type="hidden" name="contact_id" value="">

		<div class="contact-login__input-row">
			<label>First Name</label>
			<input id="edit_contact_first_name" type="text" name="contact_first_name" placeholder="Enter First Name" required>
		</div>

		<div class="contact-login__input-row">
			<label>Last Name</label>
			<input id="edit_contact_last_name" type="text" name="contact_last_name" placeholder="Enter Last Name" required>
		</div>

		<div class="contact-login__input-row">
			<label>Email</label>
			<input id="edit_contact_email" type="email" name="contact_email" placeholder="Enter Email" required>
		</div>

		<div class="contact-login__input-row">
			<label>Password</label>
			<input id="edit_contact_password" type="password" name="contact_password" placeholder="Enter Password" required>
		</div>

		<button type="submit">Submit</button>
	</form>
	<?php

	return ob_get_clean(); // Return the buffered content
}
add_shortcode('edit_new_contact_form', 'edit_new_contact_form_shortcode');


function delete_contact_form_shortcode() {
	ob_start(); // Start output buffering

	?>
	<form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
		<?php wp_nonce_field('delete_contact_action', 'delete_contact_nonce'); ?>
		<input type="hidden" name="action" value="delete_contact">
		<input id="delete-modal-contact-id" type="hidden" name="contact_id" value="">
		<button type="submit">Delete</button>
	</form>
	<?php

	return ob_get_clean(); // Return the buffered content
}
add_shortcode('delete_contact_form', 'delete_contact_form_shortcode');

function forgot_pass_contact_form_shortcode() {
	ob_start(); // Start output buffering

	?>
	<form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
		<?php wp_nonce_field('contact_forgot_password_action', 'contact_forgot_password_nonce'); ?>
		<input type="hidden" name="action" value="contact_forgot_password">

		<div class="contact_login__input-row">
			<label>Email</label>
			<input type="email" name="contact_email" placeholder="Enter Email Address" required>
		</div>

		<div class="contact-login__button-wrap">
			<button type="submit">Send</button>
		</div>
	</form>
	<?php

	return ob_get_clean(); // Return the buffered content
}
add_shortcode('forgot_pass_contact_form', 'forgot_pass_contact_form_shortcode');

function login_contact_form_shortcode() {
	ob_start(); // Start output buffering

	?>
	<form class="contact_login__form" method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
		<?php wp_nonce_field('contact_login_action', 'contact_login_nonce'); ?>
		<input type="hidden" name="action" value="contact_login">

		<div class="contact-login__input-row">
			<label>Email</label>
			<input type="email" name="contact_email" placeholder="Enter Email Address" required>
			<a href="/contact-forgot-password" class="contact-login__forgot-password">Forgot your password?</a>
		</div>

		<div class="contact-login__input-row">
			<label>Password</label>
			<input type="password" name="contact_password" placeholder="Enter Password" required>
		</div>

		<div class="contact-login__button-wrap">
			<button type="submit">Login</button>
		</div>
	</form>
	<?php

	return ob_get_clean(); // Return the buffered content
}
add_shortcode('login_contact_form', 'login_contact_form_shortcode');

function reset_pass_contact_form_shortcode() {
	ob_start(); // Start output buffering

	if (isset($_GET['token'])) {
		$token = sanitize_text_field($_GET['token']);

		// Find the client based on the stored token
		$args = [
			'post_type' => 'contact',
			'meta_query' => [
				[
					'key'   => 'client_reset_token',
					'compare' => 'EXISTS'
				]
			]
		];

		$query = new WP_Query($args);

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$contact_id = get_the_ID();
				$stored_hash = get_post_meta($contact_id, 'contact_reset_token', true);
				$expires = get_post_meta($contact_id, 'contact_reset_expires', true);

				// Validate the token
				if (password_verify($token, $stored_hash) && time() < $expires) {
					// Token is valid, show the password reset form
					?>
					<form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
						<?php wp_nonce_field('contact_reset_password_action', 'contact_reset_password_nonce'); ?>
						<input type="hidden" name="action" value="contact_reset_password">
						<input type="hidden" name="contact_id" value="<?php echo esc_attr($contact_id); ?>">
						<div class="contact_login__input-row">
							<label>Password</label>
							<input type="password" name="new_password" placeholder="Enter New Password" required>
						</div>
						<div class="contact-login__button-wrap">
							<button type="submit">Reset Password</button>
						</div>
					</form>
					<?php
				} else {
					?>
					<p>Invalid or expired reset link.</p>
					<?php
				}
			}
		} else {
			?>
			<p>Invalid reset link.</p>
			<?php
		}
	} else {
		?>
		<p>Invalid reset link.</p>
		<?php
	}

	return ob_get_clean(); // Return the buffered content
}
add_shortcode('reset_pass_contact_form', 'reset_pass_contact_form_shortcode');