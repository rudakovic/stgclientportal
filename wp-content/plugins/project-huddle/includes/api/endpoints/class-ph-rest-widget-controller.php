<?php
/**
 * REST API: PH_Page_Controller Class
 * 
 * Manages page status and access control endpoints for the ProjectHuddle plugin.
 * 
 * API Version: v2
 * Base Route: /wp-json/projecthuddle/v2/pages/
 * 
 * Endpoints:
 *   - POST   /status    : Update page status (enabled, permalink)
 *   - GET    /status    : Get page status (enabled, permalink)
 *   - POST   /access    : Update page access (enable/disable public access)
 *   - GET    /access    : Get page access (public access enabled or not)
 *   - GET    /website-type : Check if meta_key 'website_type' equals 'WordPress'
 *   - POST   /disable   : Disable/remove page by pages_key (unique)
 * 
 * REST API Controller for ProjectHuddle Page Management
 * 
 * @package    ProjectHuddle
 * @subpackage REST API
 * @version    2.0.0
 * @license    GPL-2.0+
 * @since      2.0.0
 */

class PH_Page_Controller extends WP_REST_Controller
{
    const API_NAMESPACE = 'projecthuddle/v2';
    const API_VERSION = 2;
    const REST_BASE = 'pages';
    const META_STATUS = '_page_status';
    const META_PERMALINK = '_page_permalink';
    const META_ACCESS = '_page_access_status';
    const DEFAULT_PAGE = 1;
    const DEFAULT_PER_PAGE = 50;

    /**
     * PH_Page_Controller constructor.
     *
     * Sets namespace and rest_base.
     */
    public function __construct()
    {
        $this->namespace = self::API_NAMESPACE;
        $this->rest_base = self::REST_BASE;
    }

