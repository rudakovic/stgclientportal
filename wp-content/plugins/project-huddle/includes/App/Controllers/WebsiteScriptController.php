<?php

namespace PH\Controllers;

use PH\Models\User;
use PH\Models\Visitor;
use PH\Models\Website;

class WebsiteScriptController
{
    protected $user_name = '';
    protected $user_email = '';
    protected $signature = '';
    protected $access_token = '';
    protected $signature_user = null;

    protected $website;

    public function __construct($project_id, $user_name = '', $user_email = '', $signature = '', $access_token = '')
    {
        $this->website = Website::get($project_id);
        $this->user_name = $user_name ?: $this->user_name;
        $this->user_email = $user_email ?: $this->user_email;
        $this->signature = $signature ?: $this->signature;
        $this->access_token = $access_token ?: $this->access_token;

        $this->saveToken();
        $this->signature_user = $this->handleSignature();
    }

    /**
     * Save the access token in the visitors session
     *
     * @return bool
     */
    public function saveToken()
    {
        if ($this->website && $this->access_token) {
            return Visitor::current()->saveToken($this->website, $this->access_token);
        }
        return false;
    }

    /**
     * Handle signature creation/login
     *
     * @return mixed
     */
    public function handleSignature()
    {
        if (!($this->user_email && $this->signature && $this->website->ID && $this->access_token)) {
            return false;
        }

        return User::rest()->create(
            [
                'email' => sanitize_email($this->user_email),
                'username' => sanitize_text_field($this->user_name),
                'access_token' => wp_kses_post($this->access_token),
                'project_id' => $this->website->ID,
            ],
            [
                '_signature' => $this->signature,
            ]
        );
    }

    /**
     * Server data to send to script
     *
     * @return array
     */
    public function getData()
    {
        ob_start();
        ph_get_template('ph-website-iframe.php', '', '', PH_WEBSITE_PLUGIN_DIR . 'templates/');
        $container = ob_get_contents();
        ob_end_clean();

        return [
            "container" => $container,
            "origin" => esc_url_raw(get_site_url()),
            "query_vars" => isset($_GET['ph_query_vars']) ? (bool) $_GET['ph_query_vars'] : false,
            'signature' => isset($_GET['ph_signature']) ? wp_kses_post($_GET['ph_signature']) : '',
        ];
    }

    /**
     * Loads the dynamic script
     *
     * @return string|\WP_Error
     */
    public function load()
    {
        if (!Visitor::current()->canAccess($this->website)) {
            return new \WP_Error('access_denied', 'You are not allowed to access this project', ['status' => rest_authorization_required_code()]);
        }
        // set installed
        $this->website->setInstalled(true);
        // print dynamic script
        return $this->printScript();
    }

    /**
     * Validate a post
     *
     * @param int $post_id
     * @return bool
     */
    protected function validatePost($post_id)
    {
        return get_post_status($post_id) !== false;
    }

    /**
     * Returns a formatted response of enabled pages for a post (formerly project).
     *
     * @param int $post_id
     * @return array
     */
    public function getEnabledPages($post_id)
    {
        $pages_enabled = get_option('pages_enabled', []);

        if (!is_array($pages_enabled)) {
            $pages_enabled = [];
        }

        $enabled_pages = [];
        $unique_posts = [];

        foreach ($pages_enabled as $pages_key => $page_data) {
            if (!is_array($page_data)) {
                continue;
            }

            if (empty($page_data['post_id']) || empty($page_data['permalink'])) {
                continue;
            }

            $current_post_id = (int) $page_data['post_id'];

            if ($current_post_id !== (int) $post_id) {
                continue;
            }

            if (!$this->validatePost($current_post_id)) {
                continue;
            }

            if (!in_array($current_post_id, $unique_posts, true)) {
                $unique_posts[] = $current_post_id;
            }

            $post_title = get_the_title($current_post_id);
            $post_status = get_post_status($current_post_id);

            $enabled_pages[] = [
                'post_id' => $current_post_id,
                'enabled' => true,
                'permalink' => esc_url($page_data['permalink']),
                'pages_key' => sanitize_text_field($pages_key),
                'created_at' => isset($page_data['created_at']) ? sanitize_text_field($page_data['created_at']) : null,
                'post_title' => $post_title !== false ? $post_title : '',
                'post_status' => $post_status !== false ? $post_status : '',
            ];
        }

        return [
            'success' => true,
            'message' => __('Enabled pages fetched successfully.', 'project-huddle'),
            'data' => [
                'post_id' => (int) $post_id,
                'enabled_pages' => $enabled_pages,
            ],
            'status_code' => 200,
        ];
    }

    public function getEnabledStatus($post_id)
    {
        $meta_key = '_page_access_status';
        $meta_value = get_post_meta($post_id, $meta_key, true);
        return $meta_value;
    }

