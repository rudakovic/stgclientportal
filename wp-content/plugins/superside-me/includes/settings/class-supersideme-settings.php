<?php
/**
 *
 * Settings page for SuperSide Me.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2020 Robin Cornett
 * @license   GPL-2.0+
 */

class SuperSide_Me_Settings extends SuperSide_Me_Helper {

	/**
	 * variable set for featured image option
	 * @var $page
	 */
	protected $page = 'supersideme';

	/**
	 * All settings fields
	 * @var $fields array
	 */
	protected $fields;

	/**
	 * Nonce action name
	 * @var string $action
	 */
	protected $action = 'supersideme_save-settings';

	/**
	 * Nonce name
	 * @var string $nonce
	 */
	protected $nonce = 'supersideme_nonce';

	/**
	 * add a submenu page under Appearance
	 * @since 1.0.0
	 */
	public function do_submenu_page() {

		add_action( 'admin_init', array( $this, 'register_settings' ) );

		if ( ! supersideme_do_settings_page() ) {
			return;
		}

		add_theme_page(
			__( 'SuperSide Me', 'superside-me' ),
			__( 'SuperSide Me', 'superside-me' ),
			'manage_options',
			$this->page,
			array( $this, 'do_settings_form' )
		);
		add_action( "load-appearance_page_{$this->page}", array( $this, 'build_settings_page' ) );
	}