    /**
     * Registers REST API routes.
     *
     * @since 2.0.0
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, "/{$this->rest_base}/status", [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_page_status'],
                'permission_callback' => [$this, 'check_edit_permissions'],
                'args' => $this->get_status_args(),
            ],
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_page_status'],
                'permission_callback' => [$this, 'check_read_permissions'],
                'args' => $this->get_common_args() + $this->get_page_id_arg(),
            ],
        ]);

        register_rest_route($this->namespace, "/{$this->rest_base}/access", [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_page_access'],
                'permission_callback' => [$this, 'check_edit_permissions'],
                'args' => $this->get_access_args(),
            ],
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_page_access'],
                'permission_callback' => [$this, 'check_read_permissions'],
                'args' => $this->get_common_args() + $this->get_page_id_arg(),
            ],
        ]);

        register_rest_route($this->namespace, "/{$this->rest_base}/website-type", [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_website_type'],
                'permission_callback' => [$this, 'check_read_permissions'],
                'args' => $this->get_website_type_args(),
            ],
        ]);

        register_rest_route($this->namespace, "/{$this->rest_base}/disable", [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'disable_page_by_key'],
                'permission_callback' => [$this, 'check_edit_permissions'],
                'args'                => $this->get_disable_args(),
            ],
        ]);

        register_rest_route($this->namespace, "/{$this->rest_base}/admin-pages", [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_pages'],
                'permission_callback' => [$this, 'check_read_permissions'],
                'args' => [
                    'search' => [
                        'description' => __('Search query for pages.', 'project-huddle'),
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ]
                ]
            ]
        ]);
    }

    /**
     * Updates the page status and permalink.
     *
     * @param WP_REST_Request $request REST request object.
     * @return WP_REST_Response
     */
    public function update_page_status(WP_REST_Request $request)
    {
        try {
            $post_id = $request->get_param('post_id');

            // Validate required parameters
            if (empty($post_id)) {
                return new WP_REST_Response([
                    'error' => __('post_id parameter is required.', 'project-huddle')
                ], 400);
            }

            // Validate post_id is numeric and corresponds to a valid post
            if (!is_numeric($post_id) || !$this->validate_post((int) $post_id)) {
                return new WP_REST_Response([
                    'error' => __('Invalid or non-existent post ID.', 'project-huddle')
                ], 400);
            }

            $post_id = (int) $post_id;
            $enabled = $request->get_param('enabled') ? '1' : '0';
            $new_permalink = $request->get_param('permalink');

            // Validate permalink format (basic validation)
            if ($new_permalink) {
                $new_permalink = esc_url_raw($new_permalink);
                if (!filter_var($new_permalink, FILTER_VALIDATE_URL)) {
                    return new WP_REST_Response([
                        'error' => __('Invalid permalink format.', 'project-huddle')
                    ], 400);
                }
            }

            // Fetch existing permalinks for the post
            $permalinks = get_post_meta($post_id, self::META_PERMALINK, true);
            if (!is_array($permalinks)) {
                $permalinks = [];
            }

            // Ensure that only currently enabled permalinks are checked for duplication
            $pages_enabled = get_option('pages_enabled', []);
            if (!is_array($pages_enabled)) {
                $pages_enabled = [];
            }

            if ($new_permalink) {
                foreach ($pages_enabled as $key => $value) {
                    if (
                        is_array($value) &&
                        isset($value['post_id'], $value['permalink']) &&
                        (int)$value['post_id'] === $post_id &&
                        $value['permalink'] === $new_permalink
                    ) {
                        return new WP_REST_Response([
                            'error' => __('Permalink already exists for this post.', 'project-huddle')
                        ], 400);
                    }
                }
            }

            // Generate a random unique 4-digit pages_key
            $pages_key = '';
            if ($new_permalink && $enabled === '1') {
                $max_attempts = 20; // avoid infinite loops
                $all_keys = array_keys($pages_enabled);
                $attempts = 0;
                do {
                    $candidate_key = str_pad(strval(rand(0, 9999)), 4, '0', STR_PAD_LEFT);
                    $attempts++;
                } while (in_array($candidate_key, $all_keys) && $attempts < $max_attempts);

                if (in_array($candidate_key, $all_keys)) {
                    return new WP_REST_Response([
                        'error' => __('Unable to generate unique pages_key. Try again.', 'project-huddle')
                    ], 500);
                }

                $pages_key = $candidate_key;

                // Store with key and permalink
                $permalinks[$pages_key] = [
                    'permalink' => $new_permalink,
                    'created_at' => current_time('mysql')
                ];
            }

            // Save the status and permalinks to the postmeta table
            update_post_meta($post_id, self::META_STATUS, $enabled);
            update_post_meta($post_id, self::META_PERMALINK, $permalinks);

            // Update the enabled pages based on the status and permalink
            if ($enabled === '1' && $new_permalink && $pages_key) {
                $pages_enabled[$pages_key] = [
                    'post_id' => $post_id,
                    'permalink' => $new_permalink,
                    'created_at' => current_time('mysql')
                ];
            } elseif ($new_permalink) {
                // Find and remove any existing entries with this permalink
                foreach ($pages_enabled as $key => $value) {
                    if (
                        is_array($value) &&
                        isset($value['permalink'], $value['post_id']) &&
                        $value['permalink'] === $new_permalink && (int)$value['post_id'] === $post_id
                    ) {
                        unset($pages_enabled[$key]);
                        break;
                    }
                }
            }

            // Save the updated enabled pages
            update_option('pages_enabled', $pages_enabled, true);

            // Prepare the response
            $response_data = [
                'post_id' => $post_id,
                'enabled' => $enabled === '1',
                'permalinks' => $new_permalink ?: null,
                'pages_key' => $pages_key ?: null,
                'enabled_pages_count' => count($pages_enabled)
            ];

            // Return a standardized response
            return $this->success_response($response_data);

        } catch (Exception $e) {
            return new WP_REST_Response([
                'error' => sprintf(__('An unexpected error occurred: %s', 'project-huddle'), $e->getMessage())
            ], 500);
        }
    }

