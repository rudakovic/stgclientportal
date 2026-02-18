<?php

namespace YayMailAddonWcSubscription\Shortcodes\Renderers;

use YayMail\Utils\TemplateHelpers;

/**
 * @method: static ENRSubscriptionEndDetails get_instance()
 */
class ENRSubscriptionEndDetails {

    public $item_totals = [];

    public $element_data = null;

    public $is_placeholder = false;

    public $titles = [];

    public $show_product_item_cost = false;

    private $subscription = null;

    private $sample_subscription = [];


    public function __construct( $subscription, $element_data, $is_placeholder ) {
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
                'id'       => $this->element_data['id_title'] ?? TemplateHelpers::get_content_as_placeholder( 'id_title', esc_html__( 'Subscription', 'yaymail' ), $this->is_placeholder ),

                'price'    => $this->element_data['price_title'] ?? TemplateHelpers::get_content_as_placeholder( 'price_title', esc_html__( 'Price', 'yaymail' ), $this->is_placeholder ),

                'end_date' => $this->element_data['end_date_title'] ?? TemplateHelpers::get_content_as_placeholder( 'end_date_title', esc_html__( 'End Date', 'yaymail' ), $this->is_placeholder ),
            ];
    }

    private function initialize_sample_data() {
        $this->sample_subscription = [
            'id'       => 1,
            'price'    => wc_price( 10 ),
            'end_date' => gmdate( 'm-d-Y' ),
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
        $is_placeholder = $this->is_placeholder;
        if ( $is_placeholder && empty( $this->sample_subscription ) && empty( $this->subscription ) ) {
            esc_html_e( 'The subscription end details does not have data in this order.', 'yaymail' );
        } else {
            $this->render_subscriptions();
        }
    }

    public function render_subscriptions() {
        $style       = $this->get_styles();
        $table_style = $this->get_styles() . 'padding: 0';
        ?>
        <table class="yaymail-enr-subscription-end-details td" cellspacing="0" cellpadding="6" style="<?php echo esc_attr( $table_style ); ?>" border="1" width="100%">

            <thead>
                <tr>
                    <th class="td id-title" scope="col" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $this->titles['id'] ); ?></th>
                    <th class="td price-title" scope="col" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $this->titles['price'] ); ?></th>
                    <th class="td end-date-title" scope="col" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $this->titles['end_date'] ); ?></th>
                </tr>
            </thead>

            <tbody>
                    <?php
                        $is_using_sample = empty( $this->subscription );

                        $subscription_number = $is_using_sample ? $this->sample_subscription['id'] : $this->subscription->get_order_number();

                        $subscription_id = $is_using_sample ? $this->sample_subscription['id'] : $this->subscription->get_id();

                        $subscription_price = $is_using_sample ? $this->sample_subscription['price'] : $this->subscription->get_formatted_order_total();

                        $subscription_end = $is_using_sample ? $this->sample_subscription['end_date'] : date_i18n( wc_date_format(), $this->subscription->get_time( 'end', 'site' ) );
                    ?>
                <tr>
                    <td class="td id-row" scope="row" style="<?php echo esc_attr( $style ); ?>">
                        <a href="<?php echo esc_url( wc_get_endpoint_url( 'view-subscription', $subscription_id, wc_get_page_permalink( 'myaccount' ) ) ); ?>">#<?php echo esc_html( $subscription_number ); ?></a>
                    </td>
                    <td class="td price-row" scope="row" style="<?php echo esc_attr( $style ); ?>">
                        <?php echo wp_kses_post( $subscription_price ); ?>
                    </td>
                    <td class="td end-date-row" scope="row" style="<?php echo esc_attr( $style ); ?>">
                        <?php echo wp_kses_post( $subscription_end ); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }
}
