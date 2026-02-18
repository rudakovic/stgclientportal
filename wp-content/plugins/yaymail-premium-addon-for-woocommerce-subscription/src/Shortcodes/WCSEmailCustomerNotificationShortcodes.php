<?php

namespace YayMailAddonWcSubscription\Shortcodes;

use YayMail\Abstracts\BaseShortcode;
use YayMailAddonWcSubscription\SingletonTrait;

use YayMailAddonWcSubscription\Emails\WCSEmailCustomerNotificationAutoRenewal;
use YayMailAddonWcSubscription\Emails\WCSEmailCustomerNotificationManualRenewal;
use YayMailAddonWcSubscription\Emails\WCSEmailCustomerNotificationSubscriptionExpiration;
use YayMailAddonWcSubscription\Emails\WCSEmailCustomerNotificationAutoTrialExpiration;
use YayMailAddonWcSubscription\Emails\WCSEmailCustomerNotificationManualTrialExpiration;


/**
 * @method: static WCSEmailCustomerNotificationShortcodes get_instance()
 */
class WCSEmailCustomerNotificationShortcodes extends BaseShortcode {

    use SingletonTrait;

    public function __construct() {
        $this->available_email_ids = [
            WCSEmailCustomerNotificationAutoRenewal::get_instance()->get_id(),
            WCSEmailCustomerNotificationManualRenewal::get_instance()->get_id(),
            WCSEmailCustomerNotificationSubscriptionExpiration::get_instance()->get_id(),
            WCSEmailCustomerNotificationAutoTrialExpiration::get_instance()->get_id(),
            WCSEmailCustomerNotificationManualTrialExpiration::get_instance()->get_id(),
        ];
        parent::__construct();
    }

    public function get_shortcodes() {
        $shortcodes   = [];
        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_time_til_event',
            'description' => __( 'Subscription Time Til Event', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_time_til_event' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_event_date',
            'description' => __( 'Subscription Event Date', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_event_date' ],
        ];

        return $shortcodes;
    }

    public function yaymail_wc_subscription_event_date( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return gmdate( 'Y-m-d' );
        }

        if ( empty( $render_data['subscription_event_date'] ) ) {
            /**
             * Not having subscription
             */
            return '';
        }

        return $render_data['subscription_event_date'];
    }

    public function yaymail_wc_subscription_time_til_event( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return wcs_get_human_time_diff( time() );
        }

        if ( empty( $render_data['subscription_time_til_event'] ) ) {
            /**
             * Not having subscription
             */
            return '';
        }

        return $render_data['subscription_time_til_event'];
    }

}
