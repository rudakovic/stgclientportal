<?php

namespace YayMailAddonWcSubscription\Shortcodes;

use YayMail\Abstracts\BaseShortcode;
use YayMail\Utils\Helpers;
use YayMailAddonWcSubscription\SingletonTrait;

/**
 * @method: static WcSubscriptionSwitchedShortcodes get_instance()
 */
class WcSubscriptionSwitchedShortcodes extends BaseShortcode {

    use SingletonTrait;

    public $available_email_ids = [
        'new_switch_order',
    ];

    public function get_shortcodes() {
        $shortcodes   = [];
        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_switched_count',
            'description' => __( 'Subscription Switch Order Details', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_switched_count' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_switched_email_text',
            'description' => __( 'Subscription Switch Order Details', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_switched_email_text' ],
        ];

        return $shortcodes;
    }

    public function yaymail_wc_subscription_switched_count( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return '1';
        }

        $subscriptions = [];

        if ( isset( $data['is_placeholder'] ) && $data['is_placeholder'] ) {
            $order         = Helpers::get_order_from_shortcode_data( $render_data );
            $subscriptions = wcs_get_subscriptions_for_order( $order->get_id() );
        } elseif ( isset( $render_data['subscriptions'] ) || isset( $render_data['order'] ) ) {
            $subscriptions = $render_data['subscriptions'] ?? $render_data['order'];
        }

        if ( empty( $subscriptions ) ) {
            /**
             * Not having subscription
             */
            return '';
        }

        return count( (array) $subscriptions );
    }

    /**
     * Get the email text for subscription switched notification
     *
     * @param array $data Data passed to shortcode
     * @return string Formatted email text with customer name and number of switched subscriptions
     */
    public function yaymail_wc_subscription_switched_email_text( $data ) {
        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) || empty( $render_data['subscriptions'] ) ) {
            $switched_count = 1;
        } else {
            $switched_count = count( $render_data['subscriptions'] );
        }

        return sprintf(
            // translators: customer name, number of subscriptions switched.
            _nx(
                'Customer %1$s has switched their subscription. The details of their new subscription are as follows:',
                'Customer %1$s has switched %2$d of their subscriptions. The details of their new subscriptions are as follows:',
                $switched_count,
                'Used in switch notification admin email',
                'woocommerce-subscriptions'
            ),
            do_shortcode( '[yaymail_billing_first_name]' ) . ' ' . do_shortcode( '[yaymail_billing_last_name]' ),
            $switched_count
        );
    }
}
