<?php

namespace YayMailAddonWcSubscription\Shortcodes;

use YayMail\Abstracts\BaseShortcode;
use YayMail\Utils\TemplateHelpers;
use YayMail\Utils\Helpers;
use YayMailAddonWcSubscription\SingletonTrait;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerAutoRenewalReminder;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerExpiryReminder;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerManualRenewalReminder;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerProcessingShippingFulfilmentOrder;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerShippingFrequencyNotification;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerSubscriptionPriceUpdated;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerTrialEndingReminder;
use YayMailAddonWcSubscription\Emails\WcEmailCancelledSubscription;
use YayMailAddonWcSubscription\Emails\WcEmailCompletedRenewalOrder;
use YayMailAddonWcSubscription\Emails\WcEmailCompletedSwitchOrder;
use YayMailAddonWcSubscription\Emails\WcEmailCustomerPaymentRetry;
use YayMailAddonWcSubscription\Emails\WcEmailCustomerRenewalInvoice;
use YayMailAddonWcSubscription\Emails\WcEmailExpiredSubscription;
use YayMailAddonWcSubscription\Emails\WcEmailNewRenewalOrder;
use YayMailAddonWcSubscription\Emails\WcEmailNewSwitchOrder;
use YayMailAddonWcSubscription\Emails\WcEmailOnHoldRenewalOrder;
use YayMailAddonWcSubscription\Emails\WcEmailOnHoldSubscription;
use YayMailAddonWcSubscription\Emails\WcEmailPaymentRetry;
use YayMailAddonWcSubscription\Emails\WcEmailProcessingRenewalOrder;
use YayMailAddonWcSubscription\Emails\WCSEmailCustomerNotificationSubscriptionExpiration;


/**
 * @method: static WcSubscriptionShortcodes get_instance()
 */
class WcSubscriptionShortcodes extends BaseShortcode {

    use SingletonTrait;

    public function __construct() {
        $this->available_email_ids = [
            WcEmailCancelledSubscription::get_instance()->get_id(),
            WcEmailCompletedRenewalOrder::get_instance()->get_id(),
            WcEmailCompletedSwitchOrder::get_instance()->get_id(),
            WcEmailCustomerPaymentRetry::get_instance()->get_id(),
            WcEmailCustomerRenewalInvoice::get_instance()->get_id(),
            WcEmailExpiredSubscription::get_instance()->get_id(),
            WcEmailNewRenewalOrder::get_instance()->get_id(),
            WcEmailNewSwitchOrder::get_instance()->get_id(),
            WcEmailOnHoldRenewalOrder::get_instance()->get_id(),
            WcEmailOnHoldSubscription::get_instance()->get_id(),
            WcEmailPaymentRetry::get_instance()->get_id(),
            WcEmailProcessingRenewalOrder::get_instance()->get_id(),
            WCSEmailCustomerNotificationSubscriptionExpiration::get_instance()->get_id(),
        ];

        if ( class_exists( 'WC_Subscriptions_Enhancer' ) ) {
            $this->available_email_ids[] = ENREmailCustomerAutoRenewalReminder::get_instance()->get_id();
            $this->available_email_ids[] = ENREmailCustomerExpiryReminder::get_instance()->get_id();
            $this->available_email_ids[] = ENREmailCustomerManualRenewalReminder::get_instance()->get_id();
            $this->available_email_ids[] = ENREmailCustomerProcessingShippingFulfilmentOrder::get_instance()->get_id();
            $this->available_email_ids[] = ENREmailCustomerShippingFrequencyNotification::get_instance()->get_id();
            $this->available_email_ids[] = ENREmailCustomerSubscriptionPriceUpdated::get_instance()->get_id();
            $this->available_email_ids[] = ENREmailCustomerTrialEndingReminder::get_instance()->get_id();
        }

        parent::__construct();
    }

