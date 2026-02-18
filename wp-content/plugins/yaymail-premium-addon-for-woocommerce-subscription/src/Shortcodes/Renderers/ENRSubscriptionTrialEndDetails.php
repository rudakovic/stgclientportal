<?php

namespace YayMailAddonWcSubscription\Shortcodes\Renderers;

use YayMail\Utils\TemplateHelpers;

/**
 * @method: static ENRSubscriptionTrialEndDetails get_instance()
 */
class ENRSubscriptionTrialEndDetails {

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
                'id'             => $this->element_data['id_title'] ?? TemplateHelpers::get_content_as_placeholder( 'id_title', esc_html__( 'Subscription', 'yaymail' ), $this->is_placeholder ),

                'price'          => $this->element_data['price_title'] ?? TemplateHelpers::get_content_as_placeholder( 'price_title', esc_html__( 'Price', 'yaymail' ), $this->is_placeholder ),

                'trial_end_date' => $this->element_data['trial_end_date_title'] ?? TemplateHelpers::get_content_as_placeholder( 'trial_end_date_title', esc_html__( 'Trial End Date', 'yaymail' ), $this->is_placeholder ),
            ];
    }

    private function initialize_sample_data() {
        $this->sample_subscription = [
            'id'             => 1,
            'price'          => wc_price( 10 ),
            'trial_end_date' => gmdate( 'm-d-Y' ),
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
        <table class="yaymail-enr-subscription-trial-end-details td" cellspacing="0" cellpadding="6" style="<?php echo esc_attr( $table_style ); ?>" border="1" width="100%">

            <thead>
                <tr>
                    <th class="td id-title" scope="col" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $this->titles['id'] ); ?></th>
                    <th class="td price-title" scope="col" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $this->titles['price'] ); ?></th>
                    <th class="td trial-end-date-title" scope="col" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $this->titles['trial_end_date'] ); ?></th>
                </tr>
            </thead>

            <tbody>
                    <?php
                        $is_using_sample = empty( $this->subscription );

                        $subscription_number = $is_using_sample ? $this->sample_subscription['id'] : $this->subscription->get_order_number();

                        $subscription_id = $is_using_sample ? $this->sample_subscription['id'] : $this->subscription->get_id();

                        $subscription_price = $is_using_sample ? $this->sample_subscription['price'] : $this->subscription->get_formatted_order_total();

                        $subscription_trial_end = $is_using_sample ? $this->sample_subscription['trial_end_date'] : date_i18n( wc_date_format(), $this->subscription->get_time( 'trial_end', 'site' ) );
                    ?>
                <tr>
                    <td class="td id-row" scope="row" style="<?php echo esc_attr( $style ); ?>">
                        <a href="<?php echo esc_url( wc_get_endpoint_url( 'view-subscription', $subscription_id, wc_get_page_permalink( 'myaccount' ) ) ); ?>">#<?php echo esc_html( $subscription_number ); ?></a>
                    </td>
                    <td class="td price-row" scope="row" style="<?php echo esc_attr( $style ); ?>">
                        <?php echo wp_kses_post( $subscription_price ); ?>
                    </td>
                    <td class="td trial-end-date-row" scope="row" style="<?php echo esc_attr( $style ); ?>">
                        <?php echo wp_kses_post( $subscription_trial_end ); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }
}