	/**
	 * Define and build the SuperSide Me settings page.
	 * @since 2.3.0
	 */
	public function build_settings_page() {
		$definitions  = new SuperSideMeDefineSettings();
		$sections     = $definitions->register_sections();
		$this->fields = $definitions->register_fields();
		$this->add_sections( $sections );
		$this->add_fields( $this->fields, $sections );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ), 20 );
	}

	/**
	 * create settings form
	 *
	 * @since  1.0.0
	 */
	public function do_settings_form() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php echo esc_attr( get_admin_page_title() ); ?></h1>
			<?php
			$this->get_customizer_link();
			$this->do_tabs();
			?>
			<form action="options.php" method="post">
				<?php
				$active_tab = $this->get_active_tab();
				$sections   = 'supersideme_' . $active_tab;
				$fields     = 'supersideme';
				if ( ( ! is_multisite() || is_main_site() ) && 'licensing' === $active_tab ) {
					$sections     = 'supersideme_licensing';
					$fields       = 'supersideme_licensing';
					$this->action = 'superside_license_nonce';
					$this->nonce  = 'superside_license_nonce';
				}
				do_settings_sections( $sections );
				settings_fields( $fields );
				wp_nonce_field( $this->action, $this->nonce );
				if ( 'licensing' === $active_tab ) {
					$license = get_option( 'supersidemelicense_key', '' );
					$status  = get_option( 'supersidemelicense_status', false );
					if ( ! $license || 'valid' !== $status ) {
						submit_button( __( 'Activate License', 'superside-me' ), 'primary', 'supersideme_activate', true, null );
					}
				} else {
					submit_button();
				}
				settings_errors();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Output the link to manage SuperSide Me options in the Customizer.
	 * @since 2.5.0
	 */
	private function get_customizer_link() {
		if ( apply_filters( 'supersideme_disable_settings_page', false ) || apply_filters( 'supersideme_disable_customizer_panel', false ) ) {
			return;
		}
		printf(
			' <a class="page-title-action hide-if-no-customize" href="%1$s">%2$s</a>',
			esc_url(
				add_query_arg(
					array(
						array( 'autofocus' => array( 'panel' => 'supersideme' ) ),
						'return' => rawurlencode( remove_query_arg( wp_removable_query_args(), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ),
					),
					admin_url( 'customize.php' )
				)
			),
			esc_html__( 'Manage with Live Preview', 'superside-me' )
		);
	}

	/**
	 * Load enqueue class for custom scripts/styles.
	 *
	 * @since 2.4.0
	 */
	public function enqueue() {
		$tab = $this->get_active_tab();
		if ( ! in_array( $tab, array( 'custom', 'appearance' ), true ) ) {
			return;
		}
		include_once plugin_dir_path( __FILE__ ) . '/class-supersideme-settings-enqueue.php';
		$enqueue = new SuperSideMeSettingsEnqueue( $tab );
		$enqueue->add_color_picker();
		$enqueue->enqueue_fontawesome();
		$enqueue->enqueue_custom_buttons_assets();
		$enqueue->dequeue_conflicts();
	}

	/**
	 * Output tabs. All tabs will be output if it's a single site or the main site, and the settings page is not
	 * disabled.

	 * @since 2.0.0
	 */
	protected function do_tabs() {
		$tabs = array();
		if ( ! apply_filters( 'supersideme_disable_settings_page', false ) ) {
			$tabs = $this->register_tabs();
		}
		if ( ! is_multisite() || is_main_site() ) {
			$tabs[] = array(
				'id'    => 'licensing',
				'label' => __( 'License', 'superside-me' ),
			);
		}
		if ( ! $tabs ) {
			return;
		}
		$active_tab = $this->get_active_tab();
		echo '<div class="nav-tab-wrapper">';
		printf( '<h2 id="settings-tabs" class="screen-reader-text">%s</h2>', __( 'Settings Tabs', 'superside-me' ) );
		echo '<ul>';
		foreach ( $tabs as $tab ) {
			$class = 'nav-tab';
			if ( $active_tab === $tab['id'] ) {
				$class .= ' nav-tab-active';
				printf(
					'<li class="%s">%s</li>',
					esc_attr( $class ),
					esc_html( $tab['label'] )
				);
				continue;
			}
			$query = add_query_arg(
				array(
					'page' => $this->page,
					'tab'  => $tab['id'],
				),
				'themes.php'
			);
			printf(
				'<li><a href="%s" class="%s">%s</a></li>',
				esc_url( $query ),
				esc_attr( $class ),
				esc_attr( $tab['label'] )
			);
		}
		echo '</ul>';
		echo '</div>';
	}

	/**
	 * Register the settings tabs.
	 *
	 * @since 2.4.0
	 * @return array
	 */
	protected function register_tabs() {
		return array(
			array(
				'id'    => 'main',
				'label' => __( 'General', 'superside-me' ),
			),
			array(
				'id'    => 'appearance',
				'label' => __( 'Appearance', 'superside-me' ),
			),
			array(
				'id'    => 'buttons',
				'label' => __( 'Buttons', 'superside-me' ),
			),
			array(
				'id'    => 'optional',
				'label' => __( 'Options', 'superside-me' ),
			),
			array(
				'id'    => 'menus',
				'label' => __( 'Menus', 'superside-me' ),
			),
			array(
				'id'    => 'custom',
				'label' => __( 'Menu Bar', 'superside-me' ),
			),
		);
	}

	/**
	 * Register plugin settings
	 *
	 * @since 1.5.1
	 */
	public function register_settings() {
		register_setting( 'supersideme', 'supersideme', array( $this, 'do_validation_things' ) );
	}

	/**
	 * validate all inputs
	 *
	 * @param  array $new_value various settings
	 *
	 * @return array            number or URL
	 *
	 * @since  1.0.0
	 */
	public function do_validation_things( $new_value = array() ) {

		// If the user doesn't have permission to save, then display an error message
		if ( ! $this->user_can_save( $this->action, $this->nonce ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'superside-me' ) );
		}

		if ( empty( $_POST['_wp_http_referer'] ) ) {
			return $this->setting;
		}

		check_admin_referer( $this->action, $this->nonce );

		include_once plugin_dir_path( __FILE__ ) . 'class-supersideme-settings-sanitize.php';

		$sanitize = new SuperSide_Me_Settings_Sanitize( $this->setting, $this->page );

		return $sanitize->sanitize( $new_value );
	}
}