    /**
     * Gets the status of pages, supporting optional post_id filtering and pagination.
     *
     * @param WP_REST_Request $request REST request object.
     * @return WP_REST_Response
     */
    public function get_page_status(WP_REST_Request $request)
    {
        try {
            $pages_enabled = get_option('pages_enabled', []);
            if (!is_array($pages_enabled)) {
                $pages_enabled = [];
            }

            $filter_post_id = $request->get_param('post_id'); // Get post_id from request
            $enabled_pages = [];
            $unique_posts = [];

            foreach ($pages_enabled as $pages_key => $page_data) {
                if (!is_array($page_data) || empty($page_data['post_id']) || empty($page_data['permalink'])) {
                    continue;
                }

                $post_id = $page_data['post_id'];

                // If post_id filter is applied, skip unmatched posts
                if (!empty($filter_post_id) && (int)$filter_post_id !== (int)$post_id) {
                    continue;
                }

                if (!$this->validate_post($post_id)) {
                    continue;
                }

                if (!in_array($post_id, $unique_posts)) {
                    $unique_posts[] = $post_id;
                }

                $enabled_pages[] = [
                    'post_id' => $post_id,
                    'enabled' => true,
                    'permalink' => $page_data['permalink'],
                    'pages_key' => $pages_key,
                    'created_at' => isset($page_data['created_at']) ? $page_data['created_at'] : null,
                    'post_title' => get_the_title($post_id),
                    'post_status' => get_post_status($post_id)
                ];
            }

            // Pagination
            $per_page = $request->get_param('per_page') ? (int)$request->get_param('per_page') : 10;
            $page = $request->get_param('page') ? (int)$request->get_param('page') : 1;

            if ($per_page > 0) {
                $total_pages = ceil(count($enabled_pages) / $per_page);
                $offset = ($page - 1) * $per_page;
                $enabled_pages = array_slice($enabled_pages, $offset, $per_page);
            } else {
                $total_pages = 1;
            }

            return $this->success_response([
                'api_version' => self::API_VERSION,
                'enabled_pages' => $enabled_pages,
                'unique_posts_count' => count($unique_posts),
                'total_permalinks' => count($enabled_pages),
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $per_page,
                    'total_pages' => $total_pages,
                    'total_items' => count($enabled_pages)
                ],
                'timestamp' => current_time('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            return new WP_REST_Response([
                'error' => sprintf(__('An unexpected error occurred: %s', 'project-huddle'), $e->getMessage())
            ], 500);
        }
    }

    /**
     * Disables/removes a page by its pages_key.
     *
     * @param WP_REST_Request $request REST request object.
     * @return WP_REST_Response
     */
    public function disable_page_by_key(WP_REST_Request $request)
    {
        try {
            // Get params
            $pages_key = $request->get_param('pages_key');
            $post_id_param = $request->get_param('post_id');

            // Validate required parameters
            if (empty($pages_key)) {
                return new WP_REST_Response([
                    'error' => __('pages_key parameter is required.', 'project-huddle')
                ], 400);
            }

            // Get all enabled pages
            $pages_enabled = get_option('pages_enabled', []);
            if (!is_array($pages_enabled)) {
                $pages_enabled = [];
            }

            // Check if the pages_key exists
            if (!isset($pages_enabled[$pages_key])) {
                return new WP_REST_Response([
                    'error' => __('Invalid pages_key - no page found with this key.', 'project-huddle')
                ], 404);
            }

            $page_data = $pages_enabled[$pages_key];
            $post_id_lookup = is_array($page_data) && isset($page_data['post_id']) ? $page_data['post_id'] : null;
            $post_id = $post_id_param ?: $post_id_lookup;

            // Cross-validate if both are provided
            if ($post_id_param && $post_id_param != $post_id_lookup) {
                return new WP_REST_Response([
                    'error' => __('Provided post_id does not match the record for this pages_key.', 'project-huddle')
                ], 400);
            }

            // Get current permalinks for the post
            $permalinks = get_post_meta($post_id, self::META_PERMALINK, true);
            if (!is_array($permalinks)) {
                $permalinks = [];
            }

            // Remove the pages_key from both locations
            unset($pages_enabled[$pages_key]);
            unset($permalinks[$pages_key]);

            // Update the stored data
            update_option('pages_enabled', $pages_enabled, true);
            update_post_meta($post_id, self::META_PERMALINK, $permalinks);

            // If no more permalinks exist for this post, disable it
            if (empty($permalinks)) {
                update_post_meta($post_id, self::META_STATUS, '0');
            }

            return $this->success_response([
                'success' => true,
                'message' => __('Page successfully disabled', 'project-huddle'),
                'post_id' => $post_id,
                'pages_key' => $pages_key,
                'remaining_permalinks' => count($permalinks),
                'enabled_pages_count' => count($pages_enabled)
            ]);

        } catch (Exception $e) {
            return new WP_REST_Response([
                'error' => sprintf(__('An unexpected error occurred: %s', 'project-huddle'), $e->getMessage())
            ], 500);
        }
    }

    /**
     * Updates access (public/private) for a page.
     *
     * @param WP_REST_Request $request REST request object.
     * @return WP_REST_Response|WP_Error
     */
    public function update_page_access(WP_REST_Request $request)
    {
        $post_id = $request->get_param('post_id');
        $access_enabled = (bool) $request->get_param('enable_page_access');

        if (!$this->validate_post($post_id)) {
            return $this->post_not_found_error();
        }

        update_post_meta($post_id, self::META_ACCESS, $access_enabled ? '1' : '0');

        return $this->success_response([
            'api_version' => self::API_VERSION,
            'post_id' => $post_id,
            'access_enabled' => $access_enabled,
        ]);
    }

