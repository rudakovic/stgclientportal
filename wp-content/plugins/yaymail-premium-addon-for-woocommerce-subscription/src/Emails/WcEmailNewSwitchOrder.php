<?php

namespace YayMailAddonWcSubscription\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMailAddonWcSubscription\SingletonTrait;

/**
 * WcEmailNewSwitchOrder Class
 *
 * @method static WcEmailNewSwitchOrder get_instance()
 */
class WcEmailNewSwitchOrder extends BaseEmail {
    use SingletonTrait;

    protected function __construct() {
        $emails = \WC_Emails::instance()->get_emails();
        if ( ! isset( $emails['WCS_Email_New_Switch_Order'] ) ) {
            return;
        }
        $email            = $emails['WCS_Email_New_Switch_Order'];
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
        $email_title = __( 'Subscription Switched', 'woocommerce-subscriptions' );

        // translators: customer name.
        $email_text = '[yaymail_wc_subscription_switched_email_text]';

        $new_subscriptions_detail_title      = esc_html__( 'Switch Order Details', 'woocommerce-subscriptions' );
        $complete_subscriptions_detail_title = esc_html__( 'New subscription details', 'woocommerce-subscriptions' );

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
                        'rich_text' => '<p><span>' . $email_text . '</span></p>',
                    ],
                ],
                [
                    'type'       => 'Text',
                    'attributes' => [
                        'rich_text'  => '<p><span style=\"font-size: 20px; font-weight: bold;\">' . $new_subscriptions_detail_title . '</span></p>',
                        'text_color' => esc_attr( YAYMAIL_COLOR_WC_DEFAULT ),
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
                    'type'       => 'Text',
                    'attributes' => [
                        'rich_text'  => '<p><span style=\"font-size: 20px; font-weight: bold;\">' . $complete_subscriptions_detail_title . '</span></p>',
                        'text_color' => esc_attr( YAYMAIL_COLOR_WC_DEFAULT ),
                    ],
                ],
                [
                    'type'            => 'AddonWsSubscriptionSwitchOrderDetails',
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
        return YAYMAIL_ADDON_WS_PLUGIN_PATH . 'src/templates/emails/wc-email-new-switch-order.php';
    }
}
