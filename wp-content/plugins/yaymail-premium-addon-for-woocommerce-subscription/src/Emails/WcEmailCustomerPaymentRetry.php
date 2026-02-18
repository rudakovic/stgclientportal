<?php

namespace YayMailAddonWcSubscription\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMailAddonWcSubscription\SingletonTrait;

/**
 * WcEmailCustomerPaymentRetry Class
 *
 * @method static WcEmailCustomerPaymentRetry get_instance()
 */
class WcEmailCustomerPaymentRetry extends BaseEmail {
    use SingletonTrait;

    protected function __construct() {
        $emails = \WC_Emails::instance()->get_emails();
        if ( ! isset( $emails['WCS_Email_Customer_Payment_Retry'] ) ) {
            return;
        }
        $email            = $emails['WCS_Email_Customer_Payment_Retry'];
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
        $email_title = 'Automatic payment failed for order [yaymail_order_number]';
        /* translators: %s: Customer first name */
        $email_text = sprintf( esc_html__( 'Hi %s,', 'woocommerce-subscriptions' ), '[yaymail_billing_first_name] [yaymail_billing_last_name]' );
        /* translators: %s: lowercase human time diff in the form returned by wcs_get_human_time_diff(), e.g. 'in 12 hours' */
        $email_text .= '<br>' . sprintf( esc_html_x( 'The automatic payment to renew your subscription has failed. We will retry the payment %s.', 'In customer renewal invoice email', 'woocommerce-subscriptions' ), '[yaymail_wc_subscription_get_human_time_diff]' );
        /* translators: %1$s %2$s: link markup to checkout payment url, note: no full stop due to url at the end */
        $email_text .= '<br>' . wp_kses( sprintf( _x( 'To reactivate the subscription now, you can also log in and pay for the renewal from your account page: %1$sPay Now &raquo;%2$s', 'In customer renewal invoice email', 'woocommerce-subscriptions' ), '<a href="[yaymail_order_payment_url]">', '</a>' ), [ 'a' => [ 'href' => true ] ] );

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
                        'rich_text' => '<p><span>' . $email_text . '</span></p>',
                    ],
                ],
                [
                    'type'       => 'OrderDetails',
                    'attributes' => [
                        'title' => 'Order #[yaymail_wc_subscription_order_id] <b>([yaymail_order_date])</b>',
                    ],
                ],
                [
                    'type'            => 'AddonWsSubscriptionInformation',
                    'addon_namespace' => 'YayMailAddonWcSubscription',
                ],

                [
                    'type' => 'BillingShippingAddress',
                ],

                [
                    'type' => 'Footer',
                ],
            ]
        );

        return $default_elements;
    }

    public function get_template_path() {
        return YAYMAIL_ADDON_WS_PLUGIN_PATH . 'src/templates/emails/wc-email-customer-payment-retry.php';
    }
}
