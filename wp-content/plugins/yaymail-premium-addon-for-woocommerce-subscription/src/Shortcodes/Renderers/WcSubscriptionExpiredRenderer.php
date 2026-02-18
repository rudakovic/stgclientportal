<?php

namespace YayMailAddonWcSubscription\Shortcodes\Renderers;

use YayMail\Utils\TemplateHelpers;

/**
 * @method: static WcSubscriptionExpiredRenderer get_instance()
 */
class WcSubscriptionExpiredRenderer {

    public $item_totals = [];

    public $element_data = null;

    public $is_placeholder = false;

    public $titles = [];

    public $show_product_item_cost = false;

    private $subscription = null;

    private $sample_subscription = [];


    public function __construct( $subscription, $element_data, $is_placeholder ) {
        $yaymail_settings     = yaymail_settings();
        $this->element_data   = $element_data;
        $this->is_placeholder = $is_placeholder;
        $this->initialize_titles();

        if ( ! $subscription instanceof \WC_Order ) {
            $this->initialize_sample_data();
        } else {
            $this->subscription = $subscription;
        }
    }

    public function initialize_titles() {
            $this->titles = [
                'id'        => $this->element_data['id_title'] ?? TemplateHelpers::get_content_as_placeholder( 'id_title', esc_html__( 'Subscription', 'yaymail' ), $this->is_placeholder ),

                'price'     => $this->element_data['price_title'] ?? TemplateHelpers::get_content_as_placeholder( 'price_title', esc_html__( 'Price', 'yaymail' ), $this->is_placeholder ),

                'last_date' => $this->element_data['last_date_title'] ?? TemplateHelpers::get_content_as_placeholder( 'last_date_title', esc_html__( 'Last Order Date', 'yaymail' ), $this->is_placeholder ),

                'end_date'  => $this->element_data['end_date_title'] ?? TemplateHelpers::get_content_as_placeholder( 'end_date_title', esc_html__( 'End Date', 'yaymail' ), $this->is_placeholder ),
            ];
    }

    private function initialize_sample_data() {
        $this->sample_subscription = [
            'id'        => 1,
            'price'     => wc_price( 10 ),
            'last_date' => gmdate( 'm-d-Y' ),
            'end_date'  => gmdate( 'm-d-Y' ),
        ];
    }

    public function get_styles() {
        return TemplateHelpers::get_style(
            [
                'padding'      => '12px',
                'font-size'    => '14px',
                'text-align'   => yaymail_get_text_align(),
                'font-family'  => TemplateHelpers::get_font_family_value( isset( $this->element_data['font_family'] ) ? $this->element_data['font_family'] : 'inherit' ),
                'color'        => isset( $this->element_data['text_color'] ) ? $this->element_data['text_color'] : 'inherit',
                'border-width' => '1px',
                'border-style' => 'solid',
                'border-color' => isset( $this->element_data['border_color'] ) ? $this->element_data['border_color'] : 'inherit',
            ]
        );
    }

    public function render() {
        $style       = $this->get_styles();
        $table_style = $this->get_styles() . 'padding: 0';
        ?>
        <table class="yaymail-ws-subscription-expired td" cellspacing="0" cellpadding="6" style="<?php echo esc_attr( $table_style ); ?>" border="1" width="100%">

            <thead>
                <tr>
                    <th class="td id-title" scope="col" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $this->titles['id'] ); ?></th>
                    <th class="td price-title" scope="col" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $this->titles['price'] ); ?></th>
                    <th class="td last-date-title" scope="col" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $this->titles['last_date'] ); ?></th>
                    <th class="td end-date-title" scope="col" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $this->titles['end_date'] ); ?></th>
                </tr>
            </thead>

            <tbody>
                    <?php
                        $is_using_sample = empty( $this->subscription );

                        $subscription_number = $is_using_sample ? $this->sample_subscription['id'] : $this->subscription->get_order_number();

                        $subscription_id = $is_using_sample ? $this->sample_subscription['id'] : $this->subscription->get_id();

                        $subscription_price = $is_using_sample ? $this->sample_subscription['price'] : $this->subscription->get_formatted_order_total();

                        $subscription_last_date = $is_using_sample ? $this->sample_subscription['last_date'] : ( ! empty( $this->subscription->get_time( 'last_order_date_created', 'site' ) ) ? date_i18n( wc_date_format(), $this->subscription->get_time( 'last_order_date_created', 'site' ) ) : esc_html__( '-', 'woocommerce-subscriptions' ) );

                        $subscription_end_date = $is_using_sample ? $this->sample_subscription['end_date'] : date_i18n( wc_date_format(), $this->subscription->get_time( 'end', 'site' ) );
                    ?>
                <tr>
                    <td class="td id-row" scope="row" style="<?php echo esc_attr( $style ); ?>">
                        <a href="<?php echo esc_url( wcs_get_edit_post_link( $subscription_id ) ); ?>">#<?php echo esc_html( $subscription_number ); ?></a>
                    </td>
                    <td class="td price-row" scope="row" style="<?php echo esc_attr( $style ); ?>">
                        <?php echo wp_kses_post( $subscription_price ); ?>
                    </td>
                    <td class="td last-date-row" scope="row" style="<?php echo esc_attr( $style ); ?>">
                        <?php echo wp_kses_post( $subscription_last_date ); ?>
                    </td>
                    <td class="td end-date-row" scope="row" style="<?php echo esc_attr( $style ); ?>">
                        <?php echo esc_html( $subscription_end_date ); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }
}
