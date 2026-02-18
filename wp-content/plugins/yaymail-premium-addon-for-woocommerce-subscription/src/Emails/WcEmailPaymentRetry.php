<?php

namespace YayMailAddonWcSubscription\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMailAddonWcSubscription\SingletonTrait;

/**
 * WcEmailPaymentRetry Class
 *
 * @method static WcEmailPaymentRetry get_instance()
 */
class WcEmailPaymentRetry extends BaseEmail {
    use SingletonTrait;

    protected function __construct() {
        $emails = \WC_Emails::instance()->get_emails();
        if ( ! isset( $emails['WCS_Email_Payment_Retry'] ) ) {
            return;
        }
        $email            = $emails['WCS_Email_Payment_Retry'];
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
        $email_title = __( 'Automatic renewal payment failed', 'woocommerce-subscriptions' );
        $email_text  = 'The automatic recurring payment for order #[yaymail_order_number is_plain="true"] from [yaymail_billing_first_name] [yaymail_billing_last_name] has failed. The payment will be retried [yaymail_wc_subscription_get_human_time_diff]';

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
                    'type'       => 'Text',
                    'attributes' => [
                        'rich_text' => '<p><span>' . esc_html__( 'The renewal order is as follows:', 'woocommerce-subscriptions' ) . '</span></p>',
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
                    'type'       => 'Text',
                    'attributes' => [
                        'rich_text' => '<p><span>' . __( 'Hopefully they’ll be back. Read more about <a href="https://woocommerce.com/document/managing-orders/">troubleshooting failed payments</a>.', 'woocommerce' ) . '</span></p>',
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
        return YAYMAIL_ADDON_WS_PLUGIN_PATH . 'src/templates/emails/wc-email-admin-payment-retry.php';
    }
}
