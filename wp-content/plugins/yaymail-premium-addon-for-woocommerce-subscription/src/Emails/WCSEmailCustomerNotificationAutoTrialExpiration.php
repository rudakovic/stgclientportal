<?php

namespace YayMailAddonWcSubscription\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMailAddonWcSubscription\SingletonTrait;

/**
 * WCSEmailCustomerNotificationAutoTrialExpiration Class
 *
 * @method static WCSEmailCustomerNotificationAutoTrialExpiration get_instance()
 */
class WCSEmailCustomerNotificationAutoTrialExpiration extends BaseEmail {

    use SingletonTrait;

    protected function __construct() {
        $emails = \WC_Emails::instance()->get_emails();
        if ( ! isset( $emails['WCS_Email_Customer_Notification_Auto_Trial_Expiration'] ) ) {
            return;
        }
        $email            = $emails['WCS_Email_Customer_Notification_Auto_Trial_Expiration'];
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
        $email_title = __( 'Free trial expiration: automatic payment notice', 'woocommerce-subscriptions' );
        // translators: customer name.
        $email_text = '<p>' . sprintf( __( 'Hi %s.', 'woocommerce-subscriptions' ), '[yaymail_billing_first_name] [yaymail_billing_last_name]' ) . '</p>';
        // translators: %1$s: human readable time difference (eg 3 days, 1 day), %2$s: date in local format.
        $email_text .= '<p>' . sprintf( __( 'Your paid subscription begins when your free trial expires in %1$s — that’s <strong>%2$s</strong>.', 'woocommerce-subscriptions' ), '[yaymail_wc_subscription_time_til_event]', '[yaymail_wc_subscription_event_date]' ) . '</p>';
        // translators: %1$s: link to account dashboard.
        $email_text .= '<p>' . sprintf( __( 'Payment will be deducted using the payment method on file. You can manage this subscription from your %1$s.', 'woocommerce-subscriptions' ), '<a href="[yaymail_wc_subscription_order_url]">' . esc_html__( 'account dashboard', 'woocommerce-subscriptions' ) . '</a>' ) . '</p>';
        $email_text .= '<p>' . esc_html__( 'Here are the details:', 'woocommerce-subscriptions' ) . '</p>';

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
                    'type' => 'Footer',
                ],
            ]
        );

        return $default_elements;
    }

    public function get_template_path() {
        return YAYMAIL_ADDON_WS_PLUGIN_PATH . 'src/templates/emails/customer-notification-auto-trial-ending.php';
    }
}
