<?php

namespace YayMailAddonWcSubscription\Controllers;

use YayMailAddonWcSubscription\SingletonTrait;
use YayMailAddonWcSubscription\Elements\ElementsLoader;
use YayMailAddonWcSubscription\Migrations\WcSubscriptionMigration;

defined( 'ABSPATH' ) || exit;

/**
 * AddonController Class
 *
 * @method static AddonController get_instance()
 */
class AddonController {

    use SingletonTrait;

    private $emails = [];

    protected function __construct() {

        WcSubscriptionMigration::get_instance();

        ElementsLoader::get_instance();

        add_action( 'yaymail_register_emails', [ $this, 'register_emails' ] );

        add_action( 'yaymail_register_shortcodes', [ $this, 'yaymail_addon_shortcode_defined' ] );

        add_filter( 'yaymail_template_rendering_args', [ $this, 'pass_order_to_template_args' ], 10, 2 );

        add_filter( 'yaymail_order_for_language', [ $this, 'filter_order_for_language' ], 10, 2 );
    }

    public function filter_order_for_language( $order, $data ) {
        $order_from_data = self::get_order_from_data( $data );
        if ( $order_from_data ) {
            return $order_from_data;
        }
        return $order;
    }

    public function pass_order_to_template_args( $args, $template_name ) {
        foreach ( $this->emails as $email ) {
            if ( $email->get_id() === $template_name ) {
                $order_from_data = self::get_order_from_data( $args['render_data'] ?? [] );
                if ( $order_from_data ) {
                    $args['render_data']['order'] = $order_from_data;
                }
                break;
            }
        }
        return $args;
    }

    public static function get_order_from_data( $data ) {
        if ( isset( $data['subscription'] ) && method_exists( $data['subscription'], 'get_parent' ) ) {
            $order = $data['subscription']->get_parent();
            if ( $order && is_a( $order, '\WC_Order' ) ) {
                return $order;
            }
        }

        return null;
    }

    public function yaymail_addon_shortcode_defined( $shortcode_service ) {
        \YayMailAddonWcSubscription\Shortcodes\WcSubscriptionShortcodes::get_instance();
        \YayMailAddonWcSubscription\Shortcodes\WcSubscriptionInformationShortcodes::get_instance();
        \YayMailAddonWcSubscription\Shortcodes\WcSubscriptionSwitchedShortcodes::get_instance();
        \YayMailAddonWcSubscription\Shortcodes\WcSubscriptionCustomerRenewalInvoiceShortcodes::get_instance();
        \YayMailAddonWcSubscription\Shortcodes\WcSubscriptionCustomerPaymentRetryShortcodes::get_instance();
        \YayMailAddonWcSubscription\Shortcodes\WCSEmailCustomerNotificationShortcodes::get_instance();

        if ( class_exists( 'WC_Subscriptions_Enhancer' ) ) {
            \YayMailAddonWcSubscription\Shortcodes\ENREmailCustomerShippingFrequencyNotificationShortcodes::get_instance();
            \YayMailAddonWcSubscription\Shortcodes\ENRSubscriptionShortcodes::get_instance();
        }
    }

    public function register_emails( $yaymail_emails ) {
        $this->emails[] = \YayMailAddonWcSubscription\Emails\WcEmailNewRenewalOrder::get_instance();
        $this->emails[] = \YayMailAddonWcSubscription\Emails\WcEmailCancelledSubscription::get_instance();
        $this->emails[] = \YayMailAddonWcSubscription\Emails\WcEmailCompletedSwitchOrder::get_instance();
        $this->emails[] = \YayMailAddonWcSubscription\Emails\WcEmailNewSwitchOrder::get_instance();
        $this->emails[] = \YayMailAddonWcSubscription\Emails\WcEmailProcessingRenewalOrder::get_instance();
        $this->emails[] = \YayMailAddonWcSubscription\Emails\WcEmailCompletedRenewalOrder::get_instance();
        $this->emails[] = \YayMailAddonWcSubscription\Emails\WcEmailOnHoldRenewalOrder::get_instance();
        $this->emails[] = \YayMailAddonWcSubscription\Emails\WcEmailCustomerRenewalInvoice::get_instance();
        $this->emails[] = \YayMailAddonWcSubscription\Emails\WcEmailExpiredSubscription::get_instance();
        $this->emails[] = \YayMailAddonWcSubscription\Emails\WcEmailOnHoldSubscription::get_instance();
        $this->emails[] = \YayMailAddonWcSubscription\Emails\WcEmailCustomerPaymentRetry::get_instance();
        $this->emails[] = \YayMailAddonWcSubscription\Emails\WcEmailPaymentRetry::get_instance();
        $this->emails[] = \YayMailAddonWcSubscription\Emails\WCSEmailCustomerNotificationAutoRenewal::get_instance();
        $this->emails[] = \YayMailAddonWcSubscription\Emails\WCSEmailCustomerNotificationAutoTrialExpiration::get_instance();
        $this->emails[] = \YayMailAddonWcSubscription\Emails\WCSEmailCustomerNotificationManualRenewal::get_instance();
        $this->emails[] = \YayMailAddonWcSubscription\Emails\WCSEmailCustomerNotificationManualTrialExpiration::get_instance();
        $this->emails[] = \YayMailAddonWcSubscription\Emails\WCSEmailCustomerNotificationSubscriptionExpiration::get_instance();

        if ( class_exists( 'WC_Subscriptions_Enhancer' ) ) {
            $this->emails[] = \YayMailAddonWcSubscription\Emails\ENREmailCustomerProcessingShippingFulfilmentOrder::get_instance();
            $this->emails[] = \YayMailAddonWcSubscription\Emails\ENREmailCustomerShippingFrequencyNotification::get_instance();
            $this->emails[] = \YayMailAddonWcSubscription\Emails\ENREmailCustomerSubscriptionPriceUpdated::get_instance();
            $this->emails[] = \YayMailAddonWcSubscription\Emails\ENREmailCustomerTrialEndingReminder::get_instance();
            $this->emails[] = \YayMailAddonWcSubscription\Emails\ENREmailCustomerAutoRenewalReminder::get_instance();
            $this->emails[] = \YayMailAddonWcSubscription\Emails\ENREmailCustomerManualRenewalReminder::get_instance();
            $this->emails[] = \YayMailAddonWcSubscription\Emails\ENREmailCustomerExpiryReminder::get_instance();
        }

        foreach ( $this->emails as $email ) {
            $yaymail_emails->register( $email );
        }
    }

    public function get_emails() {
        return $this->emails;
    }
}
