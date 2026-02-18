<?php

namespace YayMailAddonWcSubscription\Shortcodes\Renderers;

use YayMail\Utils\TemplateHelpers;

/**
 * @method: static ENRSubscriptionPriceChangedDetails get_instance()
 */
class ENRSubscriptionPriceChangedDetails {

    public $item_totals = [];

    public $element_data = null;

    public $is_placeholder = false;

    public $titles = [];

    public $show_product_item_cost = false;

    private $price_changed_items = [ false ];

    private $sample_price_changed_items = [];


    public function __construct( $price_changed_items, $element_data, $is_placeholder ) {
        $yaymail_settings     = yaymail_settings();
        $this->element_data   = $element_data;
        $this->is_placeholder = $is_placeholder;
        $this->initialize_titles();

        if ( empty( $price_changed_items ) ) {
            $this->initialize_sample_data();
        } else {
            $this->price_changed_items = $price_changed_items;
        }
    }

    public function initialize_titles() {
            $this->titles = [
                'new_price' => $this->element_data['new_price_title'] ?? TemplateHelpers::get_content_as_placeholder( 'new_price_title', esc_html__( 'New Price', 'yaymail' ), $this->is_placeholder ),
                'old_price' => $this->element_data['old_price_title'] ?? TemplateHelpers::get_content_as_placeholder( 'old_price_title', esc_html__( 'Price', 'yaymail' ), $this->is_placeholder ),
            ];
    }

    private function initialize_sample_data() {
        $this->sample_price_changed_items = [
            'new_price' => wc_price( 5 ),
            'old_price' => wc_price( 10 ),
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
        <table class="yaymail-enr-subscription-change-price-details td" cellspacing="0" cellpadding="6" style="<?php echo esc_attr( $table_style ); ?>" border="1" width="100%">
            <thead>
                <tr>
                    <th class="td new-price-title" scope="col" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $this->titles['new_price'] ); ?></th>
                    <th class="td old-price-title" scope="col" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $this->titles['old_price'] ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ( $this->price_changed_items as $changed ) {

                    $is_using_sample = empty( $changed );

                    $new_price = $is_using_sample ? $this->sample_price_changed_items['new_price'] : $changed['to_string'];

                    $old_price = $is_using_sample ? $this->sample_price_changed_items['old_price'] : $changed['from_string'];
                    ?>
                    <tr>
                        <td class="td new-price-row" scope="row" style="<?php echo esc_attr( $style ); ?>">
                            <?php echo wp_kses_post( $new_price ); ?>
                        </td>
                        <td class="td old-price-row" scope="row" style="<?php echo esc_attr( $style ); ?>">
                            <?php echo wp_kses_post( $old_price ); ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php
    }
}
