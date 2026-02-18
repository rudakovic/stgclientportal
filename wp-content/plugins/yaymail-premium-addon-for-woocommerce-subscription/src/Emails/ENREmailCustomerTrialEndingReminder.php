<?php

namespace YayMailAddonWcSubscription\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMailAddonWcSubscription\SingletonTrait;

/**
 * ENREmailCustomerTrialEndingReminder Class
 *
 * @method static ENREmailCustomerTrialEndingReminder get_instance()
 */
class ENREmailCustomerTrialEndingReminder extends BaseEmail {
    use SingletonTrait;

    protected function __construct() {
        $emails = \WC_Emails::instance()->get_emails();
        if ( ! isset( $emails['ENR_Email_Customer_Trial_Ending_Reminder'] ) ) {
            return;
        }
        $email            = $emails['ENR_Email_Customer_Trial_Ending_Reminder'];
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
        $email_title = __( 'Your subscription trial going to end', 'enhancer-for-woocommerce-subscriptions' );
        $email_text  = esc_html__( 'Your subscription trial is going to end soon. Here\'s the details of your subscription.', 'enhancer-for-woocommerce-subscriptions' );

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
                    'type'            => 'AddonENRSubscriptionTrialEndDetails',
                    'addon_namespace' => 'YayMailAddonWcSubscription',
                    'attributes'      => [
                        'main_title' => '',
                        'padding'    => [
                            'top'    => 0,
                            'bottom' => 15,
                            'left'   => 50,
                            'right'  => 50,
                        ],
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
        return YAYMAIL_ADDON_WS_PLUGIN_PATH . 'src/templates/emails/enr-email-customer-trial-ending-reminder.php';
    }
}