    /**
     * Prints the script output
     *
     * @return string
     */
   /**
 * Prints the script output
 *
 * @return string
 */
public function printScript()
{
    try {
        // Get Enabled Pages Status
        $getEnabledStatus = $this->getEnabledStatus($this->website->ID);

        // Initialize enabled URLs array
        $enabled_urls = [];
        $block_all = false;

        if ($getEnabledStatus == 1) {
            // Fetch enabled pages data only if the status is 1
            $enabled_pages_data = $this->getEnabledPages($this->website->ID);

            // Extract permalinks
            $enabledPermalinks = [];
            if (!empty($enabled_pages_data['data']['enabled_pages'])) {
                foreach ($enabled_pages_data['data']['enabled_pages'] as $page) {
                    $parsed = esc_url($page['permalink']); // Use the full URL here

                    if (!empty($parsed)) {
                        $enabledPermalinks[] = $parsed; // Save the full URL
                    }
                }
            }

            // Create a map of enabled full URLs
            foreach ($enabledPermalinks as $permalink) {
                if (!empty($permalink)) {
                    $enabled_urls[$permalink] = true;
                }
            }

            // Edge Case: Enabled but no pages selected
            if (empty($enabled_urls)) {
                // Block all pages if no pages are selected
                $block_all = true;
            }
        } else {
            // For status 0, allow all pages (no URL filtering)
            $enabled_urls = []; // Empty array signifies no restriction
        }

        ob_start();
        ?>
        // Add refresh token
        <?php if (is_user_logged_in()): ?>
            <?php $refresh_token = User::current()->getRefreshToken(); ?>
            <?php if ($refresh_token): ?>
                localStorage.setItem('ph_authorization', '<?php echo wp_kses_post($refresh_token); ?>');
            <?php endif; ?>
        <?php endif; ?>

        const blockAll = <?php echo $block_all ? 'true' : 'false'; ?>;
        const enabledUrls = <?php echo json_encode($enabled_urls); ?>;

        // Normalize the current URL: strip query parameters and enforce HTTPS
        const currentUrl = window.location.href.split('?')[0].toLowerCase(); // Remove query parameters
        const normalizedUrl = currentUrl.replace(/^http:/, 'https:'); // Enforce HTTPS
        console.log('Current URL:', normalizedUrl);

        if (blockAll) {
            console.log('Script blocked: access is enabled but no pages are selected.');
            // Block script from running on all pages
        } else {
            // Check if the normalized current URL matches any enabled URLs
            const isEnabled = Object.keys(enabledUrls).length === 0 || Object.keys(enabledUrls).some(enabledUrl => {
                return normalizedUrl === enabledUrl.toLowerCase() ||
                    normalizedUrl.startsWith(enabledUrl.toLowerCase() + '/');
            });

            if (isEnabled) {
                var PH_Website = <?php echo json_encode($this->getData()); ?>;

                // Comment scroll
                var queryString = window.location.search;
                var urlParams = new URLSearchParams(queryString);
                var comment_id = urlParams.get("ph_comment");
                PH_Website.comment_scroll = comment_id || 0;

                // Remove query vars
                var parsed = new URL(window.location);
                parsed.search = parsed.search.replace(
                    /&?ph_access_token=([^&]$|[^&]*)/i,
                    ""
                );
                parsed.search = parsed.search.replace(/&?ph_comment=([^&]$|[^&]*)/i, "");
                window.history.replaceState({}, window.title, parsed.toString());

                var head = document.getElementsByTagName('head')[0];
                var cssnode = document.createElement('link');

                PH_Website.isSSO = true;

                // Add CSS
                cssnode.type = 'text/css';
                cssnode.rel = 'stylesheet';
                cssnode.href =
                    '<?php echo esc_url(PH_PLUGIN_URL . 'assets/css/dist/ph-website-comments-parent.css'); ?>?v=<?php echo esc_html(PH_VERSION); ?>';
                head.appendChild(cssnode);

                var css = '<?php echo ph_parent_website_style_options(); ?>',
                    head = document.head || document.getElementsByTagName('head')[0],
                    style = document.createElement('style');

                style.type = 'text/css';
                if (style.styleSheet){
                    style.styleSheet.cssText = css;
                } else {
                    style.appendChild(document.createTextNode(css));
                }

                head.appendChild(style);

                // Need to append this on parent domain
                (function(d, s, id){
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)){ return; }
                    js = d.createElement(s); js.id = id;
                    js.src = "//cdn.jsdelivr.net/npm/html2canvas@1.0.0-rc.5/dist/html2canvas.min.js";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'ph-html2canvas'));

                // Load iframe creator on enabled pages
                <?php
                $file = PH_WEBSITE_PLUGIN_DIR . 'assets/js/ph-iframe-creator.js';
                if (file_exists($file)) {
                    $js_content = file_get_contents($file);
                    if ($js_content !== false) {
                        echo wp_kses($js_content, array(
                            'script' => array(
                                'type' => array(),
                                'src' => array(),
                                'async' => array(),
                                'defer' => array()
                            )
                        ));
                    }
                }
                ?>
            } else {
                console.log(`Script not enabled for ${normalizedUrl}.`);
                // Ensure we don't load the iframe creator for non-enabled pages
            }
        }
        <?php
        do_action('ph_website_script_loaded');
        return ob_get_clean();
    } catch (Exception $e) {
        return '';
    }
}

}