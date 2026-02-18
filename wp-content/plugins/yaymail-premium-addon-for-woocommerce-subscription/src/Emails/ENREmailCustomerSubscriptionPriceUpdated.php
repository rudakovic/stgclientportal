<?php

namespace YayMailAddonWcSubscription\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMailAddonWcSubscription\SingletonTrait;

/**
 * ENREmailCustomerSubscriptionPriceUpdated Class
 *
 * @method static ENREmailCustomerSubscriptionPriceUpdated get_instance()
 */
class ENREmailCustomerSubscriptionPriceUpdated extends BaseEmail {
    use SingletonTrait;

    protected function __construct() {
        $emails = \WC_Emails::instance()->get_emails();
        if ( ! isset( $emails['ENR_Email_Customer_Subscription_Price_Updated'] ) ) {
            return;
        }
        $email            = $emails['ENR_Email_Customer_Subscription_Price_Updated'];
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
        $email_title = __( 'Your subscription price is updated', 'enhancer-for-woocommerce-subscriptions' );

        /* translators: 1: Subscription number */
        $email_text = sprintf( esc_html__( 'The subscription price for your subscription %s has been updated. You have to pay the updated price for the future renewals. Here\'s the details of your subscription.', 'enhancer-for-woocommerce-subscriptions' ), '<a href="[yaymail_wc_subscription_order_url]">#[yaymail_wc_subscription_order_number]</a>' );

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
                    'type'            => 'AddonENRSubscriptionPriceChangedDetails',
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
        return YAYMAIL_ADDON_WS_PLUGIN_PATH . 'src/templates/emails/enr-email-customer-subscription-price-updated.php';
    }
}
