<?php

namespace YayMailAddonWcSubscription\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMailAddonWcSubscription\SingletonTrait;

/**
 * ENREmailCustomerAutoRenewalReminder Class
 *
 * @method static ENREmailCustomerAutoRenewalReminder get_instance()
 */
class ENREmailCustomerAutoRenewalReminder extends BaseEmail {
    use SingletonTrait;

    protected function __construct() {
        $emails = \WC_Emails::instance()->get_emails();
        if ( ! isset( $emails['ENR_Email_Customer_Auto_Renewal_Reminder'] ) ) {
            return;
        }
        $email            = $emails['ENR_Email_Customer_Auto_Renewal_Reminder'];
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
        $email_title = __( 'Your subscription is going to auto renew', 'enhancer-for-woocommerce-subscriptions' );
        /* translators: 1: Subscription number 2: Subscription due date */
        $email_text = sprintf( esc_html__( 'Your Subscription %1$s is due for renewal on %2$s. Please make sure that you have sufficient funds in your account.', 'enhancer-for-woocommerce-subscriptions' ), '<a href="[yaymail_wc_subscription_order_url]">#[yaymail_wc_subscription_order_number]</a>', '[yaymail_wc_subscription_next_payment]' );

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
                    'type'            => 'AddonWsSubscriptionOrderDetails',
                    'addon_namespace' => 'YayMailAddonWcSubscription',
                ],
                [
                    'type' => 'BillingShippingAddress',
                ],
                [
                    'type'       => 'Text',
                    'attributes' => [
                        'rich_text' => '<p><span>' . __( 'Thanks for shopping with us.', 'enhancer-for-woocommerce-subscriptions' ) . '</span></p>',
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
        return YAYMAIL_ADDON_WS_PLUGIN_PATH . 'src/templates/emails/enr-email-customer-auto-renewal-reminder.php';
    }
}