    /**
     * Gets access status for a page.
     *
     * @param WP_REST_Request $request REST request object.
     * @return WP_REST_Response|WP_Error
     */
    public function get_page_access(WP_REST_Request $request)
    {
        $post_id = $request->get_param('post_id');

        if (!$this->validate_post($post_id)) {
            return $this->post_not_found_error();
        }

        return $this->success_response([
            'api_version' => self::API_VERSION,
            'post_id' => $post_id,
            'access_enabled' => get_post_meta($post_id, self::META_ACCESS, true) === '1',
        ]);
    }

    /**
     * Checks if the meta_key 'website_type' equals 'WordPress' for a page.
     *
     * @param WP_REST_Request $request REST request object.
     * @return WP_REST_Response|WP_Error
     */
 /**
 * Checks if the meta_key 'website_type' equals 'WordPress' for a page.
 * Always allows is_wordpress = true for current website if its meta_value is "".
 *
 * @param WP_REST_Request $request REST request object.
 * @return WP_REST_Response|WP_Error
 */
public function get_website_type(WP_REST_Request $request)
{
    $post_id = $request->get_param('post_id');
    $meta_key = 'website_type';

    if (!$this->validate_post($post_id)) {
        return $this->post_not_found_error();
    }

    $meta_value = get_post_meta($post_id, $meta_key, true);

    // Get the current website's main post ID.
    $current_website_post_id = get_option('page_on_front');
    if (!$current_website_post_id) {
        $current_website_post_id = get_option('page_for_posts');
    }
    if (!$current_website_post_id) {
        $current_website_post_id = $post_id;
    }

    // Normalize meta_value for easier checks
    $is_empty = ($meta_value === '' || $meta_value === null);

    // If this is the current website post and meta_value is "" or null, allow is_wordpress = true
    if ((int)$post_id === (int)$current_website_post_id && $is_empty) {
        return $this->success_response([
            'post_id' => $post_id,
            'meta_key' => $meta_key,
            'meta_value' => $meta_value,
            'is_wordpress' => true,
        ]);
    }

    // If meta_value is 'WordPress', "", or null => is_wordpress true
    if ($meta_value === 'WordPress' || $is_empty) {
        return $this->success_response([
            'post_id' => $post_id,
            'meta_key' => $meta_key,
            'meta_value' => $meta_value,
            'is_wordpress' => true,
        ]);
    }

    // If meta_value is 'custom' => is_wordpress false
    if ($meta_value === 'custom') {
        return $this->success_response([
            'post_id' => $post_id,
            'meta_key' => $meta_key,
            'meta_value' => $meta_value,
            'is_wordpress' => false,
        ]);
    }

    // Default: treat any other value as not WordPress
    return $this->success_response([
        'post_id' => $post_id,
        'meta_key' => $meta_key,
        'meta_value' => $meta_value,
        'is_wordpress' => true, // or false if you want to be strict
    ]);
}

    /**
     * Get all published pages including the homepage.
     */
    public function get_pages(WP_REST_Request $request) {
        $search_query = $request->get_param('search');
        if ($search_query !== null) {
            $search_query = sanitize_text_field($search_query);
        }

        $args = array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        );

        if (!empty($search_query)) {
            $args['s'] = $search_query;
        }

        $pages = get_posts($args);

        $response = array();

        // Get homepage ID and add homepage (static entry)
        $homepage_id = get_option('page_on_front');
        if ($homepage_id) {
            $homepage = get_post($homepage_id);
            if ($homepage) {
                $response[] = array(
                    'id'    => $homepage_id,
                    'title' => esc_html(get_the_title($homepage_id)),
                    'url'   => esc_url(get_permalink($homepage_id)),
                );
            }
        } else {
            // Fallback if no static page set
            $response[] = array(
                'id'    => 0,
                'title' => 'Site Homepage',
                'url'   => esc_url(home_url('/')),
            );
        }

        // Add other pages, but skip homepage if already included
        foreach ($pages as $page) {
            if ($homepage_id && $page->ID == $homepage_id) {
                continue;
            }
            $response[] = array(
                'id'    => $page->ID,
                'title' => esc_html($page->post_title),
                'url'   => esc_url(get_permalink($page->ID)),
            );
        }