    public function get_shortcodes() {
        $shortcodes = [];

        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_order_id',
            'description' => __( 'Order ID', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_order_id' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_order_details',
            'description' => __( 'Subscription Order Details', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_order_details' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_id',
            'description' => __( 'Subscription ID', 'yaymail' ),
            'attributes'  => [
                'subscription_id' => __( 'Subscription ID', 'yaymail' ),
            ],
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_id' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_id_link',
            'description' => __( 'Subscription ID link', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_id_link' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_id_url',
            'description' => __( 'Subscription ID link', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_id_url' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_cancelled',
            'description' => __( 'Subscription ID link', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_cancelled' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_switch_order_details',
            'description' => __( 'Subscription Switch Order Details', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_switch_order_details' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_expired',
            'description' => __( 'Subscription Expired', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_expired' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_suspended',
            'description' => __( 'Subscription Expired', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_suspended' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_order_url',
            'description' => __( 'Subscription Order Url', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_order_url' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_order_number',
            'description' => __( 'Subscription Order Url', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_order_number' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_next_payment',
            'description' => __( 'Subscription Next Payment', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_next_payment' ],
        ];

        return $shortcodes;
    }

    /**
     * Almost same as core's [yaymail_order_id], but this one also has additional logic to display order link
     */
    public function yaymail_wc_subscription_order_id( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return '1';
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            /**
             * Not having order_id
             */
            return '';
        }

        $sent_to_admin = isset( $render_data['sent_to_admin'] ) ? $render_data['sent_to_admin'] : false;

        /**
         * @reference: wp-content/plugins/woocommerce-subscriptions/vendor/woocommerce/subscriptions-core/templates/emails/email-order-details.php:20
         */
        $link_element_url = ( $sent_to_admin ) ? wcs_get_edit_post_link( wcs_get_objects_property( $order, 'id' ) ) : $order->get_view_order_url();

        $template = ! empty( $data['template'] ) ? $data['template'] : null;

        $text_link_color = ! empty( $template ) ? $template->get_text_link_color() : YAYMAIL_COLOR_WC_DEFAULT;

        $link_style = TemplateHelpers::get_style(
            [
                'color'           => 'heading' === $data['element']['type'] ? 'inherit' : $text_link_color,
                'text-decoration' => 'heading' !== $data['element']['type'] ? 'underline' : 'none',
            ]
        );

        return wp_kses_post( "<a style='$link_style' href='{$link_element_url}'>{$order->get_id()}</a>" );
    }


    public function yaymail_wc_subscription_next_payment( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample data
             */
            return date_i18n( wc_date_format(), strtotime( '+1 day' ) );
        }

        if ( isset( $data['is_placeholder'] ) && $data['is_placeholder'] ) {
            $order = Helpers::get_order_from_shortcode_data( $render_data );

            $arr_subscription = wcs_get_subscriptions_for_order( $order->get_id() );

            $subscription = array_values( $arr_subscription )[0] ?? null;
        } elseif ( isset( $render_data['subscription'] ) || isset( $render_data['order'] ) ) {
                $subscription = $render_data['subscription'] ?? $render_data['order'];
        }

        if ( empty( $subscription ) ) {
            /**
             * Not having subscription
             */
            return '';
        }

        return date_i18n( wc_date_format(), $subscription->get_time( 'next_payment', 'site' ) );
    }

    public function yaymail_wc_subscription_order_number( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample data
             */
            return '1';
        }

        if ( isset( $data['is_placeholder'] ) && $data['is_placeholder'] ) {
            $order = Helpers::get_order_from_shortcode_data( $render_data );

            $arr_subscription = wcs_get_subscriptions_for_order( $order->get_id() );

            $subscription = array_values( $arr_subscription )[0] ?? null;
        } elseif ( isset( $render_data['subscription'] ) || isset( $render_data['order'] ) ) {
                $subscription = $render_data['subscription'] ?? $render_data['order'];
        }

        if ( empty( $subscription ) ) {
            /**
             * Not having subscription
             */
            return '';
        }

        return $subscription->get_order_number();
    }

    public function yaymail_wc_subscription_order_url( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return site_url();
        }

        if ( isset( $data['is_placeholder'] ) && $data['is_placeholder'] ) {
            $order = Helpers::get_order_from_shortcode_data( $render_data );

            $arr_subscription = wcs_get_subscriptions_for_order( $order->get_id() );

            $subscription = array_values( $arr_subscription )[0] ?? null;
        } elseif ( isset( $render_data['subscription'] ) || isset( $render_data['order'] ) ) {
                $subscription = $render_data['subscription'] ?? $render_data['order'];
        }

        if ( empty( $subscription ) ) {
            /**
             * Not having subscription
             */
            return '';
        }

        return $subscription->get_view_order_url();
    }

    public function yaymail_wc_subscription_suspended( $args ) {

        $render_data = isset( $args['render_data'] ) ? $args['render_data'] : [];

        $is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample data
             */
            $html = yaymail_get_content( 'src/templates/shortcodes/subscription-suspended/sample.php', $args, YAYMAIL_ADDON_WS_PLUGIN_PATH );
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
            return esc_html__( 'The subscription suspended does not have data in this order.', 'yaymail' );

        }

        if ( empty( $subscription ) && ! $is_placeholder ) {
            /**
             * Not having subscription
             */
            return '';
        }

        $args['render_data']['subscription'] = $subscription;

        $html = yaymail_get_content( 'src/templates/shortcodes/subscription-suspended/main.php', $args, YAYMAIL_ADDON_WS_PLUGIN_PATH );
        return $html;
    }

    public function yaymail_wc_subscription_expired( $args ) {

        $render_data = isset( $args['render_data'] ) ? $args['render_data'] : [];

        $is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample data
             */
            $html = yaymail_get_content( 'src/templates/shortcodes/subscription-expired/sample.php', $args, YAYMAIL_ADDON_WS_PLUGIN_PATH );
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
            return esc_html__( 'The subscription expired does not have data in this order.', 'yaymail' );

        }

        if ( empty( $subscription ) && ! $is_placeholder ) {
            /**
             * Not having subscription
             */
            return '';
        }

        $args['render_data']['subscription'] = $subscription;

        $html = yaymail_get_content( 'src/templates/shortcodes/subscription-expired/main.php', $args, YAYMAIL_ADDON_WS_PLUGIN_PATH );
        return $html;
    }

    public function yaymail_wc_subscription_switch_order_details( $args ) {

        $render_data = isset( $args['render_data'] ) ? $args['render_data'] : [];

        $is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;

        $is_sample = isset( $render_data['is_sample'] ) ? $render_data['is_sample'] : false;

        if ( $is_sample ) {
            /**
             * Is sample order
             */
            $html = yaymail_get_content( 'src/templates/shortcodes/subscription-order-details/sample.php', $args, YAYMAIL_ADDON_WS_PLUGIN_PATH );
            return $html;
        }

        $subscriptions = '';

        // Check data in real order
        if ( isset( $render_data['subscriptions'] ) ) {
            $subscriptions = $render_data['subscriptions'];
        } else {
            $order = Helpers::get_order_from_shortcode_data( $render_data );
            if ( $order instanceof \WC_Order ) {
                $subscriptions = wcs_get_subscriptions_for_order( $order, [ 'order_type' => [ 'switch' ] ] );
            }
        }

        if ( empty( $subscriptions ) && $is_placeholder ) {
            /**
             * Not having subscription
             */
            return esc_html__( 'The subscription switch does not have data in this order.', 'yaymail' );

        }

        if ( empty( $subscriptions ) && ! $is_placeholder ) {
            /**
             * Not having subscription
             */
            return '';
        }

        $args['render_data']['subscriptions']         = $subscriptions;
        $args['render_data']['is_multi_subscription'] = true;

        $html = yaymail_get_content( 'src/templates/shortcodes/subscription-order-details/main.php', $args, YAYMAIL_ADDON_WS_PLUGIN_PATH );
        return $html;
    }

    public function yaymail_wc_subscription_cancelled( $args ) {

        $render_data = isset( $args['render_data'] ) ? $args['render_data'] : [];

        $is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            $html = yaymail_get_content( 'src/templates/shortcodes/subscription-cancelled/sample.php', $args, YAYMAIL_ADDON_WS_PLUGIN_PATH );
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
            return esc_html__( 'The subscription cancelled does not have data in this order.', 'yaymail' );

        }

        if ( empty( $subscription ) && ! $is_placeholder ) {
            /**
             * Not having subscription
             */
            return '';
        }

        $args['render_data']['subscription'] = $subscription;

        $html = yaymail_get_content( 'src/templates/shortcodes/subscription-cancelled/main.php', $args, YAYMAIL_ADDON_WS_PLUGIN_PATH );
        return $html;
    }

    public function yaymail_wc_subscription_id_url( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return site_url();
        }

        if ( isset( $data['is_placeholder'] ) && $data['is_placeholder'] ) {
            $order = Helpers::get_order_from_shortcode_data( $render_data );

            $arr_subscription = wcs_get_subscriptions_for_order( $order->get_id() );

            $subscription = array_values( $arr_subscription )[0] ?? null;
        } elseif ( isset( $render_data['subscription'] ) || isset( $render_data['order'] ) ) {
                $subscription = $render_data['subscription'] ?? $render_data['order'];
        }

        if ( empty( $subscription ) ) {
            /**
             * Not having subscription
             */
            return '';
        }

        return $subscription->get_edit_order_url();
    }

    public function yaymail_wc_subscription_id_link( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        $template = ! empty( $data['template'] ) ? $data['template'] : null;

        $text_link_color = ! empty( $template ) ? $template->get_text_link_color() : YAYMAIL_COLOR_WC_DEFAULT;

        $link_style = TemplateHelpers::get_style(
            [
                'color'           => 'heading' === $data['element']['type'] ? 'inherit' : $text_link_color,
                'text-decoration' => 'heading' !== $data['element']['type'] ? 'underline' : 'none',
            ]
        );

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return wp_kses_post( "<a style='" . $link_style . "' href='" . esc_url( site_url() ) . "'>" . esc_url( site_url() ) . '</a>' );

        }

        if ( isset( $data['is_placeholder'] ) && $data['is_placeholder'] ) {
            $order = Helpers::get_order_from_shortcode_data( $render_data );

            $arr_subscription = wcs_get_subscriptions_for_order( $order->get_id() );

            $subscription = array_values( $arr_subscription )[0] ?? null;
        } elseif ( isset( $render_data['subscription'] ) || isset( $render_data['order'] ) ) {
            $subscription = $render_data['subscription'] ?? $render_data['order'];
        }

        if ( empty( $subscription ) ) {
            /**
             * Not having subscription
             */
            return '';
        }

        return wp_kses_post( "<a style='$link_style' href='{$subscription->get_edit_order_url()}'> {$subscription->get_id()} </a>" );
    }

    public function yaymail_wc_subscription_id( $data, $shortcode_atts = [] ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        $is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return '1';
        }

        $subscription_id = null;
        if ( ! empty( $shortcode_atts['subscription_id'] ) ) {
            $subscription_id = $shortcode_atts['subscription_id'];
        }

        $sent_to_admin = ! empty( $render_data['sent_to_admin'] );

        $template = ! empty( $data['template'] ) ? $data['template'] : null;

        $text_link_color = ! empty( $template ) ? $template->get_text_link_color() : YAYMAIL_COLOR_WC_DEFAULT;

        $link_style = TemplateHelpers::get_style(
            [
                'color'           => 'heading' === $data['element']['type'] ? 'inherit' : $text_link_color,
                'text-decoration' => 'heading' !== $data['element']['type'] ? 'underline' : 'none',
            ]
        );

        // Get the subscription
        $subscription = null;
        if ( $is_placeholder ) {
            $order = Helpers::get_order_from_shortcode_data( $render_data );

            $arr_subscription = wcs_get_subscriptions_for_order( $order->get_id() );

            $subscription = array_values( $arr_subscription )[0] ?? null;
        } elseif ( isset( $render_data['subscription'] ) || isset( $render_data['order'] ) ) {
            $subscription = $render_data['subscription'] ?? $render_data['order'];
        }
        if ( empty( $subscription ) ) {
            /**
             * Not having subscription
             */
            return '';
        }
        $url = $subscription->get_view_order_url();
        if ( $sent_to_admin ) {
            $url = $subscription->get_edit_order_url();
        }

        $subscription_id = $subscription_id ?? $subscription->get_id();

        return wp_kses_post( "<a style='$link_style' href='{$url}'>{$subscription_id}</a>" );
    }

