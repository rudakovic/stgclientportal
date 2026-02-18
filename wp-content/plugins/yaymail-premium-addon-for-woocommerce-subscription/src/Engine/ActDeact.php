<?php

namespace YayMailAddonWcSubscription\Engine;

use YayMailAddonWcSubscription\SingletonTrait;
use YayMail\Migrations\MainMigration;
/**
 * Activate and deactive method of the plugin and relates.
 */
class ActDeact {
    use SingletonTrait;

    protected function __construct() {}

    public static function activate() {
        if ( ! function_exists( 'YayMail\\init' ) || version_compare( YAYMAIL_VERSION, '4.0', '<' ) ) {
            return;
        }
        if ( class_exists( 'YayMail\Migrations\AbstractAddonMigrationManager' ) ) {
            \YayMailAddonWcSubscription\Migrations\WcSubscriptionMigration::get_instance();
            MainMigration::get_instance()->migrate();
        }
    }

    public static function deactivate() {
    }
}

// Shortcodes to be replaced
