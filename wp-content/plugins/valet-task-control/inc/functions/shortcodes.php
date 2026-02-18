<?php
use ValetTasks\Services\Client;
use ValetTasks\Services\ClickUp;
use ValetTasks\Services\Project;

function valet_open_tasks_shortcode( $atts ) {
    $atts = shortcode_atts(
            [
                    'limit' => 0,
                	'type'  => 'open'
            ],
            $atts,
            'valet_open_tasks'
    );

    $limit = intval( $atts['limit'] );
    $type = $atts['type'];

    $client = new Client();
	$clickup = new ClickUp();
	$client_lists = $client->get_clickup_lists() ?? [];
    $post_id = $client->get_id();

    // Use transient key per post
    $transient_key = 'clickup_tasks_' . $post_id;

    // Try to get tasks from transient
    $tasks = get_transient( $transient_key );

    if ( $tasks === false || count( $tasks ) == 0 ) {
        $tasks = [];
		foreach ($client_lists as $list) {
			if($list['show_clickup_list']) {
				$list_tasks = $clickup->getTasks( $list['client_clickup_list_id'] );
                if(isset($list_tasks['tasks'])) {
                    $tasks = array_merge( $tasks ?? [], $list_tasks['tasks'] );
                }
			}
		}
        set_transient( $transient_key, $tasks, 3600 );
    }

	ob_start();
	?>
	<div class="overflow-x-auto">
		<table class="table">
			<thead>
			<tr>
				<th>Date</th>
				<th>Task</th>
				<th>Hours Used</th>
			</tr>
			</thead>
			<tbody>
			<?php
			if ( ! empty( $tasks ) && is_array( $tasks ) ) :
				$counter = 0;
				foreach ( $tasks as $row ) :
                    $status_type = $row['status']['type'] ?? '';
                    if ( $type === 'open' && $status_type === 'closed' ) {
                        continue;
                    }

                    if ( $type === 'closed' && $status_type !== 'closed' ) {
                        continue;
                    }
                    if ( $limit > 0 && $counter >= $limit ) {
                        break;
                    }
					$date = isset( $row['date_created'] ) ? date( 'M-d-y', intval( $row['date_created'] / 1000 ) ) : '';
					?>
					<tr>
						<td><?php echo esc_html( $date ); ?></td>
						<td><?php echo esc_html( $row['name'] ?? '' ); ?></td>
						<td>0</td>
					</tr>
					<?php
					$counter++;
				endforeach;
			else :
				?>
				<tr>
					<td class="client-page__no-tasks-row" colspan="3">
						No tasks
					</td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php

	return ob_get_clean();
}

add_shortcode( 'valet_open_tasks', 'valet_open_tasks_shortcode' );

function valet_get_task_count( $post_id, $type = 'open' ) {

    $transient_key = 'clickup_tasks_' . $post_id;
    $tasks = get_transient( $transient_key );

    if ( $tasks === false || ! is_array( $tasks ) ) {
        return 0;
    }

    $count = 0;

    foreach ( $tasks as $row ) {
        $status_type = $row['status']['type'] ?? '';

        if ( $type === 'open' && $status_type === 'closed' ) {
            continue;
        }

        if ( $type === 'closed' && $status_type !== 'closed' ) {
            continue;
        }

        $count++;
    }

    return $count;
}

function valet_tasks_checklist_shortcode() {
    add_action( 'wp_footer', 'valet_enqueue_tasks_script' );

    $project = new Project();
    $clickup = new ClickUp();
    $client_list_id = $project->get_clickup_list_id();
    $post_id = $project->get_id();

    if(empty($client_list_id)) {
        return '<p>No ClickUp list ID added.</p>';
    }

    // Use transient key per post
    $transient_key = 'clickup_tasks_' . $post_id;

    // Try to get tasks from transient
    $tasks = get_transient( $transient_key );

    if ( $tasks === false || count( $tasks ) == 0 ) {
        $data = $clickup->getTasks( $client_list_id );
        $tasks = $data['tasks'];
        set_transient( $transient_key, $tasks, 3600 );
    }

    if ( empty( $tasks ) ) {
        return '<p>No tasks found.</p>';
    }

    $nonce = wp_create_nonce( 'valet_tasks_nonce' );
    ob_start();
    ?>
    <div class="valet-project-tasks" id="valet-project-tasks" data-nonce="<?php echo esc_attr( $nonce ); ?>">
        <div id="valet-project-task-list">
            <?php foreach ( $tasks as $task ) : ?>
                <?php
                $task_id   = esc_attr( $task['id'] );
                $task_name = esc_html( $task['name'] );
                $checked   = ( isset( $task['status']['status'] ) && $task['status']['status'] === 'complete' );
                ?>
                <label style="display:block; margin-bottom:6px;">
                    <input
                            type="checkbox"
                            name="task_<?php echo $task_id; ?>"
                            data-task-id="<?php echo $task_id; ?>"
                            data-post-id="<?php echo $post_id; ?>"
                            <?php checked( $checked ); ?>
                            onchange="valetTaskToggle(this)"
                    >
                    <?php echo $task_name; ?>
                </label>
            <?php endforeach; ?>
        </div>
        <div style="display:none;" id="valet-project-task-list-spinner">
            <p>loading...</p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode( 'valet_tasks_checklist', 'valet_tasks_checklist_shortcode' );