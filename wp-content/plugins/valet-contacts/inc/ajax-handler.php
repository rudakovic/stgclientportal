<?php
add_action('wp_ajax_search_contacts', 'search_contacts_callback');
add_action('wp_ajax_nopriv_search_contacts', 'search_contacts_callback');

function search_contacts_callback() {
	// Security check
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'search_contacts_nonce')) {
	wp_send_json_error(['message' => 'Invalid request.']);
	die();
	}

	$search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
	$paged = isset($_POST['page']) ? absint($_POST['page']) : 1;
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
		// Now check the relationship field 'contact_client' which stores the client post ID
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
//		's'              => $search,
		'meta_query'     => $meta_query,
	];

	$query = new WP_Query($args);

	if ($query->have_posts()) {
		ob_start();
		while ($query->have_posts()) {
			$query->the_post();

			// Get the custom fields (ACF fields or post meta)
			$title = esc_html( get_the_title( get_the_ID() ) );
			$email        = esc_html( get_post_meta( get_the_ID(), 'contact_email', true ) );
			$first_name   = esc_html( get_post_meta( get_the_ID(), 'contact_first_name', true ) );
			$last_name    = esc_html( get_post_meta( get_the_ID(), 'contact_last_name', true ) );
			$job_title    = esc_html( get_post_meta( get_the_ID(), 'contact_job_title', true ) );
			$role_meta = get_post_meta( get_the_ID(), 'contact_role', true ); // Get stored values
			$field_obj = get_field_object('contact_role'); // Get field object with choices
			$role = [];
			if (!empty($role_meta) && !empty($field_obj['choices'])) {
				foreach ((array) $role_meta as $value) { // Ensure it's an array
					if (isset($field_obj['choices'][$value])) {
						$role[] = $field_obj['choices'][$value]; // Get the label
					}
				}
			}
			$client_id    = get_post_meta( get_the_ID(), 'contact_client', true );
			$client_title = esc_html( get_the_title( $client_id ));
			$client_url   = esc_html( get_permalink( $client_id ));



			// Custom row structure for each contact
			echo '<div class="brxe-pvpino brxe-block contact-table__row contact-table-results">';
			if(!empty($first_name) || !empty($last_name)) {
				echo '<div class="brxe-bveepu brxe-div contact-table__row-item"><p class="brxe-enrlmv brxe-text-basic">' . $first_name . ' ' . $last_name . '</p></div>';
			} else {
				echo '<div class="brxe-bveepu brxe-div contact-table__row-item"><p class="brxe-enrlmv brxe-text-basic">' . $title . '</p></div>';
			}
			echo '<div class="brxe-xeavrj brxe-div contact-table__row-item contact-table__row-item-space-between">';
			echo '<p class="brxe-gckbkz brxe-text-basic">' . $email . '</p>';
			echo '<i class="ti-layers brxe-zuyhuc brxe-icon contact-table__row-item-copy" onclick="copyContactEmail(event, \'' . $email . '\')"></i>';
			echo '</div>';
			echo '<div class="brxe-bhsubp brxe-div contact-table__row-item"><p class="brxe-idfhtb brxe-text-basic"><a href="' . $client_url . '" aria-label="Read more about ' . $client_title . '">' . $client_title . '</a></p></div>';
			echo '<div class="brxe-mrtejg brxe-div contact-table__row-item"><p class="brxe-twwrta brxe-text-basic">' . $job_title . '</p></div>';
			echo '<div class="brxe-rcwbwk brxe-div contact-table__row-item"><p class="brxe-ldfhln brxe-text-basic">' . esc_html( implode(', ', $role) ) . '</p></div>';
			echo '</div>';
		}
		$output = ob_get_clean();
	} else {
		$output = '<p>No contacts found.</p>';
	}

	// Generate pagination
	$total_pages = $query->max_num_pages;
	$pagination = '';

	if ($total_pages > 1) {
		$pagination .= '<ul class="page-numbers">';

		// Previous button
		if ($paged > 1) {
			$pagination .= '<li><p class="prev page-numbers" aria-label="Previous page"  onclick="changePage(event,' . ($paged - 1) . ')" data-page="' . ($paged - 1) . '">←</p></li>';
		}

		// First page
		if ($paged > 3) {
			error_log('ovde:' . $paged );
			$pagination .= '<li><p aria-label="Page 1" class="page-numbers" onclick="changePage(event,1)" data-page="1">1</p></li>';
			if ($paged > 4) {
				$pagination .= '<li><span class="page-numbers dots">…</span></li>';
			}
		}

		// Pages around current
		$start = max(1, $paged - 2);
		$end = min($total_pages, $paged + 2);

		for ($i = $start; $i <= $end; $i++) {
			error_log('ne ne ovde:' . $paged );
			if ($i == $paged || ($i == 1 && $paged == 0)) {
				$pagination .= '<li><span aria-label="Page ' . $i . '" aria-current="page" class="page-numbers current">' . $i . '</span></li>';
			} else {
				$pagination .= '<li><p aria-label="Page ' . $i . '" class="page-numbers" onclick="changePage(event,' . $i . ')" data-page="' . $i . '">' . $i . '</p></li>';
			}
		}

		// Last page
		if ($paged < $total_pages - 2) {
			if ($paged < $total_pages - 3) {
				$pagination .= '<li><span class="page-numbers dots">…</span></li>';
			}
			$pagination .= '<li><p aria-label="Page ' . $total_pages . '" class="page-numbers" onclick="changePage(event,' . $total_pages . ')" data-page="' . $total_pages . '">' . $total_pages . '</p></li>';
		}

		// Next button
		if ($paged < $total_pages) {
			$pagination .= '<li><p class="next page-numbers" aria-label="Next page" onclick="changePage(event,' . ($paged + 1) . ')" data-page="' . ($paged + 1) . '">→</p></li>';
		}

		$pagination .= '</ul>';
	}

	wp_reset_postdata();
	wp_send_json_success(['html' => $output, 'pagination' => $pagination, 'search' => $search, 'page' => $paged]);
	wp_die();
}