    public function yaymail_wc_subscription_order_details( $args ) {

        $render_data = isset( $args['render_data'] ) ? $args['render_data'] : [];

        $is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;

        $is_customized_preview = isset( $render_data['is_customized_preview'] ) ? $render_data['is_customized_preview'] : false;

        $is_sample = isset( $render_data['is_sample'] ) ? $render_data['is_sample'] : false;

        if ( $is_sample ) {
            /**
             * Is sample order
             */
            $html = yaymail_get_content( 'src/templates/shortcodes/subscription-order-details/sample.php', $args, YAYMAIL_ADDON_WS_PLUGIN_PATH );
            return $html;
        }

        $subscription = '';

        // Check data in real order
        if ( isset( $render_data['subscription'] ) || isset( $render_data['order'] ) && ! $is_customized_preview && ! $is_placeholder ) {
            $subscription = $render_data['subscription'] ?? $render_data['order'];
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
            return esc_html__( 'The subscription does not have data in this order.', 'yaymail' );

        }

        if ( empty( $subscription ) && ! $is_placeholder ) {
            /**
             * Not having subscription
             */
            return '';
        }

        $args['render_data']['order'] = $subscription;

        $html = yaymail_get_content( 'src/templates/shortcodes/subscription-order-details/main.php', $args, YAYMAIL_ADDON_WS_PLUGIN_PATH );
        return $html;
    }
}
