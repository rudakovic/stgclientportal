<?php
defined( 'ABSPATH' ) || exit;

use YayMailAddonWcSubscription\Shortcodes\Renderers\ENRSubscriptionPriceChangedDetails;

$is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;

$render_data = isset( $args['render_data'] ) ? $args['render_data'] : [];

$price_changed_items_data = isset( $render_data['price_changed_items'] ) ? $render_data['price_changed_items'] : null;

$element_data = isset( $args['element']['data'] ) ? $args['element']['data'] : [];

$order_details = new ENRSubscriptionPriceChangedDetails( $price_changed_items_data, $element_data, $is_placeholder );

$order_details->render();
