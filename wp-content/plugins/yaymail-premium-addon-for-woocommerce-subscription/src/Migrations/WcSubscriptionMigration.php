<?php
namespace YayMailAddonWcSubscription\Migrations;

use YayMail\Migrations\AbstractAddonMigrationManager;
use YayMail\Migrations\MigrationHelper;
use YayMailAddonWcSubscription\SingletonTrait;


/**
 * WcSubscriptionMigration Class
 *
 * @method static WcSubscriptionMigration get_instance()
 */
class WcSubscriptionMigration extends AbstractAddonMigrationManager {
    use SingletonTrait;


    protected function __construct() {
        $this->default_legacy_version = '2.9.9';
        $addon_versions               = get_option( 'yaymail_addon_versions' );
        $old_version                  = MigrationHelper::format_version_number( $addon_versions['YayMailAddonWcSubscription'] ?? $this->default_legacy_version );

        parent::__construct( $old_version, YAYMAIL_ADDON_WS_VERSION, 'YayMailAddonWcSubscription', __( 'YayMail Addon For WooCommerce Subscription', 'yaymail' ) );
    }

    protected function declare_migrations() {
        $this->addon_migrations = [
            '4.0' => 'YayMailAddonWcSubscription\Migrations\Versions\Wc_Subscription_Ver_3_0_0',
        ];
    }

    protected function filter_migrations_by_v4_supported_status(): void {
        $this->filter_migrations_by_version_threshold( '4.0' );
    }
}