        return rest_ensure_response($response);
    }

    /**
     * Validates if a post exists.
     *
     * @param int $post_id Post ID to validate.
     * @return bool
     */
    protected function validate_post($post_id)
    {
        return get_post($post_id) !== null;
    }

    /**
     * Returns a standardized "post not found" error.
     *
     * @return WP_Error
     */
    protected function post_not_found_error()
    {
        return new WP_Error(
            'rest_post_not_found',
            __('Page not found.', 'project-huddle'),
            ['status' => 404]
        );
    }

    /**
     * Returns a standardized success response with additional timestamp.
     *
     * @param array $data Data to include in the response.
     * @return WP_REST_Response
     */
    protected function success_response($data)
    {
        $data['timestamp'] = current_time('mysql', 1);
        return new WP_REST_Response($data, 200);
    }

    /**
     * Checks if the current user has permission to edit pages.
     *
     * @return bool|WP_Error
     */
    public function check_edit_permissions()
    {
        return current_user_can('edit_pages') ? true : new WP_Error(
            'rest_forbidden',
            __('You do not have permission to edit pages.', 'project-huddle'),
            ['status' => rest_authorization_required_code()]
        );
    }

    /**
     * Checks if the current user has permission to read.
     *
     * @return bool|WP_Error
     */
    public function check_read_permissions()
    {
        return current_user_can('read') ? true : new WP_Error(
            'rest_forbidden',
            __('You do not have permission to access this endpoint.', 'project-huddle'),
            ['status' => rest_authorization_required_code()]
        );
    }

    /**
     * Returns common REST arguments.
     *
     * @return array
     */
    protected function get_common_args()
    {
        return [
            'version' => [
                'description' => __('API version.', 'project-huddle'),
                'type' => 'integer',
                'default' => self::API_VERSION,
                'sanitize_callback' => 'absint',
            ],
            'page' => [
                'description' => __('Current page of the collection.', 'project-huddle'),
                'type' => 'integer',
                'default' => self::DEFAULT_PAGE,
                'sanitize_callback' => 'absint',
            ],
            'per_page' => [
                'description' => __('Maximum number of items returned.', 'project-huddle'),
                'type' => 'integer',
                'default' => self::DEFAULT_PER_PAGE,
                'sanitize_callback' => 'absint',
            ],
        ];
    }

    /**
     * Returns REST argument for page ID.
     *
     * @return array
     */
    protected function get_page_id_arg()
    {
        return [
            'post_id' => [
                'description' => __('The ID of the page.', 'project-huddle'),
                'type' => 'integer',
                'required' => true,
                'sanitize_callback' => 'absint',
            ],
        ];
    }

    /**
     * Returns REST arguments for disabling a page by key.
     *
     * @return array
     */
    protected function get_disable_args()
    {
        return [
            'pages_key' => [
                'description'       => __('The unique key of the page to disable.', 'project-huddle'),
                'type'              => 'string',
                'required'          => true,
                'validate_callback' => function($param) {
                    return is_string($param) && preg_match('/^[0-9]{4}$/', $param);
                }
            ]
        ];
    }

    /**
     * Returns REST arguments for updating page status.
     *
     * @return array
     */
    protected function get_status_args()
    {
        return $this->get_common_args() + [
            'post_id' => [
                'description' => __('The ID of the page to update.', 'project-huddle'),
                'type' => 'integer',
                'required' => true,
                'sanitize_callback' => 'absint',
            ],
            'enabled' => [
                'description' => __('Whether the page is enabled.', 'project-huddle'),
                'type' => 'boolean',
                'required' => true,
                'sanitize_callback' => 'rest_sanitize_boolean',
            ],
            'permalink' => [
                'description' => __('The permalink of the page.', 'project-huddle'),
                'type' => 'string',
                'required' => true,
                'sanitize_callback' => 'esc_url_raw',
            ],
        ];
    }

    /**
     * Returns REST arguments for updating page access.
     *
     * @return array
     */
    protected function get_access_args()
    {
        return $this->get_common_args() + [
            'post_id' => [
                'description' => __('The ID of the page to toggle access for.', 'project-huddle'),
                'type' => 'integer',
                'required' => true,
                'sanitize_callback' => 'absint',
            ],
            'enable_page_access' => [
                'description' => __('Whether to enable or disable page access.', 'project-huddle'),
                'type' => 'boolean',
                'required' => true,
                'sanitize_callback' => 'rest_sanitize_boolean',
            ],
        ];
    }

    /**
     * Returns REST arguments for website type check.
     *
     * @return array
     */
    protected function get_website_type_args()
    {
        return [
            'post_id' => [
                'description' => __('The ID of the page.', 'project-huddle'),
                'type' => 'integer',
                'required' => true,
                'sanitize_callback' => 'absint',
            ],
        ];
    }
}

add_action('rest_api_init', function () {
    $controller = new PH_Page_Controller();
    $controller->register_routes();
});