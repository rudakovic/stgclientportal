<?php
namespace YayMailAddonWcSubscription;

/**
 * License for addon
 */
class License {
    use SingletonTrait;

    public function __construct() {
        add_filter( 'yaymail_available_licensing_plugins', [ $this, 'yaymail_addon_license' ] );
    }

    public static function yaymail_addon_license( $plugins ) {
        $plugin_data    = [
            'Name'       => 'YayMail Addon for WooCommerce Subscriptions',
            'Version'    => YAYMAIL_ADDON_WS_VERSION,
            'AuthorName' => 'YayCommerce',
        ];
        $plugin_version = $plugin_data['Version'];
        $plugin_name    = $plugin_data['Name'];
        $plugin_slug    = strtolower( str_replace( ' ', '_', $plugin_name ) );
        $plugins[]      = [
            'name'     => $plugin_name,
            'slug'     => $plugin_slug,
            'dir_path' => YAYMAIL_ADDON_WS_PLUGIN_PATH,
            'basename' => YAYMAIL_ADDON_WS_BASE_NAME,
            'url'      => YAYMAIL_ADDON_WS_PLUGIN_URL,
            'file'     => YAYMAIL_ADDON_WS_PLUGIN_FILE,
            'version'  => $plugin_version,
            'item_id'  => '6795',
        ];
        return $plugins;
    }
}
