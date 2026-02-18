<?php

namespace YayMailAddonWcSubscription\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMailAddonWcSubscription\SingletonTrait;
use YayMail\Utils\TemplateHelpers;

/**
 * WCSEmailCustomerNotificationAutoRenewal Class
 *
 * @method static WCSEmailCustomerNotificationAutoRenewal get_instance()
 */
class WCSEmailCustomerNotificationAutoRenewal extends BaseEmail {

    use SingletonTrait;

    protected function __construct() {
        $emails = \WC_Emails::instance()->get_emails();
        if ( ! isset( $emails['WCS_Email_Customer_Notification_Auto_Renewal'] ) ) {
            return;
        }
        $email            = $emails['WCS_Email_Customer_Notification_Auto_Renewal'];
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
        add_action( 'yaymail_' . $email->id . '_register_shortcodes', [ $this, 'add_shortcodes_to_email' ] );
    }
    public function add_shortcodes_to_email( $email ) {
        $email->register_shortcodes( $this->list_shortcodes() );
    }
    public function list_shortcodes() {
        $shortcodes = [];

        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_customer_subscription_details_link',
            'description' => __( 'Customer Dashboard\'s Subscription Details', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'attributes'  => [
                'text_link' => esc_html__( 'account dashboard', 'woocommerce-subscriptions' ),
            ],
            'callback'    => [ $this, 'yaymail_wc_subscription_customer_subscription_details_link' ],
        ];

        return $shortcodes;
    }

    public function yaymail_wc_subscription_customer_subscription_details_link( $data, $shortcode_atts = [] ) {
        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        $template = ! empty( $data['template'] ) ? $data['template'] : null;

        $text_link_color = ! empty( $template ) ? $template->get_text_link_color() : YAYMAIL_COLOR_WC_DEFAULT;

        $link_style = TemplateHelpers::get_style(
            [
                'color'           => 'heading' === $data['element']['type'] ? 'inherit' : $text_link_color,
                'text-decoration' => 'heading' !== $data['element']['type'] ? 'underline' : 'none',
            ]
        );

        $is_placeholder = isset( $data['is_placeholder'] ) ? $data['is_placeholder'] : false;

        $text_link = isset( $shortcode_atts['text_link'] ) ? $shortcode_atts['text_link'] : TemplateHelpers::get_content_as_placeholder( 'text_link', esc_html__( 'account dashboard', 'woocommerce-subscriptions' ), $is_placeholder );

        $url = ! empty( $render_data['subscription'] ) ? esc_url( $render_data['subscription']->get_view_order_url() ) : '#';
        return wp_kses_post(
            '<a style="' . esc_attr( $link_style ) . '" href="' . esc_url( $url ) . '">' . esc_html( $text_link ) . '</a>'
        );
    }

    public function get_default_elements() {
        $email_title = __( 'Automatic renewal notice', 'woocommerce-subscriptions' );
        // translators: customer name.
        $email_text = '<p>' . sprintf( __( 'Hi %s.', 'woocommerce-subscriptions' ), '[yaymail_billing_first_name] [yaymail_billing_last_name]' ) . '</p>';
        // translators: %1$s: human readable time difference (eg 3 days, 1 day), %2$s: date in local format.
        $email_text .= '<p>' . sprintf( __( 'Your subscription will <strong>automatically renew</strong> in %1$s — that’s <strong>%2$s</strong>.', 'woocommerce-subscriptions' ), '[yaymail_wc_subscription_time_til_event]', '[yaymail_wc_subscription_event_date]' ) . '</p>';

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
                    'type'       => 'Text',
                    'attributes' => [
                        'rich_text' => '<p>' . esc_html__( 'Here are the details:', 'woocommerce-subscriptions' ) . '</p>',
                        'padding'   => [
                            'top'    => 0,
                            'bottom' => 0,
                            'left'   => 50,
                            'right'  => 50,
                        ],
                    ],
                ],
                [
                    'type'            => 'AddonWsSubscriptionInformation',
                    'addon_namespace' => 'YayMailAddonWcSubscription',
                    'attributes'      => [
                        'padding' => [
                            'top'    => 0,
                            'bottom' => 0,
                            'left'   => 50,
                            'right'  => 50,
                        ],
                    ],
                ],
                [
                    'type'       => 'Text',
                    'attributes' => [
                        'rich_text' => sprintf(
                            // translators: %1$s: link to account dashboard.
                            '<small>' . __(
                                'You can manage this subscription from your %s',
                                'woocommerce-subscriptions'
                            ) . '</small>',
                            '[yaymail_wc_subscription_customer_subscription_details_link]'
                        ),
                    ],
                ],
                [
                    'type'       => 'Text',
                    'attributes' => [
                        'rich_text' => __( 'Thank you for being a loyal customer, [yaymail_billing_first_name] — we appreciate your business.', 'woocommerce-subscriptions' ),
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
        return YAYMAIL_ADDON_WS_PLUGIN_PATH . 'src/templates/emails/customer-notification-auto-renewal.php';
    }
}
