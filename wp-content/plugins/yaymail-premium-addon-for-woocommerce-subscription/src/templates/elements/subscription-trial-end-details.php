<?php
defined( 'ABSPATH' ) || exit;

use YayMail\Utils\TemplateHelpers;

use YayMail\Utils\Helpers;

/**
 * $args includes
 * $element
 * $render_data
 * $is_nested
 */
if ( empty( $args['element'] ) ) {
    return;
}

$yaymail_settings = yaymail_settings();

$element       = $args['element'];
$data          = $element['data'];
$template_name = isset( $args['template']->get_data()['name'] ) ? $args['template']->get_data()['name'] : '';
$render_data   = isset( $args['render_data'] ) ? $args['render_data'] : '';
$is_sample     = isset( $render_data['is_sample'] ) ? $render_data['is_sample'] : false;

$subscription = '';

if ( isset( $render_data['subscription'] ) ) {
    $subscription = $render_data['subscription'];
} else {
    $order_data = Helpers::get_order_from_shortcode_data( $render_data );

    if ( $order_data instanceof \WC_Order ) {
        $arr_subscription = wcs_get_subscriptions_for_order( $order_data->get_id() );

        $subscription = array_values( $arr_subscription )[0] ?? null;
    }
}

$wrapper_style = TemplateHelpers::get_style(
    [
        'word-break'       => 'break-word',
        'background-color' => $data['background_color'],
        'padding'          => TemplateHelpers::get_spacing_value( isset( $data['padding'] ) ? $data['padding'] : [] ),
    ]
);

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

if ( ! empty( $subscription ) || $is_sample ) {
    ob_start();
    ?>
    <h2 class="yaymail-enr-subscription-trial-end-details__title" style="<?php echo esc_attr( $table_title_style ); ?>"> <?php echo wp_kses_post( do_shortcode( $data['main_title'] ) ); ?> </h2>
    <?php
    $element_content = ob_get_contents();
    ob_end_clean();
    $element_content .= do_shortcode( $data['rich_text'] ?? '' );

    TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_style );
}
