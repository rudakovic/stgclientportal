<?php

namespace YayMailAddonWcSubscription\Notices;

use YayMailAddonWcSubscription\SingletonTrait;

defined( 'ABSPATH' ) || exit;

/**
 * NoticeMain Class
 *
 * @method static NoticeMain get_instance()
 */
class NoticeMain {
    use SingletonTrait;

    protected $third_party_name = 'WooCommerce Subscription';
    protected function __construct() {
        add_action(
            'after_plugin_row_' . YAYMAIL_ADDON_WS_BASE_NAME,
            [ $this, 'display_under_plugin_notices' ],
            10,
            2
        );

        add_action( 'admin_footer', [ $this, 'enqueue_admin_script' ] );
    }

    /**
     * Displays the required notices below the plugin row if dependencies are missing.
     *
     * @param string $plugin_file
     */
    public function display_under_plugin_notices( $plugin_file ) {
        if ( defined( 'YAYMAIL_VERSION' ) ) {
            $yaymail_version = YAYMAIL_VERSION;
        } else {
            $yaymail_version = '4.0.0';
        }

        if ( function_exists( 'YayMail\\init' ) && class_exists( 'WC_Subscriptions' ) && version_compare( $yaymail_version, '4.0', '>=' ) ) {
            return;
            // No need to show notices if dependencies are met
        }

        $wp_list_table = _get_list_table( 'WP_MS_Themes_List_Table' );

        echo wp_kses_post(
            '<tr class="plugin-update-tr' . ( is_plugin_active( $plugin_file ) ? ' active' : '' ) . '">
                <td colspan="' . esc_attr( $wp_list_table->get_column_count() ) . '" class="plugin-update colspanchange">'
                . ( ! function_exists( 'YayMail\\init' ) ? $this->get_core_required_notice() : '' )
                . ( ! class_exists( 'WC_Subscriptions' ) ? $this->get_third_party_required_notice() : '' )
                . ( version_compare( $yaymail_version, '4.0', '<' ) ? $this->get_core_update_notice() : '' )
                . '</td>
            </tr>'
        );
    }

    /**
     * Returns the notice to update to new core version.
     */
    protected function get_core_update_notice() {
        return sprintf(
            '<div class="notice inline notice-error notice-alt"><p>%s <a href="%s">%s</a> or <a href="%s">%s</a></p></div>',
            esc_html__( 'To use this addon, you need to update YayMail plugin to version 4.0 or higher. Get', 'yaymail' ),
            esc_url( 'https://wordpress.org/plugins/yaymail/' ),
            esc_html__( 'YayMail Free', 'yaymail' ),
            esc_url( 'https://yaycommerce.com/yaymail-woocommerce-email-customizer/' ),
            esc_html__( 'YayMail Pro', 'yaymail' )
        );
    }


    /**
     * Returns the notice for missing WooCommerce Subscription.
     */
    protected function get_third_party_required_notice() {
        return sprintf(
            '<div class="notice inline notice-warning notice-alt"><p>%s</p></div>',
            sprintf(
                esc_html__( 'In order to customize templates of %1$s, please install %2$s first.', 'yaymail' ),
                '<strong>' . esc_html( $this->third_party_name ) . '</strong>',
                '<strong>' . esc_html( $this->third_party_name ) . '</strong>'
            )
        );
    }

    /**
     * Returns the notice for missing YayMail plugin.
     */
    protected function get_core_required_notice() {
        return sprintf(
            '<div class="notice inline notice-warning notice-alt"><p>%s <a href="%s">%s</a> or <a href="%s">%s</a></p></div>',
            esc_html__( 'To use this addon, you need to install and activate YayMail plugin. Get', 'yaymail' ),
            esc_url( 'https://wordpress.org/plugins/yaymail/' ),
            esc_html__( 'YayMail Free', 'yaymail' ),
            esc_url( 'https://yaycommerce.com/yaymail-woocommerce-email-customizer/' ),
            esc_html__( 'YayMail Pro', 'yaymail' )
        );
    }

    /**
     * Enqueues a script to modify the plugin row styling in the admin footer.
     */
    public function enqueue_admin_script() {
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var pluginRow = document.querySelector('tr[data-plugin="<?php echo esc_js( YAYMAIL_ADDON_WS_BASE_NAME ); ?>"]');
                if (pluginRow) pluginRow.classList.add('update');
            });
        </script>
        <?php
    }
}
