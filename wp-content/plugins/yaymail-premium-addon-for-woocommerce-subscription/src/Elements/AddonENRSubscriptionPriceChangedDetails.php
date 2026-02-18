<?php
namespace YayMailAddonWcSubscription\Elements;

use YayMail\Abstracts\BaseElement;
use YayMail\Elements\ElementsHelper;
use YayMailAddonWcSubscription\SingletonTrait;
use YayMailAddonWcSubscription\Emails\ENREmailCustomerSubscriptionPriceUpdated;
/**
 * SubscriptionPriceChangedDetails
 */
class AddonENRSubscriptionPriceChangedDetails extends BaseElement {

    use SingletonTrait;

    protected static $type = 'addon_enr_subscription_price_changed_details';

    protected function __construct() {
        $this->available_email_ids = [];

        if ( class_exists( 'WC_Subscriptions_Enhancer' ) ) {
            $this->available_email_ids = [
                ENREmailCustomerSubscriptionPriceUpdated::get_instance()->get_id(),
            ];
        }
    }

    public static function get_data( $attributes = [] ) {
        self::$icon = YAYMAIL_EXTRA_ELEMENT_ICON;

        return [
            'id'              => uniqid(),
            'type'            => self::$type,
            'name'            => __( 'Subscription Price Changed Details', 'yaymail' ),
            'icon'            => self::$icon,
            'group'           => YAYMAIL_ADDON_WS_NAMES,
            'position'        => 250,
            'available'       => true,
            'addon_namespace' => 'YayMailAddonWcSubscription',
            'data'            => [
                'padding'          => ElementsHelper::get_spacing( $attributes ),
                'background_color' => ElementsHelper::get_color(
                    $attributes,
                    [
                        'default_value' => '#fff',
                    ]
                ),
                'title_color'      => ElementsHelper::get_color(
                    $attributes,
                    [
                        'value_path'    => 'title_color',
                        'title'         => __( 'Title color', 'yaymail' ),
                        'default_value' => YAYMAIL_COLOR_WC_DEFAULT,
                    ]
                ),
                'text_color'       => ElementsHelper::get_color(
                    $attributes,
                    [
                        'value_path'    => 'text_color',
                        'title'         => __( 'Text color', 'yaymail' ),
                        'default_value' => YAYMAIL_COLOR_TEXT_DEFAULT,
                    ]
                ),
                'border_color'     => ElementsHelper::get_color(
                    [
                        'value_path'    => 'border_color',
                        'title'         => __( 'Border color', 'yaymail' ),
                        'default_value' => YAYMAIL_COLOR_BORDER_DEFAULT,
                    ]
                ),
                'font_family'      => ElementsHelper::get_font_family_selector( $attributes ),
                'main_title'       => ElementsHelper::get_text_input(
                    $attributes,
                    [
                        'value_path'    => 'main_title',
                        'title'         => __( 'Main title', 'yaymail' ),
                        'default_value' => __( 'Subscription Price Changed Details', 'woocommerce' ),
                    ]
                ),
                'new_price_title'  => ElementsHelper::get_text_input(
                    $attributes,
                    [
                        'value_path'    => 'new_price_title',
                        'title'         => __( 'New price title', 'yaymail' ),
                        'default_value' => __( 'New price', 'woocommerce' ),
                    ]
                ),
                'old_price_title'  => ElementsHelper::get_text_input(
                    $attributes,
                    [
                        'value_path'    => 'old_price_title',
                        'title'         => __( 'Old Price title', 'yaymail' ),
                        'default_value' => __( 'Old Price', 'woocommerce' ),
                    ]
                ),

                'rich_text'        => [
                    'value_path'    => 'rich_text',
                    'component'     => '',
                    'title'         => __( 'Content', 'yaymail' ),
                    'default_value' => '[yaymail_enr_subscription_price_changed_details]',
                    'type'          => 'content',
                ],
            ],
        ];
    }

    public static function get_layout( $element, $args ) {
        $path = 'src/templates/elements/subscription-price-changed-details.php';

        return yaymail_get_content( $path, array_merge( [ 'element' => $element ], $args ), YAYMAIL_ADDON_WS_PLUGIN_PATH );
    }
}
