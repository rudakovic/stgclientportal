<?php

namespace YayMailAddonWcSubscription\Shortcodes;

use YayMail\Abstracts\BaseShortcode;
use YayMail\Utils\Helpers;
use YayMailAddonWcSubscription\SingletonTrait;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerAutoRenewalReminder;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerExpiryReminder;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerManualRenewalReminder;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerProcessingShippingFulfilmentOrder;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerShippingFrequencyNotification;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerSubscriptionPriceUpdated;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerTrialEndingReminder;


/**
 * @method: static ENRSubscriptionShortcodes get_instance()
 */
class ENRSubscriptionShortcodes extends BaseShortcode {

    use SingletonTrait;

    public function __construct() {
        $this->available_email_ids = [
            ENREmailCustomerAutoRenewalReminder::get_instance()->get_id(),
            ENREmailCustomerExpiryReminder::get_instance()->get_id(),
            ENREmailCustomerManualRenewalReminder::get_instance()->get_id(),
            ENREmailCustomerProcessingShippingFulfilmentOrder::get_instance()->get_id(),
            ENREmailCustomerShippingFrequencyNotification::get_instance()->get_id(),
            ENREmailCustomerSubscriptionPriceUpdated::get_instance()->get_id(),
            ENREmailCustomerTrialEndingReminder::get_instance()->get_id(),
        ];

        parent::__construct();
    }

    public function get_shortcodes() {
        $shortcodes   = [];
        $shortcodes[] = [
            'name'        => 'yaymail_enr_subscription_price_changed_details',
            'description' => __( 'Subscription Price Change Details', 'yaymail' ),
            'group'       => 'enr_woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_enr_subscription_price_changed_details' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_enr_subscription_trial_end_details',
            'description' => __( 'Subscription Trial End Details', 'yaymail' ),
            'group'       => 'enr_woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_enr_subscription_trial_end_details' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_enr_subscription_end_details',
            'description' => __( 'Subscription Trial End Details', 'yaymail' ),
            'group'       => 'enr_woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_enr_subscription_end_details' ],
        ];

        return $shortcodes;
    }

    public function yaymail_enr_subscription_end_details( $args ) {

        $render_data = isset( $args['render_data'] ) ? $args['render_data'] : [];

        $is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample data
             */
            $html = yaymail_get_content( 'src/templates/shortcodes/subscription-end-details/sample.php', $args, YAYMAIL_ADDON_WS_PLUGIN_PATH );
            return $html;
        }

        if ( isset( $render_data['subscription'] ) ) {
            $subscription = $render_data['subscription'];
        } else {
            $order = Helpers::get_order_from_shortcode_data( $render_data );
            if ( $order instanceof \WC_Order ) {
                $arr_subscription = wcs_get_subscriptions_for_order( $order->get_id() );
                $subscription     = array_values( $arr_subscription )[0] ?? null;
            }
        }

        if ( empty( $subscription ) && $is_placeholder ) {
            /**
             * Not having subscription
             */
            return esc_html__( 'The subscription end details does not have data in this order.', 'yaymail' );

        }

        if ( empty( $subscription ) && ! $is_placeholder ) {
            /**
             * Not having subscription
             */
            return '';
        }

        $args['render_data']['subscription'] = $subscription;

        $html = yaymail_get_content( 'src/templates/shortcodes/subscription-end-details/main.php', $args, YAYMAIL_ADDON_WS_PLUGIN_PATH );
        return $html;
    }

    public function yaymail_enr_subscription_trial_end_details( $args ) {

        $render_data = isset( $args['render_data'] ) ? $args['render_data'] : [];

        $is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample data
             */
            $html = yaymail_get_content( 'src/templates/shortcodes/subscription-trial-end-details/sample.php', $args, YAYMAIL_ADDON_WS_PLUGIN_PATH );
            return $html;
        }

        $subscription = '';

        if ( isset( $render_data['subscription'] ) ) {
            $subscription = $render_data['subscription'];
        } else {
            $order = Helpers::get_order_from_shortcode_data( $render_data );
            if ( $order instanceof \WC_Order ) {
                $arr_subscription = wcs_get_subscriptions_for_order( $order->get_id() );
                $subscription     = array_values( $arr_subscription )[0] ?? null;
            }
        }

        if ( empty( $subscription ) && $is_placeholder ) {
            /**
             * Not having subscription
             */
            return esc_html__( 'The subscription trial end does not have data in this order.', 'yaymail' );

        }

        if ( empty( $subscription ) && ! $is_placeholder ) {
            /**
             * Not having subscription
             */
            return '';
        }

        $args['render_data']['subscription'] = $subscription;

        $html = yaymail_get_content( 'src/templates/shortcodes/subscription-trial-end-details/main.php', $args, YAYMAIL_ADDON_WS_PLUGIN_PATH );
        return $html;
    }

    public function yaymail_enr_subscription_price_changed_details( $args ) {

        $render_data = isset( $args['render_data'] ) ? $args['render_data'] : [];

        $is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;

        $is_customized_preview = isset( $render_data['is_customized_preview'] ) ? $render_data['is_customized_preview'] : false;

        $is_sample = isset( $render_data['is_sample'] ) ? $render_data['is_sample'] : false;

        $template_path_sample = 'src/templates/shortcodes/subscription-price-changed-details/sample.php';

        if ( $is_sample ) {
            return yaymail_get_content( $template_path_sample, $args, YAYMAIL_ADDON_WS_PLUGIN_PATH );
        }

        $order         = Helpers::get_order_from_shortcode_data( $render_data );
        $subscriptions = ( $order instanceof \WC_Order ) ? wcs_get_subscriptions_for_order( $order->get_id() ) : '';

        $has_subscriptions = ! empty( $subscriptions );

        if ( $is_customized_preview ) {
            return $has_subscriptions ? yaymail_get_content( $template_path_sample, $args, YAYMAIL_ADDON_WS_PLUGIN_PATH ) : '';
        }

        if ( $is_placeholder ) {
            return $has_subscriptions ? yaymail_get_content( $template_path_sample, $args, YAYMAIL_ADDON_WS_PLUGIN_PATH ) : esc_html__( 'The subscription price changed details does not have data in this order.', 'yaymail' );
        }

        // Check data in real order
        $price_changed_items = $render_data['price_changed_items'] ?? null;

        if ( empty( $price_changed_items ) ) {
            /**
             * Not having subscription
             */
            return '';
        }

        $args['render_data']['price_changed_items'] = $price_changed_items;

        $html = yaymail_get_content( 'src/templates/shortcodes/subscription-price-changed-details/main.php', $args, YAYMAIL_ADDON_WS_PLUGIN_PATH );
        return $html;
    }
}
