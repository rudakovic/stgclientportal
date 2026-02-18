<?php

namespace YayMailAddonWcSubscription\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMailAddonWcSubscription\SingletonTrait;

/**
 * WcEmailProcessingRenewalOrder Class
 *
 * @method static WcEmailProcessingRenewalOrder get_instance()
 */
class WcEmailProcessingRenewalOrder extends BaseEmail {
    use SingletonTrait;

    protected function __construct() {
        $emails = \WC_Emails::instance()->get_emails();
        if ( ! isset( $emails['WCS_Email_Processing_Renewal_Order'] ) ) {
            return;
        }
        $email            = $emails['WCS_Email_Processing_Renewal_Order'];
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
        $email_title = __( 'Thank you for your order', 'woocommerce-subscriptions' );
        // translators: customer name.
        $email_text = sprintf( esc_html__( 'Hi %s,', 'woocommerce' ), '[yaymail_billing_first_name]' );
        // translators: order number.
        $email_text .= '<br><br>' . sprintf( esc_html__( 'Just to let you know &mdash; we\'ve received your subscription renewal order #%s, and it is now being processed:', 'woocommerce-subscriptions' ), '[yaymail_order_number]' );

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
        return YAYMAIL_ADDON_WS_PLUGIN_PATH . 'src/templates/emails/wc-email-customer-processing-renewal-order.php';
    }
}
