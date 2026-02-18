<?php

/**
 * SureTriggers
 */
class PH_SureTriggers
{
	public function __construct()
	{
		// Hook into the admin menu action to add your submenu.
		add_action('admin_menu', array($this, 'register_submenu_page'), 100);

		// Add AJAX actions.
        add_action('wp_ajax_ph_install_and_activate', array($this, 'install_and_activate_plugin'));
        add_action('wp_ajax_ph_activate_plugin', array($this, 'activate_plugin'));
		add_action('wp_ajax_check_suretriggers_connection',  array($this, 'check_suretriggers_connection' ));
	}

	/**
     * Verify nonce
     *
     * @param string $nonce_action
     * @return void
     */
    private function verify_nonce($nonce_action) {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], $nonce_action)) {
            wp_send_json_error(__('Invalid nonce. Action not allowed.', 'project-huddle'));
        }
    }

	/**
	 * Check if the user is connected to SureTriggers.
	 */
	public function check_suretriggers_connection() {

		// Validate the nonce
		$this->verify_nonce('ph_suretriggers');
		
		$is_connected = apply_filters('suretriggers_is_user_connected', '');
	
		if ( $is_connected ) {
			wp_send_json_success( array( 'authenticated' => true ) );
		} else {
			wp_send_json_error( array( 'authenticated' => false ) );
		}
	}

	/**
     * Check user permissions
     *
     * @param string $capability
     * @return void
     */
    private function check_permissions($capability) {
        if (!current_user_can($capability)) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'project-huddle'));
        }
    }

	/**
	 * Install and activate the SureTriggers plugin
	 *
	 * @return void
	 */
	public function install_and_activate_plugin() {

		$this->verify_nonce('ph_suretriggers');
		$this->check_permissions('install_plugins');
	
		// Include necessary WordPress plugin installation files.
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	
		// Get plugin information from the WordPress plugin repository.
		$plugin_slug = 'suretriggers';
		$plugin_info = plugins_api('plugin_information', array('slug' => $plugin_slug));
	
		// Suppress output during plugin installation.
		$installed = $this->install_plugin( $plugin_info->download_link );

		wp_cache_flush();

		if (! is_wp_error($installed) && $installed) {
			$plugin_slug = 'suretriggers/suretriggers.php';

			if ( ! is_plugin_active( $plugin_slug ) ) {
				$result = activate_plugin( $plugin_slug );
				delete_transient( 'st-redirect-after-activation' );
	
				if (is_wp_error($result)) {
					wp_send_json_error( __('Could not install OttoKit.', 'project-huddle' ) );
				}
	
				wp_send_json_success( __('Plugin activated successfully.', 'project-huddle' ) );
			}
            wp_send_json_success( __('OttoKit activated.', 'project-huddle' ) );
        } else {
            wp_send_json_error( __('Could not install OttoKit.', 'project-huddle' ) );
        }
	}

	/**
     * Install the plugin file
     *
     * @return void
     */
    public function install_plugin( $link ) {
        wp_cache_flush();

        $upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
        $installed = $upgrader->install($link);

        return $installed;
    }
	

    /**
	 * Activate the plugin
	 *
	 * @return void
	 */
    public function activate_plugin() {

		$this->verify_nonce('ph_suretriggers');
		
		$this->check_permissions('activate_plugins');

        $plugin_slug = 'suretriggers/suretriggers.php';

        if ( ! is_plugin_active( $plugin_slug ) ) {
            $result = activate_plugin( $plugin_slug );
			delete_transient( 'st-redirect-after-activation' );

            if (is_wp_error($result)) {
                wp_send_json_error( $result->get_error_message() );
            }

            wp_send_json_success( __( 'Plugin activated successfully.', 'project-huddle' ) );
        }

        wp_send_json_success( __( 'Plugin is already activated.', 'project-huddle' ) );
    }

	/**
	 * Register the submenu page
	 *
	 * @return void
	 */
	public function register_submenu_page() {
		// Add a submenu page under the ProjectHuddle menu
		$page = add_submenu_page(
			'project-huddle',                          // Parent slug (slug of SureFeedback)
			__('Integrations', 'project-huddle'),       // Page title
			__('Integrations', 'project-huddle'),       // Menu title
			'manage_options',                           // Capability required to access the page
			'ph-integrations',                          // Menu slug
			array($this, 'renderIntegrations')               // Callback function to render the page
		);
		
		// Enqueue assets (scripts, styles) for the submenu page
		add_action( 'admin_print_styles-' . $page, [$this, 'assets'] );
	}

	/**
	 * Check if SureTriggers is installed and activated
	 *
	 * @return string
	 */
	public function suretriggers_installed_and_activated() {
		// Include the plugin.php file if not already included.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_slug = 'suretriggers/suretriggers.php';

		if ( is_plugin_active( $plugin_slug ) ) {
			return 'activated';
		} elseif ( file_exists(WP_PLUGIN_DIR . '/' . $plugin_slug ) ) {
			return 'installed';
		} else {
			return 'not_installed';
		}
	}

	/**
	 * Render the integrations page
	 *
	 * @return void
	 */
	public function renderIntegrations() {

		$status = $this->suretriggers_installed_and_activated();
		$button_label = __( 'Configure Integration', 'project-huddle' );
		$button_class = 'suretriggers-active';

		$is_suretrigger_connected = apply_filters('suretriggers_is_user_connected', '');

		$plugin = 'suretriggers/suretriggers.php';

		if ( 'installed' === $status ) {
			$button_label = __( 'Activate', 'project-huddle' );
			$button_class = 'suretriggers-installed';
		} elseif( 'not_installed' === $status ) {
			$button_label = __( 'Install & Activate', 'project-huddle' );
			$button_class = 'suretriggers-not-installed';
		} elseif( ! $is_suretrigger_connected ) {
			$button_label = __( 'Connect with OttoKit', 'project-huddle' );
			$button_class = 'suretriggers-not-connected';
		}

		$button = '<a href="#" class="button-primary ph-integrations-action ' . $button_class . '">' . esc_html( $button_label ) . '</a>';
		?>
		<div id="ph-integrations-suretriggers">
			<div class="ph-integrations-wrapper" style="display:none;" >
				<div class="ph-integrations-container">
					<div class="ph-integrations-content">
						<h3><?php echo esc_html__('Integration Powered by OttoKit', 'project-huddle'); ?></h3>
						<p><?php echo esc_html__('OttoKit connects with over hundreds of apps, enabling you to automate tasks like sending feedback via email or SMS. With SureFeedback and OttoKit, you can manage any automation needs.', 'project-huddle'); ?></p>
						<ul>
							<li><?php echo esc_html__('✔ Automate Repetitive Tasks', 'project-huddle'); ?></li>
							<li><?php echo esc_html__('✔ Connect with hundreds of apps', 'project-huddle'); ?></li>
							<li><?php echo esc_html__('✔ No code required', 'project-huddle'); ?></li>
							<li><?php echo esc_html__('✔ Save Time & Money', 'project-huddle'); ?></li>
							<li><?php echo esc_html__('✔ Free forever for basic features', 'project-huddle'); ?></li>
							<li><?php echo esc_html__('✔ And more...', 'project-huddle'); ?></li>
						</ul>
						<?php echo $button; ?>
					</div>
					<div class="ph-integrations-icons">
						<img src="<?php echo esc_url(PH_PLUGIN_URL . '/assets/img/suretriggers.png'); ?>" alt="SureTriggers" />
					</div>
				</div>
			</div>
			<?php if( 'suretriggers-active' === $button_class ) { ?>
				<div class="ph-suretriggers-wrap" style="display:none;" >
					<div class="ph-suretriggers-inner-wrap">
						<div class="ph-suretriggers-title">
							<h5><?php echo esc_html__('OttoKit Integration', 'project-huddle'); ?></h5>
						</div>
						<div class="ph-st-modal-separator"></div>
							<div class="ph-suretriggers-embed">
								<script>
									jQuery(document).ready(function($) {
										var embeddedUrl = "<?php echo apply_filters('suretriggers_get_iframe_url', 'https://app.ottokit.com/'); ?>";
											SureTriggers.init({
											"client_id": "projecthuddle",
											"st_embed_url": embeddedUrl,
											"embedded_identifier": "ph",
											"target": "suretriggers-iframe-wrapper",
											"event": {},
											"integration": "ProjectHuddle",
											"summary": "Create new workflow for SureFeedback",
											"selected_options": {},
											"sample_response": {},
											"configure_trigger": true,
											"trigger_allowed_apps": ["ProjectHuddle"],
										});
									});
								</script>
								<div id="suretriggers-iframe-wrapper"></div>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Enqueue assets for the integrations page
	 *
	 * @return void
	 */
	public function assets() {
		$js_dir = PH_PLUGIN_URL . 'assets/js/';
		$css_dir = PH_PLUGIN_URL . 'assets/css/';

		wp_enqueue_script( 'project-huddle-suretriggers-integration', 'https://app.ottokit.com/js/v2/embed.js', [], PH_VERSION, true );

		wp_enqueue_script( 'project-huddle-integrations', $js_dir . 'ph-integrations.js', [], PH_VERSION, true);
		wp_enqueue_style( 'project-huddle-integrations', $css_dir . 'ph-integrations.css', [], PH_VERSION );

		$is_suretrigger_connected = apply_filters('suretriggers_is_user_connected', '');

		wp_localize_script( 'project-huddle-integrations', 'PH_Integrations', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'integration_text' => __( 'Configure Integration', 'project-huddle' ),
			'install_text' => __( 'Install & Activate', 'project-huddle' ),
			'installing_text' => __( 'Installing...', 'project-huddle' ),
			'connecting_text' => __( 'Connecting...', 'project-huddle' ),
			'activate_text' => __( 'Activate', 'project-huddle' ),
			'activating_text' => __( 'Activating...', 'project-huddle' ),
			'connect_text' 		=> __( 'Connect with OttoKit', 'project-huddle' ),
			'connected_text' 		=> __( 'Connected', 'project-huddle' ),
			'integration_url' => admin_url( 'admin.php?page=ph-integrations' ),
			'suretriggers_url' => admin_url( 'admin.php?page=suretriggers' ),
			'is_suretrigger_connected' => $is_suretrigger_connected,
			'nonce' => wp_create_nonce( 'ph_suretriggers' ),
			'activation_failed' => __( 'Activation failed. Please try again.', 'project-huddle' ),
			'installation_failed' => __( 'Installation failed. Please try again.', 'project-huddle' ),
		) );

	}
	
}

// Instantiate the class to add the submenu
new PH_SureTriggers();
