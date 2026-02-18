<?php
defined( 'ABSPATH' ) || exit;

use YayMailAddonWcSubscription\Shortcodes\Renderers\WcSubscriptionInformationRenderer;

$is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;

$render_data = isset( $args['render_data'] ) ? $args['render_data'] : [];

$order_data = isset( $render_data['order'] ) ? $render_data['order'] : null;

$element_data = isset( $args['element']['data'] ) ? $args['element']['data'] : [];

$order_details = new WcSubscriptionInformationRenderer( $render_data['order'], $element_data, $is_placeholder, $args );

$order_details->render();
