<?php

namespace YayMailAddonWcSubscription\Shortcodes;

use YayMail\Abstracts\BaseShortcode;
use YayMailAddonWcSubscription\SingletonTrait;

/**
 * @method: static WcSubscriptionInformationShortcodes get_instance()
 */
class WcSubscriptionInformationShortcodes extends BaseShortcode {

    use SingletonTrait;

    public function get_shortcodes() {
        $shortcodes   = [];
        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_information',
            'description' => __( 'Subscription Information', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_information' ],
        ];

        return $shortcodes;
    }

    public function yaymail_wc_subscription_information( $args ) {

        $render_data = isset( $args['render_data'] ) ? $args['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            $html = yaymail_get_content( 'src/templates/shortcodes/subscription-information/sample.php', $args, YAYMAIL_ADDON_WS_PLUGIN_PATH );
            return $html;
        }

        if ( empty( $render_data['order'] ) ) {
            /**
             * Not having subscription
             */
            return '';
        }

        $html = yaymail_get_content( 'src/templates/shortcodes/subscription-information/main.php', $args, YAYMAIL_ADDON_WS_PLUGIN_PATH );
        return $html;
    }
}
