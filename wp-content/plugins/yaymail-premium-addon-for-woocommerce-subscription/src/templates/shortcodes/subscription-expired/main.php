<?php
defined( 'ABSPATH' ) || exit;

use YayMailAddonWcSubscription\Shortcodes\Renderers\WcSubscriptionExpiredRenderer;

$is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;

$render_data = isset( $args['render_data'] ) ? $args['render_data'] : [];

$subscription_data = isset( $render_data['subscription'] ) ? $render_data['subscription'] : null;

$element_data = isset( $args['element']['data'] ) ? $args['element']['data'] : [];

$order_details = new WcSubscriptionExpiredRenderer( $subscription_data, $element_data, $is_placeholder );

$order_details->render();
