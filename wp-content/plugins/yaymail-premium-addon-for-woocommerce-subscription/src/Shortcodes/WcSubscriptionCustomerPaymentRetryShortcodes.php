<?php

namespace YayMailAddonWcSubscription\Shortcodes;

use YayMail\Abstracts\BaseShortcode;
use YayMailAddonWcSubscription\SingletonTrait;


/**
 * @method: static WcSubscriptionCustomerPaymentRetryShortcodes get_instance()
 */
class WcSubscriptionCustomerPaymentRetryShortcodes extends BaseShortcode {

    use SingletonTrait;

    public function __construct() {
        $this->available_email_ids = [
            'customer_payment_retry',
            'payment_retry',
        ];
        parent::__construct();
    }

    public function get_shortcodes() {
        $shortcodes   = [];
        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_get_human_time_diff',
            'description' => __( 'Subscription Get Human Time Diff', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_get_human_time_diff' ],
        ];

        return $shortcodes;
    }

    public function yaymail_wc_subscription_get_human_time_diff( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return wcs_get_human_time_diff( time() );
        }

        $retry = $render_data['retry'] ?? null;

        if ( empty( $retry ) ) {
            /**
             * Not having subscription
             */
            return '';
        }

        return wcs_get_human_time_diff( $retry->get_time() );
    }

}
