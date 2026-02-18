<?php

defined( 'ABSPATH' ) || exit;

$text_align  = yaymail_get_text_align();
$margin_side = is_rtl() ? 'left' : 'right';

$order_data     = isset( $args['order'] ) ? $args['order'] : '';
$element_data   = isset( $args['element']['data'] ) ? $args['element']['data'] : [];
$text_style     = isset( $args['text_style'] ) ? $args['text_style'] : '';
$image_style    = isset( $args['styles_product_image'] ) ? $args['styles_product_image'] : '';
$is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;

$yaymail_settings       = yaymail_settings();
$order_items            = $order_data->get_items();
$image_height           = isset( $yaymail_settings['product_image_height'] ) ? $yaymail_settings['product_image_height'] : '30';
$image_width            = isset( $yaymail_settings['product_image_width'] ) ? $yaymail_settings['product_image_width'] : '30';
$image_position         = isset( $yaymail_settings['product_image_position'] ) ? $yaymail_settings['product_image_position'] : 'top';
$show_image             = isset( $yaymail_settings['show_product_image'] ) ? boolval( $yaymail_settings['show_product_image'] ) : false;
$show_sku               = isset( $yaymail_settings['show_product_sku'] ) ? boolval( $yaymail_settings['show_product_sku'] ) : false;
$show_des               = isset( $yaymail_settings['show_product_description'] ) ? boolval( $yaymail_settings['show_product_description'] ) : false;
$show_hyper_links       = isset( $yaymail_settings['show_product_hyper_links'] ) ? boolval( $yaymail_settings['show_product_hyper_links'] ) : false;
$show_regular_price     = isset( $yaymail_settings['show_product_regular_price'] ) ? boolval( $yaymail_settings['show_product_regular_price'] ) : false;
$show_product_item_cost = isset( $yaymail_settings['show_product_item_cost'] ) ? boolval( $yaymail_settings['show_product_item_cost'] ) : false;

$show_purchase_note = true;
$purchase_note      = true;

