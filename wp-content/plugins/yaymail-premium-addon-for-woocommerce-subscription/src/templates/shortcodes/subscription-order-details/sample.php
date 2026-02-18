<?php
defined( 'ABSPATH' ) || exit;

use YayMailAddonWcSubscription\Shortcodes\Renderers\WcSubscriptionOrderDetailsRenderer;

$is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;

$element_data = isset( $args['element']['data'] ) ? $args['element']['data'] : [];

$order_details = new WcSubscriptionOrderDetailsRenderer( null, $element_data, $is_placeholder );

$order_details->render();
