<?php

namespace YayMailAddonWcSubscription\Shortcodes;

use YayMail\Abstracts\BaseShortcode;
use YayMail\Utils\Helpers;
use YayMailAddonWcSubscription\SingletonTrait;

/**
 * @method: static ENREmailCustomerShippingFrequencyNotificationShortcodes get_instance()
 */
class ENREmailCustomerShippingFrequencyNotificationShortcodes extends BaseShortcode {

    use SingletonTrait;

    public function __construct() {
        $this->available_email_ids = [
            ENR_PREFIX . 'customer_shipping_frequency_notification',
        ];

        parent::__construct();
    }

    public function get_shortcodes() {
        $shortcodes   = [];
        $shortcodes[] = [
            'name'        => 'yaymail_enr_subscription_shipping_cycle_string',
            'description' => __( 'Subscription Switch Order Details', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_enr_subscription_shipping_cycle_string' ],
        ];

        return $shortcodes;
    }

    public function yaymail_enr_subscription_shipping_cycle_string( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return __( 'shipping', 'yaymail' );
        }

        if ( isset( $data['is_placeholder'] ) && $data['is_placeholder'] ) {
            $order         = Helpers::get_order_from_shortcode_data( $render_data );
            $subscriptions = wcs_get_subscriptions_for_order( $order->get_id() );
            $subscription  = array_values( $subscriptions )[0] ?? null;
        } else {
            $subscription = $render_data['subscription'] ?? null;
        }

        if ( empty( $subscription ) ) {
            /**
             * Not having subscription
             */
            return '';
        }

        $shipping_cycle_string = _enr_get_shipping_frequency_string(
            [
                'is_synced'      => _enr_is_shipping_frequency_synced( $subscription ),
                'interval'       => $subscription->get_meta( ENR_PREFIX . 'shipping_period_interval' ),
                'period'         => $subscription->get_meta( ENR_PREFIX . 'shipping_period' ),
                'sync_date_day'  => $subscription->get_meta( ENR_PREFIX . 'shipping_frequency_sync_date_day' ),
                'sync_date_week' => $subscription->get_meta( ENR_PREFIX . 'shipping_frequency_sync_date_week' ),
            ]
        );

        return $shipping_cycle_string;
    }
}