foreach ( $order_items as $item_id => $item ) :
    $product                = $item->get_product();
    $sku                    = '';
    $purchase_note          = '';
    $image                  = '';
    $short_description      = '';
    $product_name           = '';
    $product_permalink      = '#';
    $product_name_hyperlink = '';
    $product_regular_price  = '';

    if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
        continue;
    }

    if ( is_object( $product ) ) {
        $sku                   = $product->get_sku();
        $purchase_note         = $product->get_purchase_note();
        $image_url             = $product->get_image_id() ? current( wp_get_attachment_image_src( $product->get_image_id(), 'full' ) ) : '#';
        $image                 = $is_placeholder ? "<img width='{{product_image_width}}px' height='{{product_image_height}}px' src='{$image_url}' alt='product image'/>" : $product->get_image( [ $image_width, $image_height ] );
        $short_description     = $product->get_short_description();
        $product_name          = $item->get_name();
        $product_permalink     = method_exists( $product, 'get_permalink' ) ? $product->get_permalink() : '#';
        $product_hyper_link    = "<a href='{$product_permalink}' target='_blank'>{$product_name}</a>";
        $product_regular_price = isset( $product->get_data()['regular_price'] ) ? (float) $product->get_data()['regular_price'] : '';
    }

    ?>
    <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order_data ) ); ?>" style="<?php echo esc_attr( $text_style ); ?>;">
        <td colspan="<?php echo wp_kses_post( apply_filters( 'yaymail_order_item_product_title_colspan', 1, $element_data ) ); ?>" class="td yaymail_item_product_content" style="<?php echo esc_attr( $text_style ); ?>; word-wrap:break-word;">
            <?php
            // Show title/image etc.
            if ( $show_image && 'bottom' !== $image_position || $is_placeholder ) {
                echo wp_kses_post( "<div class='yaymail-product_image_position__top' style='{$image_style}'>" );
                require YAYMAIL_ADDON_WS_PLUGIN_PATH . 'src/templates/shortcodes/subscription-order-details/order-items/image-content.php';
                echo ( '</div>' );
            }
            ?>

            <!-- Product details -->
            <div class='yaymail-product-details'>
            <?php

            // Product name.
            require YAYMAIL_ADDON_WS_PLUGIN_PATH . 'src/templates/shortcodes/subscription-order-details/order-items/product-name-content.php';

            // SKU.
            if ( ( $show_sku && ! empty( $sku ) ) || ( $is_placeholder && ! empty( $sku ) ) ) {
                require YAYMAIL_ADDON_WS_PLUGIN_PATH . 'src/templates/shortcodes/subscription-order-details/order-items/sku-content.php';
            }

            // Product Description.
            if ( ( $show_des && ! empty( $short_description ) ) || ( $is_placeholder && ! empty( $short_description ) ) ) {
                require YAYMAIL_ADDON_WS_PLUGIN_PATH . 'src/templates/shortcodes/subscription-order-details/order-items/product-short-description-content.php';
            }
            // allow other plugins to add additional product information here.
            do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order_data, '' );
            ?>
            <div class="yaymail-wc-item-meta" style="clear:both">
            <?php
                wc_display_item_meta(
                    $item,
                    [
                        'label_before' => '<strong class="wc-item-meta-label yaymail-item-meta-label" style="float: ' . esc_attr( $text_align ) . '; margin-' . esc_attr( $margin_side ) . ': .25em; clear: both">',
                    ]
                );
            ?>
            </div>
            <?php

            // allow other plugins to add additional product information here.
            do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order_data, '' );

            // Show title/image etc in bottom.
            if ( $show_image && 'bottom' === $image_position || $is_placeholder ) {
                echo wp_kses_post( "<div class='yaymail-product_image_position__bottom' style='{$image_style}'>" );
                require YAYMAIL_ADDON_WS_PLUGIN_PATH . 'src/templates/shortcodes/subscription-order-details/order-items/image-content.php';
                echo ( '</div>' );
            }
            ?>
            </div>
            <!-- End Product details -->
        </td>

        <?php
        // Show product item cost
        if ( $show_product_item_cost || $is_placeholder ) {
            ?>
                <td colspan="<?php echo wp_kses_post( apply_filters( 'yaymail_order_item_cost_colspan', 1, $element_data ) ); ?>" class="td yaymail_item_cost_content" style="<?php echo esc_attr( $text_style ); ?>;">
                    <?php echo wp_kses_post( wc_price( $order_data->get_item_subtotal( $item, false, true ), [ 'currency' => $order_data->get_currency() ] ) ); ?>
                </td>
            <?php
        }
        ?>
        <td colspan="<?php echo wp_kses_post( apply_filters( 'yaymail_order_item_quantity_colspan', 1, $element_data ) ); ?>" class="td yaymail_item_quantity_content" style="<?php echo esc_attr( $text_style ); ?>;">
            <?php
            $qty          = $item->get_quantity();
            $refunded_qty = $order_data->get_qty_refunded_for_item( $item_id );

            if ( $refunded_qty ) {
                $qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
            } else {
                $qty_display = esc_html( $qty );
            }
            echo wp_kses_post( apply_filters( 'woocommerce_email_order_item_quantity', $qty_display, $item ) );
            ?>
        </td>
        <td colspan="<?php echo wp_kses_post( apply_filters( 'yaymail_order_item_price_colspan', 1, $element_data ) ); ?>" class="td yaymail_item_price_content" style="<?php echo esc_attr( $text_style ); ?>;">
            <?php
            // Show product regular price.
            if ( ( $show_regular_price && ! empty( $product_regular_price ) ) || ( $is_placeholder && ! empty( $product_regular_price ) ) ) {
                require YAYMAIL_ADDON_WS_PLUGIN_PATH . 'src/templates/shortcodes/subscription-order-details/order-items/product-regular-price-content.php';
            }
            ?>
            <?php echo wp_kses_post( $order_data->get_formatted_line_subtotal( $item ) ); ?>
        </td>
    </tr>
    <?php

    if ( $show_purchase_note && $purchase_note ) {
        ?>
        <tr>
            <td colspan="3" style="<?php echo esc_attr( $text_style ); ?>;">
                <?php
                echo wp_kses_post( wpautop( do_shortcode( $purchase_note ) ) );
                ?>
            </td>
        </tr>
        <?php
    }
    ?>

<?php endforeach; ?>
