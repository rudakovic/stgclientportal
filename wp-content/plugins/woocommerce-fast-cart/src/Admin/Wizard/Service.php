<?php
/**
 * Undocumented script.
 *
 * @package   Barn2\password-protected-categories
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */

namespace Barn2\Plugin\WC_Fast_Cart\Admin\Wizard;

use Barn2\Plugin\WC_Fast_Cart\Dependencies\Lib\Plugin\License\EDD_Licensing;
use Barn2\Plugin\WC_Fast_Cart\Dependencies\Lib\Plugin\License\Plugin_License;
use Barn2\Plugin\WC_Fast_Cart\Dependencies\Lib\Plugin\Licensed_Plugin;
use Barn2\Plugin\WC_Fast_Cart\Dependencies\Lib\Registerable;
use Barn2\Plugin\WC_Fast_Cart\Dependencies\Lib\Service\Standard_Service;
use Barn2\Plugin\WC_Fast_Cart\Dependencies\Lib\Service\Service_Container;

use Barn2\Plugin\WC_Fast_Cart\Admin\Wizard\Steps\Welcome;
use Barn2\Plugin\WC_Fast_Cart\Admin\Wizard\Steps\General;
use Barn2\Plugin\WC_Fast_Cart\Admin\Wizard\Steps\Features;
use Barn2\Plugin\WC_Fast_Cart\Admin\Wizard\Steps\Cart;
use Barn2\Plugin\WC_Fast_Cart\Admin\Wizard\Steps\Pages;
use Barn2\Plugin\WC_Fast_Cart\Admin\Wizard\Steps\Cross_Selling;
use Barn2\Plugin\WC_Fast_Cart\Admin\Wizard\Steps\Complete;
use Barn2\Plugin\WC_Fast_Cart\Dependencies\Setup_Wizard\Setup_Wizard;
use Barn2\Plugin\WC_Fast_Cart\Dependencies\Setup_Wizard\Util;

/**
 * Undocumented class
 */
class Service implements Registerable, Standard_Service {

	use Service_Container;

	/**
	 * Plugin instance
	 *
	 * @var Licensed_Plugin
	 */
	private $plugin;

	/**
	 * Wizard instance
	 *
	 * @var Setup_Wizard
	 */
	private $wizard;

	/**
	 * Setup the setup wizard. Pun intended.
	 *
	 * @param Licensed_Plugin $plugin
	 */
	public function __construct( Licensed_Plugin $plugin ) {

		$this->plugin = $plugin;

		$valid_license = $plugin->get_license()->is_valid();

		$steps = [
			new Welcome(),
			new General(),
			new Features(),
			new Cart(),
			new Pages(),
			new Cross_Selling(),
			new Complete(),
		];

		$wizard = new Setup_Wizard( $this->plugin, $steps );

		$wizard->configure(
			[
				'skip_url'        => admin_url( 'admin.php?page=wc-settings&tab=fast-cart&skip-setup' ),
				'license_tooltip' => esc_html__( 'The license key is contained in your order confirmation email.', 'wc-fast-cart' ),
				'utm_id'          => 'wfc',
			]
		);

		$wizard->add_edd_api( EDD_Licensing::class );
		$wizard->add_license_class( Plugin_License::class );
		$wizard->add_restart_link( 'wc-settings', 'woocommerce-fast-cart' );

		$wizard->add_custom_asset(
			$plugin->get_dir_url() . 'assets/js/admin/wfc-setup.js',
			Util::get_script_dependencies( $this->plugin, './assets/js/admin/wfc-setup.js' )
		);

		$this->wizard = $wizard;
	}

	/**
	 * Boot the wizard.
	 *
	 * @return void
	 */
	public function register() {
		$this->wizard->boot();
	}
}
