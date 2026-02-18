<?php
defined( 'ABSPATH' ) || exit;

use YayMailAddonWcSubscription\Shortcodes\Renderers\WcSubscriptionOrderDetailsRenderer;
use YayMail\Utils\TemplateHelpers;

$is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;

$render_data = isset( $args['render_data'] ) ? $args['render_data'] : [];

$order_data = isset( $render_data['order'] ) ? $render_data['order'] : null;

$element_data = isset( $args['element']['data'] ) ? $args['element']['data'] : [];

$is_multi_subscription = isset( $render_data['is_multi_subscription'] ) ? $render_data['is_multi_subscription'] : false;

if ( $is_multi_subscription ) {
    $subscriptions_data = isset( $render_data['subscriptions'] ) ? $render_data['subscriptions'] : null;
    $subscriptions      = $is_placeholder ? [ array_values( $subscriptions_data )[0] ?? null ] : $subscriptions_data;

    $element           = $args['element'];
    $data              = $element['data'];
    $table_title_style = TemplateHelpers::get_style(
        [
            'text-align'  => yaymail_get_text_align(),
            'color'       => isset( $data['title_color'] ) ? $data['title_color'] : 'inherit',
            'margin-top'  => '0',
            'font-size'   => '20px',
            'font-weight' => 'normal',
            'font-family' => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
        ]
    );

    foreach ( $subscriptions as $key => $subscription ) {
        if ( ! $subscription instanceof \WC_Order ) {
            continue;
        }

        $subscription_shortcode = '[yaymail_wc_subscription_id subscription_id=' . $subscription->get_id() . ']';
        if ( ! $is_placeholder ) {
            ?>
                <h2 class="yaymail-ws-subscription-switch-order-details__title" style="<?php echo esc_attr( $table_title_style ); ?>" > <?php echo wp_kses_post( do_shortcode( str_replace( '[yaymail_wc_subscription_id]', $subscription_shortcode, $data['title'] ) ) ); ?> </h2>
            <?php
        }
        $order_details = new WcSubscriptionOrderDetailsRenderer( $subscription, $element_data, $is_placeholder, $is_subscription_switch_order = true );
        $order_details->render();
        echo '<br>';
    }
} else {
    $order_details = new WcSubscriptionOrderDetailsRenderer( $order_data, $element_data, $is_placeholder );
    $order_details->render();
}

