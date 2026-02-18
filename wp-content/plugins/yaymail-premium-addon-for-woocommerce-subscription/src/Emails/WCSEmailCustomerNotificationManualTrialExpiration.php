<?php

namespace YayMailAddonWcSubscription\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMailAddonWcSubscription\SingletonTrait;

/**
 * WCSEmailCustomerNotificationManualTrialExpiration Class
 *
 * @method static WCSEmailCustomerNotificationManualTrialExpiration get_instance()
 */
class WCSEmailCustomerNotificationManualTrialExpiration extends BaseEmail {

    use SingletonTrait;

    protected function __construct() {
        $emails = \WC_Emails::instance()->get_emails();
        if ( ! isset( $emails['WCS_Email_Customer_Notification_Manual_Trial_Expiration'] ) ) {
            return;
        }
        $email            = $emails['WCS_Email_Customer_Notification_Manual_Trial_Expiration'];
        $this->id         = $email->id;
        $this->title      = $email->get_title();
        $this->root_email = $email;
        $this->recipient  = function_exists( 'yaymail_get_email_recipient_zone' ) ? yaymail_get_email_recipient_zone( $email ) : '';
        $this->source     = [
            'plugin_id'   => 'wc-subscriptions',
            'plugin_name' => 'WooCommerce Subscriptions',
        ];
       $this->render_priority = apply_filters( 'yaymail_email_render_priority', 10, $this->id );
        add_filter( 'wc_get_template', [ $this, 'get_template_file' ], $this->render_priority ?? 10, 3 );
    }

    public function get_default_elements() {
        $email_title = __( 'Free trial expiration: manual payment required', 'woocommerce-subscriptions' );
        // translators: customer name.
        $email_text = '<p>' . sprintf( __( 'Heads up, %s.', 'woocommerce-subscriptions' ), '[yaymail_billing_first_name] [yaymail_billing_last_name]' ) . '</p>';
        // translators: %1$s: human readable time difference (eg 3 days, 1 day), %2$s: date in local format.
        $email_text .= '<p>' . sprintf( __( 'Your free trial expires in %1$s — that’s <strong>%2$s</strong>.', 'woocommerce-subscriptions' ), '[yaymail_wc_subscription_time_til_event]', '[yaymail_wc_subscription_event_date]' ) . '</p>';

        $additional_text = __( 'Congratulations on the sale.', 'woocommerce' );

        $default_elements = ElementsLoader::load_elements(
            [
                [
                    'type' => 'Logo',
                ],
                [
                    'type'       => 'Heading',
                    'attributes' => [
                        'rich_text' => $email_title,
                    ],
                ],
                [
                    'type'       => 'Text',
                    'attributes' => [
                        'rich_text' => $email_text,
                    ],
                ],
                [
                    'type'            => 'AddonWsSubscriptionInformation',
                    'addon_namespace' => 'YayMailAddonWcSubscription',
                ],
                [
                    'type'       => 'Text',
                    'attributes' => [
                        'rich_text' => '<p>Thank you for choosing [yaymail_site_name]!</p>',
                    ],
                ],
                [
                    'type' => 'Footer',
                ],
            ]
        );

        return $default_elements;
    }

    public function get_template_path() {
        return YAYMAIL_ADDON_WS_PLUGIN_PATH . 'src/templates/emails/customer-notification-manual-trial-ending.php';
    }
}
