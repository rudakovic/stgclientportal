<?php
namespace YayMailAddonWcSubscription\Elements;

use YayMail\Abstracts\BaseElement;
use YayMail\Elements\ElementsHelper;
use YayMailAddonWcSubscription\SingletonTrait;
use YayMailAddonWcSubscription\Emails\WcEmailCancelledSubscription;
use YayMailAddonWcSubscription\Emails\WcEmailExpiredSubscription;
use YayMailAddonWcSubscription\Emails\WcEmailOnHoldSubscription;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerShippingFrequencyNotification;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerTrialEndingReminder;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerAutoRenewalReminder;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerManualRenewalReminder;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerExpiryReminder;
/**
 * Subscription Order Details
 */
class AddonWsSubscriptionOrderDetails extends BaseElement {

    use SingletonTrait;

    protected static $type = 'addon_ws_subscription_order_details';

    protected function __construct() {
        $this->available_email_ids = [
            WcEmailCancelledSubscription::get_instance()->get_id(),

            // WcEmailCompletedSwitchOrder::get_instance()->get_id(),
            // WcEmailNewSwitchOrder::get_instance()->get_id(),
            // WcEmailProcessingRenewalOrder::get_instance()->get_id(),
            // WcEmailCompletedRenewalOrder::get_instance()->get_id(),
            // WcEmailOnHoldRenewalOrder::get_instance()->get_id(),
            // WcEmailCustomerRenewalInvoice::get_instance()->get_id(),

            WcEmailExpiredSubscription::get_instance()->get_id(),
            WcEmailOnHoldSubscription::get_instance()->get_id(),
            // WcEmailCustomerPaymentRetry::get_instance()->get_id(),
            // WcEmailPaymentRetry::get_instance()->get_id(),
        ];

        if ( class_exists( 'WC_Subscriptions_Enhancer' ) ) {
            // $this->available_email_ids[] = ENREmailCustomerProcessingShippingFulfilmentOrder::get_instance()->get_id();
            $this->available_email_ids[] = ENREmailCustomerShippingFrequencyNotification::get_instance()->get_id();
            $this->available_email_ids[] = ENREmailCustomerTrialEndingReminder::get_instance()->get_id();
            $this->available_email_ids[] = ENREmailCustomerAutoRenewalReminder::get_instance()->get_id();
            $this->available_email_ids[] = ENREmailCustomerManualRenewalReminder::get_instance()->get_id();
            $this->available_email_ids[] = ENREmailCustomerExpiryReminder::get_instance()->get_id();
        }
    }

    public static function get_data( $attributes = [] ) {
        self::$icon = YAYMAIL_EXTRA_ELEMENT_ICON;

        return [
            'id'              => uniqid(),
            'type'            => self::$type,
            'name'            => __( 'Subscription Order Details', 'yaymail' ),
            'icon'            => self::$icon,
            'group'           => YAYMAIL_ADDON_WS_NAMES,
            'available'       => true,
            'addon_namespace' => 'YayMailAddonWcSubscription',
            'position'        => 190,
            'data'            => [
                'padding'              => ElementsHelper::get_spacing( $attributes ),
                'background_color'     => ElementsHelper::get_color(
                    $attributes,
                    [
                        'default_value' => '#fff',
                    ]
                ),
                'title_color'          => ElementsHelper::get_color(
                    $attributes,
                    [
                        'value_path'    => 'title_color',
                        'title'         => __( 'Title color', 'yaymail' ),
                        'default_value' => YAYMAIL_COLOR_WC_DEFAULT,
                    ]
                ),
                'text_color'           => ElementsHelper::get_color(
                    $attributes,
                    [
                        'value_path'    => 'text_color',
                        'title'         => __( 'Text color', 'yaymail' ),
                        'default_value' => YAYMAIL_COLOR_TEXT_DEFAULT,
                    ]
                ),
                'border_color'         => ElementsHelper::get_color(
                    [
                        'value_path'    => 'border_color',
                        'title'         => __( 'Border color', 'yaymail' ),
                        'default_value' => YAYMAIL_COLOR_BORDER_DEFAULT,
                    ]
                ),
                'font_family'          => ElementsHelper::get_font_family_selector( $attributes ),
                'rich_text'            => [
                    'value_path'    => 'rich_text',
                    'component'     => '',
                    'title'         => __( 'Content', 'yaymail' ),
                    'default_value' => '[yaymail_wc_subscription_order_details]',
                    'type'          => 'content',
                ],
                'title'                => [
                    'value_path'    => 'title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Order item title', 'yaymail' ),
                    'default_value' => isset( $attributes['title'] ) ? $attributes['title'] : '<b>Subscription</b> #[yaymail_wc_subscription_id]',
                    'type'          => 'content',
                ],
                'product_title'        => [
                    'value_path'    => 'product_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Product title', 'yaymail' ),
                    'default_value' => esc_html__( 'Product', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'cost_title'           => [
                    'value_path'    => 'cost_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Cost title', 'yaymail' ),
                    'default_value' => esc_html__( 'Cost', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'quantity_title'       => [
                    'value_path'    => 'quantity_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Quantity title', 'yaymail' ),
                    'default_value' => esc_html__( 'Quantity', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'price_title'          => [
                    'value_path'    => 'price_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Price title', 'yaymail' ),
                    'default_value' => esc_html__( 'Price', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'cart_subtotal_title'  => [
                    'value_path'    => 'cart_subtotal_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Subtotal title', 'yaymail' ),
                    'default_value' => esc_html__( 'Subtotal:', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'payment_method_title' => [
                    'value_path'    => 'payment_method_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Payment method title', 'yaymail' ),
                    'default_value' => esc_html__( 'Payment method:', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'order_total_title'    => [
                    'value_path'    => 'order_total_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Total title', 'yaymail' ),
                    'default_value' => esc_html__( 'Total:', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'order_note_title'     => [
                    'value_path'    => 'order_note_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Note title', 'yaymail' ),
                    'default_value' => esc_html__( 'Note:', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'shipping_title'       => [
                    'value_path'    => 'shipping_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Shipping title', 'yaymail' ),
                    'default_value' => esc_html__( 'Shipping:', 'woocommerce' ),
                    'type'          => 'content',
                ],
                'discount_title'       => [
                    'value_path'    => 'discount_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Discount title', 'yaymail' ),
                    'default_value' => esc_html__( 'Discount:', 'woocommerce' ),
                    'type'          => 'content',
                ],
            ],
        ];
    }

    public static function get_layout( $element, $args ) {
        $path = 'src/templates/elements/subscription-order-details.php';
        return yaymail_get_content( $path, array_merge( [ 'element' => $element ], $args ), YAYMAIL_ADDON_WS_PLUGIN_PATH );
    }
}
