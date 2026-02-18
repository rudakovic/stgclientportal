<?php
/**
 * REST API: PH_REST_Request_Change_Notifications_Controller class
 *
 * Handles request change notifications for ProjectHuddle via REST API.
 * This controller manages the endpoint for sending change request notifications
 * to project assignees when changes are requested in a project.
 *
 * @since      2.7.0
 * @package    ProjectHuddle
 * @subpackage REST_API
 * @author     BSF Team
 */

defined('ABSPATH') || exit;

/**
 * Core class to access ProjectHuddle request change notifications via REST API.
 *
 * @since 2.7.0
 */
class PH_REST_Request_Change_Notifications_Controller extends WP_REST_Controller
{
    /**
     * REST API namespace.
     * @var string
     */
    protected $namespace = 'projecthuddle/v2';

    /**
     * REST route base.
     *
     * @var string
     */
    protected $rest_base = 'request-changes';

    public function __construct()
    {
        $this->namespace = 'projecthuddle/v2';
        $this->rest_base = 'request-changes';
    }

    /**
     * Register REST API routes.
     */
    public function register_routes(): void {
         register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/log',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'get_last_request_time' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                    'args'                => [
                        'project_id' => [
                            'required'          => true,
                            'validate_callback' => [ $this, 'validate_numeric' ],
                            'description'       => __( 'The ID of the project to fetch the last request time for.', 'project-huddle' ),
                            'type'              => 'integer',
                        ],
                    ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'send_notification' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                    'args'                => $this->get_request_args(),
                ],
            ]
        );
    }

    /**
     * Get route argument schema.
     *
     * @return array Request argument schema.
     */
    protected function get_request_args(): array {
        return [
            'assignee_email' => [
                'required'          => true,
                'validate_callback' => [ $this, 'validate_email' ],
                'sanitize_callback' => 'sanitize_email',
                'description'       => __( 'Assignee email address.', 'project-huddle' ),
                'type'              => 'string',
            ],
            'comments' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
                'description'       => __( 'Change request comment.', 'project-huddle' ),
                'type'              => 'string',
                'default'           => '',
            ],
            'requested_by' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
                'description'       => __( 'User requesting the change.', 'project-huddle' ),
                'type'              => 'string',
            ],
            'project_id' => [
                'required'          => true,
                'sanitize_callback' => 'absint',
                'description'       => __( 'Project ID.', 'project-huddle' ),
                'type'              => 'integer',
            ],
            'project_approved' => [
                'required'          => false,
                'sanitize_callback' => 'rest_sanitize_boolean',
                'description'       => __( 'Whether the project is approved.', 'project-huddle' ),
                'type'              => 'boolean',
                'default'           => false,
            ],
            'project_link' => [
                'required'          => true,
                'sanitize_callback' => 'esc_url_raw',
                'validate_callback' => 'rest_validate_request_arg',
                'description'       => __( 'Project link URL.', 'project-huddle' ),
                'type'              => 'string',
            ],
            'project_model_type' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
                'description'       => __( 'Project model type.', 'project-huddle' ),
                'type'              => 'string',
            ],
        ];
    }

    /**
     * Validate numeric parameter.
     *
     * @param mixed $value Parameter value.
     * @return bool True if valid, false otherwise.
     */
    public function validate_numeric( $value ): bool {
        return is_numeric( $value );
    }

    /**
     * Validate email parameter.
     *
     * @param mixed $value Parameter value.
     * @return bool True if valid, false otherwise.
     */
    public function validate_email( $value ): bool {
        return is_email( $value ) !== false;
    }

    /**
     * Get the last request time for a project.
     *
     * @param WP_REST_Request $request Current REST request.
     * @return WP_REST_Response REST response.
     */
    public function get_last_request_time( WP_REST_Request $request ): WP_REST_Response {
        $project_id       = (int) $request->get_param( 'project_id' );
        $last_request_time = get_post_meta( $project_id, 'log_request_change_last_requested', true );

        if ( ! $last_request_time ) {
            return new WP_REST_Response(
                [
                    'message'           => __( 'No last request time available for this project.', 'project-huddle' ),
                    'project_id'        => $project_id,
                    'last_request_time' => null,
                ],
                200
            );
        }

        return new WP_REST_Response(
            [
                'message'           => __( 'Last request time retrieved successfully.', 'project-huddle' ),
                'project_id'        => $project_id,
                'last_request_time' => $last_request_time,
            ],
            200
        );
    }

    /**
     * Update the last request time for a project.
     *
     * @param WP_REST_Request $request Current REST request.
     * @return bool True on success, false otherwise.
     */
    public function update_last_request_time( WP_REST_Request $request ): bool {
        $project_id   = (int) $request->get_param( 'project_id' );
        $current_time = current_time( 'mysql' );

        return update_post_meta( $project_id, 'log_request_change_last_requested', $current_time );
    }

    /**
     * Handle the request change notification.
     *
     * @param WP_REST_Request $request Current REST request.
     * @return WP_REST_Response REST response.
     */
    public function send_notification( WP_REST_Request $request ): WP_REST_Response {
        $params = $request->get_params();

        // Sanitize and validate parameters.
        $assignee_email     = sanitize_email( $params['assignee_email'] );
        $comment            = sanitize_text_field( $params['comments'] ?? '' );
        $requested_by       = sanitize_text_field( $params['requested_by'] );
        $project_id         = absint( $params['project_id'] );
        $project_approved   = rest_sanitize_boolean( $params['project_approved'] ?? false );
        $project_link       = esc_url_raw( $params['project_link'] );
        $project_model_type = sanitize_text_field( $params['project_model_type'] );

        error_log( sprintf(
            'PH_REST_Request_Change_Notifications_Controller::send_notification called with params: %s',
            print_r( $params, true )
        ));

        // Validate project existence using strict post_type check.
        $projects = get_posts([
            'include'     => [ $project_id ],
            'post_type'   => [ 'ph-website', 'ph-project' ],
            'post_status' => 'any',
            'numberposts' => 1
        ]);

        $project = !empty($projects) ? $projects[0] : null;
        if ( ! $project ) {
            return new WP_REST_Response(
                [
                    'message' => __( 'Invalid project ID or not a valid project post type.', 'project-huddle' ),
                ],
                400
            );
        }

        error_log( sprintf(
            'PH_REST_Request_Change_Notifications_Controller::send_notification project found: %s',
            print_r( $project, true )
        ));

        // Validate email.
        if ( ! is_email( $assignee_email ) ) {
            return new WP_REST_Response(
                [
                    'message' => __( 'Invalid assignee email.', 'project-huddle' ),
                ],
                400
            );
        }

        // Trigger the email notification.
        $this->trigger_email_action(
            [
                'assignee_email'    => $assignee_email,
                'comments'          => $comment,
                'requested_by'      => $requested_by,
                'project_title'     => $project->post_title, // pass project title
                'project_approved'  => $project_approved,
                'project_link'      => $project_link,
                'project_model_type'=> $project_model_type,
            ]
        );

        // Update the last request time.
        $this->update_last_request_time( $request );

        return new WP_REST_Response(
            [
                'message' => sprintf(
                    __( 'Change Request received for %s.', 'project-huddle' ),
                    esc_html( $assignee_email )
                ),
            ],
            200
        );
    }

    /**
     * Triggers the email notification action.
     *
     * @param array $params Notification parameters.
     */
    protected function trigger_email_action( array $params ): void {
        /**
         * Fires when an email notification for a request change is triggered.
         *
         * @param string $assignee_email Assignee email address.
         * @param string $subject Email subject.
         * @param string $message Email message.
         * @param int    $project_id Project ID.
         * @param bool   $project_approved Whether the project was approved.
         * @param string $project_link Project link.
         * @param string $project_model_type Project model type.
         */
        do_action(
            'ph_email_request_change',
            $params['assignee_email'],
            sprintf(
                __( 'Change Request received for %s', 'project-huddle' ),
                $params['project_title'] // Use title, not just ID
            ),
            $params['comments'],
            $params['requested_by'],
            $params['project_approved'],
            $params['project_link'],
            $params['project_model_type'],
        );
    }

    /**
     * Permissions check for sending notification.
     *
     * @param WP_REST_Request $request Current REST request.
     * @return bool True if the user has permission, false otherwise.
     */
    public function permissions_check(WP_REST_Request $request)
    {
        return true;
    }
}

add_action('rest_api_init', function () {
    (new PH_REST_Request_Change_Notifications_Controller())->register_routes();
});