<?php

namespace YayMailAddonWcSubscription\Shortcodes\Renderers;

use YayMail\Utils\TemplateHelpers;

/**
 * @method: static WcSubscriptionOrderDetailsRenderer get_instance()
 */
class WcSubscriptionOrderDetailsRenderer {

    public $item_totals = [];

    public $order = null;

    public $order_note = '';

    public $element_data = null;

    public $is_placeholder = false;

    public $is_subscription_switch_order = false;

    public $titles = [];

    public $show_product_item_cost = false;

    public $colspan_value = '2';

    public function __construct( $order, $element_data, $is_placeholder, $is_subscription_switch_order = false ) {
        $yaymail_settings                   = yaymail_settings();
        $this->show_product_item_cost       = isset( $yaymail_settings['show_product_item_cost'] ) ? boolval( $yaymail_settings['show_product_item_cost'] ) : false;
        $this->colspan_value                = $is_placeholder ? '{{show_product_item_cost}}' : ( $this->show_product_item_cost ? '3' : '2' );
        $this->element_data                 = $element_data;
        $this->is_placeholder               = $is_placeholder;
        $this->is_subscription_switch_order = $is_subscription_switch_order;
        $this->initialize_titles();

        if ( ! $order instanceof \WC_Order ) {
            $this->initialize_sample_data();
        } else {
            $this->initialize_order_data( $order );
        }
    }

    public function initialize_titles() {
        $this->titles = [
            'product'        => isset( $this->element_data['product_title'] ) ? $this->element_data['product_title'] : TemplateHelpers::get_content_as_placeholder( 'product_title', esc_html__( 'Product', 'woocommerce' ), $this->is_placeholder ),
            'cost'           => isset( $this->element_data['cost_title'] ) ? $this->element_data['cost_title'] : TemplateHelpers::get_content_as_placeholder( 'cost_title', esc_html__( 'Cost', 'woocommerce' ), $this->is_placeholder ),
            'quantity'       => isset( $this->element_data['quantity_title'] ) ? $this->element_data['quantity_title'] : TemplateHelpers::get_content_as_placeholder( 'quantity_title', esc_html__( 'Quantity', 'woocommerce' ), $this->is_placeholder ),
            'price'          => isset( $this->element_data['price_title'] ) ? $this->element_data['price_title'] : TemplateHelpers::get_content_as_placeholder( 'price_title', esc_html__( 'Price', 'woocommerce' ), $this->is_placeholder ),
            'cart_subtotal'  => isset( $this->element_data['cart_subtotal_title'] ) ? $this->element_data['cart_subtotal_title'] : TemplateHelpers::get_content_as_placeholder( 'cart_subtotal_title', esc_html__( 'Subtotal:', 'woocommerce' ), $this->is_placeholder ),
            'shipping'       => isset( $this->element_data['shipping_title'] ) ? $this->element_data['shipping_title'] : TemplateHelpers::get_content_as_placeholder( 'shipping_title', esc_html__( 'Shipping:', 'woocommerce' ), $this->is_placeholder ),
            'discount'       => isset( $this->element_data['discount_title'] ) ? $this->element_data['discount_title'] : TemplateHelpers::get_content_as_placeholder( 'discount_title', esc_html__( 'Discount:', 'woocommerce' ), $this->is_placeholder ),
            'payment_method' => isset( $this->element_data['payment_method_title'] ) ? $this->element_data['payment_method_title'] : TemplateHelpers::get_content_as_placeholder( 'payment_method_title', esc_html__( 'Payment method:', 'woocommerce' ), $this->is_placeholder ),
            'order_total'    => isset( $this->element_data['order_total_title'] ) ? $this->element_data['order_total_title'] : TemplateHelpers::get_content_as_placeholder( 'order_total_title', esc_html__( 'Total:', 'woocommerce' ), $this->is_placeholder ),
            'order_note'     => isset( $this->element_data['order_note_title'] ) ? $this->element_data['order_note_title'] : TemplateHelpers::get_content_as_placeholder( 'order_note_title', esc_html__( 'Note:', 'woocommerce' ), $this->is_placeholder ),
        ];
    }

