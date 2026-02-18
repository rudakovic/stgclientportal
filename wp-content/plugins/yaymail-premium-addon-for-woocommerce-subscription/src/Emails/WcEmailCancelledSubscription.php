<?php

namespace YayMailAddonWcSubscription\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMailAddonWcSubscription\SingletonTrait;

/**
 * WcEmailCancelledSubscription Class
 *
 * @method static WcEmailCancelledSubscription get_instance()
 */
class WcEmailCancelledSubscription extends BaseEmail {
    use SingletonTrait;

    protected function __construct() {
        $emails = \WC_Emails::instance()->get_emails();
        if ( ! isset( $emails['WCS_Email_Cancelled_Subscription'] ) ) {
            return;
        }
        $email            = $emails['WCS_Email_Cancelled_Subscription'];
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
        $email_title = __( 'Subscription Cancelled', 'woocommerce-subscriptions' );
        // translators: customer name.
        $email_text      = sprintf( esc_html__( 'A subscription belonging to %1$s has been cancelled. Their subscription\'s details are as follows:', 'woocommerce' ), '[yaymail_billing_first_name] [yaymail_billing_last_name]' );
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
                    'type'            => 'AddonWsSubscriptionCancelled',
                    'addon_namespace' => 'YayMailAddonWcSubscription',
                    'attributes'      => [
                        'padding' => [
                            'top'    => 0,
                            'right'  => 50,
                            'bottom' => 15,
                            'left'   => 50,
                        ],
                    ],
                ],
                [
                    'type'            => 'AddonWsSubscriptionOrderDetails',
                    'addon_namespace' => 'YayMailAddonWcSubscription',
                    'attributes'      => [
                        'title'   => '',
                        'padding' => [
                            'top'    => 0,
                            'right'  => 50,
                            'bottom' => 15,
                            'left'   => 50,
                        ],
                    ],
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
        return YAYMAIL_ADDON_WS_PLUGIN_PATH . 'src/templates/emails/wc-email-cancelled-subscription.php';
    }
}
