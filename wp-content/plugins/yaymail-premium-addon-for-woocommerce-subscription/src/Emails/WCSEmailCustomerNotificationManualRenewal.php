<?php

namespace YayMailAddonWcSubscription\Emails;

use YayMail\Utils\TemplateHelpers;
use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMailAddonWcSubscription\SingletonTrait;

/**
 * WCSEmailCustomerNotificationManualRenewal Class
 *
 * @method static WCSEmailCustomerNotificationManualRenewal get_instance()
 */
class WCSEmailCustomerNotificationManualRenewal extends BaseEmail {

    use SingletonTrait;

    protected function __construct() {
        $emails = \WC_Emails::instance()->get_emails();
        if ( ! isset( $emails['WCS_Email_Customer_Notification_Manual_Renewal'] ) ) {
            return;
        }
        $email            = $emails['WCS_Email_Customer_Notification_Manual_Renewal'];
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
            'name'        => 'yaymail_wc_subscription_can_renew_early',
            'description' => __( 'Subscription Can Renew Early', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_can_renew_early' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_renewal_link',
            'description' => __( 'Subscription Renewal Link', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_renewal_link' ],
        ];

        return $shortcodes;
    }

    public function yaymail_wc_subscription_renewal_link( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        $template = ! empty( $data['template'] ) ? $data['template'] : null;

        $text_link_color = ! empty( $template ) ? $template->get_text_link_color() : YAYMAIL_COLOR_WC_DEFAULT;

        $link_style = TemplateHelpers::get_style(
            [
                'color'           => 'heading' === $data['element']['type'] ? 'inherit' : $text_link_color,
                'text-decoration' => 'heading' !== $data['element']['type'] ? 'underline' : 'none',
            ]
        );

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            $link_text = __( 'Renew Subscription', 'woocommerce-subscriptions' );
            return wp_kses_post( "<a style='" . $link_style . "' href='" . esc_url( site_url() ) . "'>" . $link_text . '</a>' );

        }

        $can_renew_early = $render_data['can_renew_early'];
        $url_for_renewal = $render_data['url_for_renewal'];

        if ( $can_renew_early ) {
            $link_text = __( 'Renew Subscription', 'woocommerce-subscriptions' );
        } else {
            $link_text = __( 'Manage Subscription', 'woocommerce-subscriptions' );
        }

        return wp_kses_post( "<a style='{$link_style}' href='{$url_for_renewal}'>{$link_text}</a>" );
    }

    public function yaymail_wc_subscription_can_renew_early( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) || ! empty( $render_data['can_renew_early'] ) ) {
            return wp_kses(
                __( 'You can <strong>renew it manually</strong> in a few short steps via the <em>Subscriptions</em> tab in your account dashboard.', 'woocommerce-subscriptions' ),
                [
                    'strong' => [],
                    'em'     => [],
                ]
            );
        }

        return '';
    }

    public function get_default_elements() {
        $email_title = __( 'Manual renewal notice', 'woocommerce-subscriptions' );
        // translators: customer name.
        $email_text = '<p>' . sprintf( __( 'Hi %s.', 'woocommerce-subscriptions' ), '[yaymail_billing_first_name] [yaymail_billing_last_name]' ) . '</p>';
        // translators: %1$s: human readable time difference (eg 3 days, 1 day), %2$s: date in local format.
        $email_text .= '<p>' . sprintf( __( 'Your subscription is up for renewal in %1$s — that’s <strong>%2$s</strong>.', 'woocommerce-subscriptions' ), '[yaymail_wc_subscription_time_til_event]', '[yaymail_wc_subscription_event_date]' ) . ' [yaymail_wc_subscription_can_renew_early]</p>';

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
                        'rich_text' => '<p style=\"margin: 0px;font-size: 14px; text-align: center;\">[yaymail_wc_subscription_renewal_link]</p>',
                    ],
                ],
                [
                    'type'       => 'Text',
                    'attributes' => [
                        'rich_text' => '<p style=\"margin: 0px;font-size: 14px; ">' . __( 'Here are the details:', 'woocommerce-subscriptions' ) . '</p>',
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
                            'bottom' => 15,
                            'left'   => 50,
                            'right'  => 50,
                        ],
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
        return YAYMAIL_ADDON_WS_PLUGIN_PATH . 'src/templates/emails/customer-notification-manual-renewal.php';
    }
}