    private function initialize_sample_data() {
        $this->item_totals = [
            'cart_subtotal'  => [
                'label' => $this->titles['cart_subtotal'],
                'value' => wc_price( 18 ),
            ],
            'shipping'       => [
                'label' => $this->titles['shipping'],
                'value' => __( 'Free shipping', 'yaymail' ),
            ],
            'payment_method' => [
                'label' => $this->titles['payment_method'],
                'value' => __( 'Direct bank transfer', 'yaymail' ),
            ],
            'order_total'    => [
                'label' => $this->titles['order_total'],
                'value' => wc_price( 18 ),
            ],
        ];
        $this->order_note  = 'YayMail';
    }

    private function initialize_order_data( $order ) {
        $this->item_totals = $order->get_order_item_totals();
        $this->order_note  = $order->get_customer_note();
        $this->order       = $order;
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

    public function get_styles_product_image() {
        return TemplateHelpers::get_style(
            [
                'margin-bottom' => '5px',
                'margin-right'  => '5px',
            ]
        );
    }

    public function render() {
        $style = $this->get_styles() . 'padding: 0';
        $class = $this->is_subscription_switch_order ? 'yaymail-ws-subscription-switch-order-details' : 'yaymail-ws-subscription-order-details';
        ?>
        <table class="<?php echo esc_attr( "yaymail-order-details-table td $class " ); ?>" cellspacing="0" cellpadding="6" width="100%" style="<?php echo esc_attr( $style ); ?>" border="1">
            <?php
            $this->render_heading();
            $this->render_order_items();
            $this->render_footer();
            ?>
        </table>
        <?php
    }

    public function render_heading() {
        $product_col_span  = apply_filters( 'yaymail_order_item_product_title_colspan', 1, $this->element_data );
        $cost_col_span     = apply_filters( 'yaymail_order_item_cost_colspan', 1, $this->element_data );
        $quantity_col_span = apply_filters( 'yaymail_order_item_quantity_colspan', 1, $this->element_data );
        $price_col_span    = apply_filters( 'yaymail_order_item_price_colspan', 1, $this->element_data );
        $styles            = $this->get_styles();
        $product_col_style = TemplateHelpers::get_style(
            [
                'max-width' => '55%',
                'width'     => '45%',
            ]
        );
        ?>
        <thead class="yaymail_element_head_order_details yaymail_element_head_order_item">
            <tr>
                <th class="td yaymail_item_product_title" colspan="<?php echo wp_kses_post( $product_col_span ); ?>"  scope="col" style="<?php echo esc_attr( $styles . $product_col_style ); ?>;"><?php echo esc_html( $this->titles['product'] ); ?></th>
                <?php if ( $this->show_product_item_cost || $this->is_placeholder ) : ?>
                    <th class="td yaymail_item_price_per_item" colspan="<?php echo esc_attr( $cost_col_span ); ?>" scope="col" style="<?php echo esc_attr( $styles ); ?>"><?php echo esc_html( $this->titles['cost'] ); ?></th>
                <?php endif; ?>
                <th class="td yaymail_item_quantity_title" colspan="<?php echo wp_kses_post( $quantity_col_span ); ?>" scope="col" style="<?php echo esc_attr( $styles ); ?>;"><?php echo esc_html( $this->titles['quantity'] ); ?></th>
                <th class="td yaymail_item_price_title" colspan="<?php echo wp_kses_post( $price_col_span ); ?>" scope="col" style="<?php echo esc_attr( $styles ); ?>;"><?php echo esc_html( $this->titles['price'] ); ?></th>
            </tr>
        </thead>
        <?php
    }

    public function render_order_items() {

        ?>
        <tbody class="yaymail_element_body_order_details yaymail_element_body_order_item">
            <?php
            if ( null === $this->order ) {
                $this->render_sample_items();
            } else {
                $this->render_real_items();
            }
            ?>
        </tbody>
        <?php
    }

    public function render_sample_items() {
        $style = $this->get_styles();
        ?>
        <tr class="order_item">
            <td colspan="<?php echo wp_kses_post( apply_filters( 'yaymail_order_item_product_title_colspan', 1, $this->element_data ) ); ?>" class="td yaymail_item_product_content" scope="row" style="<?php echo esc_attr( $style ); ?>;">
                <?php esc_html_e( 'Happy YayCommerce', 'yaymail' ); ?>
            </td>
            <?php if ( $this->show_product_item_cost || $this->is_placeholder ) : ?>
                <td colspan="<?php echo wp_kses_post( apply_filters( 'yaymail_order_item_cost_colspan', 1, $this->element_data ) ); ?>" class="td yaymail_item_cost_content" scope="row" style="<?php echo esc_attr( $style ); ?>;">
                    <?php echo wp_kses_post( wc_price( 9 ) ); ?>
                </td>
            <?php endif; ?>
            <td colspan="<?php echo wp_kses_post( apply_filters( 'yaymail_order_item_quantity_colspan', 1, $this->element_data ) ); ?>" class="td yaymail_item_quantity_content" scope="row" style="<?php echo esc_attr( $style ); ?>;">
                <?php esc_html_e( '2', 'yaymail' ); ?>
            </td>
            <td colspan="<?php echo wp_kses_post( apply_filters( 'yaymail_order_item_price_colspan', 1, $this->element_data ) ); ?>" class="td yaymail_item_price_content" scope="row" style="<?php echo esc_attr( $style ); ?>;">
                <?php echo wp_kses_post( wc_price( 18 ) ); ?>
            </td>
        </tr>
        <?php
    }

    public function render_real_items() {
        $style_image_position_left = TemplateHelpers::get_style(
            [
                'float' => 'left',
            ]
        );

        $args_data = [
            'order'                => $this->order,
            'text_style'           => $this->get_styles(),
            'styles_product_image' => isset( yaymail_settings()['product_image_position'] ) & 'left' === yaymail_settings()['product_image_position'] ? $this->get_styles_product_image() . $style_image_position_left : $this->get_styles_product_image(),
            'is_placeholder'       => $this->is_placeholder,
        ];

        // Just has data when send mail
        if ( isset( $args['element'] ) && ! empty( $args['element'] ) ) {
            $args_data['element'] = $args['element'];
        }
        $path_data    = apply_filters( 'yaymail_order_details_items', 'src/templates/shortcodes/subscription-order-details/order-items/main.php' );
        $html         = yaymail_get_content( $path_data, $args_data, YAYMAIL_ADDON_WS_PLUGIN_PATH );
        $allowed_html = TemplateHelpers::wp_kses_allowed_html();
        echo wp_kses( $html, $allowed_html );
    }

    public function render_footer() {
        // TODO: change class name
        ?>
        <tfoot class="yaymail_element_foot_order_details yaymail_element_foot_order_item">
            <?php
            $this->render_item_totals();

            if ( ! empty( $this->order ) && $this->order->get_customer_note() ) {
                $this->render_customer_note();
            }
            ?>
        </tfoot>
        <?php
    }

    public function render_item_totals() {
        $index = 0;
        foreach ( $this->item_totals as $key => $total ) {
            $index++;
            $tr_class              = "yaymail-order-detail-row-{$key}";
            $can_apply_placeholder = $this->is_placeholder && isset( $this->titles[ $key ] );
            $label                 = TemplateHelpers::get_content_as_placeholder( "{$key}_title", esc_html( $total['label'] ), $can_apply_placeholder );
            $style                 = TemplateHelpers::get_style(
                [
                    'border-top-width' => 1 === $index ? '4px' : '0',
                ]
            ) . $this->get_styles();
            ?>
            <tr class="<?php echo esc_attr( $tr_class ); ?>">
                <th class="td" scope="row" colspan="<?php echo esc_attr( $this->colspan_value ); ?>" style="<?php echo esc_attr( $style ); ?>"><?php echo wp_kses_post( $label ); ?></th>
                <td class="td" style="<?php echo esc_attr( $style ); ?>"><?php echo wp_kses_post( $total['value'] ); ?></td>
            </tr>
            <?php
        }
    }

    public function render_customer_note() {
        if ( ! empty( $this->order_note ) ) :
            $style = $this->get_styles();
            ?>
        <tr class="yaymail-order-detail-row-order_note">
            <th class="td" scope="row" colspan="<?php echo esc_attr( $this->colspan_value ); ?>" style="<?php echo esc_attr( $style ); ?>;"><?php echo esc_html( $this->titles['order_note'] ); ?></th>
            <td class="td" style="<?php echo esc_attr( $style ); ?>;"><?php echo wp_kses_post( nl2br( wptexturize( $this->order_note ) ) ); ?></td>
        </tr>
            <?php
        endif;
    }

}
