<?php

/**
 * Projects in Admin
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * PH_Admin_Project_Images Class
 *
 * This class handles the metabox for the project images in the admin
 *
 * @since 1.0
 */
class PH_Project_Admin
{

	/**
	 * Custom post type slug
	 *
	 * @since 1.0
	 */
	public $post_type_slug = 'ph-project';

	/**
	 * Setup project admin
	 *
	 * @since 10
	 */
	public function __construct()
	{

		// run only on admin pages
		if (!is_admin()) {
			return;
		}

		// remove project shortlink
		remove_action('wp_head', 'wp_shortlink_wp_head', 10);

		// remove shortlink
		add_filter('pre_get_shortlink', array($this, 'remove_shortlink'), 10, 2);

		// add collaborators ui
		add_action('post_submitbox_start', array($this, 'collaboratorsBox'));

		add_action( 'admin_footer', __CLASS__ . '::show_nps_notice' );

		// add help beacon
		// add_action('admin_footer', array($this, 'beacon'));

		// add help url to menu
		// add_action('admin_menu', array($this, 'help_url'), 9999999);

		// maybe add update notice
		add_action('admin_notices', array($this, 'maybe_add_update_notice'));
	}

	/**
	 * Add helpscout beacon for help
	 */
	public function beacon()
	{
		if (!current_user_can('edit_ph-projects')) {
			return;
		}
		?>
			<!-- FreeScout BEGIN -->
			<script>var FreeScoutW={s:{"color":"#0068BD","position":"br","require":["name","email"],"id":565956024}};(function(d,e,s){if(d.getElementById("freescout-w"))return;a=d.createElement(e);m=d.getElementsByTagName(e)[0];a.async=1;a.id="freescout-w";a.src=s;m.parentNode.insertBefore(a, m)})(document,"script","https://help.brainstormforce.com/modules/chat/js/widget.js?v=2387");</script>
			<!-- FreeScout END -->

			<script>
				jQuery('[href="#show-help"]').click(function(e) {
					if (typeof FreeScoutW === 'undefined') {
						return true
					}

					e.preventDefault();
					FreeScoutW.init();
					var btn = document.getElementById('fsw-btn');
					var isOpen = jQuery( btn ).hasClass( 'ph-open-chat' );
					if ( btn && ! isOpen ) {
						var clickEvent = new MouseEvent('click', {
							bubbles: true,
							cancelable: true,
							view: window
						});
						btn.dispatchEvent(clickEvent);
						jQuery( btn ).addClass('ph-open-chat');
					} else if( btn && isOpen ) {
						FreeScoutW.minimize();
						jQuery( btn ).removeClass( 'ph-open-chat' )
					}
				})
			</script>

			<style>
				#fsw-btn {
					height: 30px !important;
					width: 30px !important;
				}
			</style>
		<?php
	}

	public function maybe_add_update_notice()
	{
		if (isset($_GET['ph_message'])) {
		?>
			<div class="notice notice-warning">
				<p><?php echo esc_html($_GET['ph_message']); ?></p>
			</div>
		<?php
		}
	}

	public function help_url()
	{
		global $submenu;
		if (!current_user_can('manage_options')) {
			return;
		}
		if ( ! ph_licensing()->has_active_valid_license()) {
			$permalink = esc_url(sprintf('admin.php?page=project-huddle-account&ph_message="%s"', __('Please enter a valid license key for help.', 'project-huddle')));
		} else {
			$permalink = '#show-help';
		}
		$submenu['project-huddle'][] = array(
			__('Help', 'project-huddle'),
			'manage_options',
			$permalink,
		);
	}

	/**
	 * Shows collaborators and controls
	 *
	 * @since 1.0
	 */
	// TODO: Maybe separate functionality of collaborators
	public function collaboratorsBox()
	{
		?>
<?php
	}


	/**
	 * Removes wp.me shortlink functionality
	 *
	 * @param $false
	 * @param $post_id
	 *
	 * @return string
	 */
	public function remove_shortlink($false, $post_id)
	{
		return 'ph-project' === get_post_type($post_id) ? '' : $false;
	}

	/**
	 * Render SureFeedback NPS Survey Notice.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public static function show_nps_notice() {

		if ( class_exists( 'Nps_Survey' ) ) {
			\Nps_Survey::show_nps_notice(
				'nps-survey-project-huddle',
				array(
					'show_if'          => true, // Add your display conditions.
					'dismiss_timespan' => 2 * WEEK_IN_SECONDS,
					'display_after'    => 2 * WEEK_IN_SECONDS,
					'plugin_slug'      => 'project-huddle',
					'show_on_screens'  => array( 'toplevel_page_project-huddle' ),
					'message'          => array(
	
						// Step 1 i.e rating input.
						'logo'                  => esc_url( PH_PLUGIN_URL . 'assets/img/project-huddle-icon.png' ),
						'plugin_name'           => __( 'SureFeedback', 'project-huddle' ),
						'nps_rating_message'    => __( 'How likely are you to recommend SureFeedback to your friends or colleagues?', 'project-huddle' ),
	
						// Step 2A i.e. positive.
						'feedback_content'      => __( 'Could you please do us a favor and give us a 5-star rating on WordPress? It would help others choose SureFeedback with confidence. Thank you!', 'project-huddle' ),
						'plugin_rating_link'    => esc_url( 'https://wordpress.org/support/plugin/projecthuddle-child-site/reviews/#new-post' ),
	
						// Step 2B i.e. negative.
						'plugin_rating_title'   => __( 'Thank you for your feedback', 'project-huddle' ),
						'plugin_rating_content' => __( 'We value your input. How can we improve your experience?', 'project-huddle' ),
					),
				)
			);
		}
	}
}
