<?php
defined( 'ABSPATH' ) || exit;

use YayMailAddonWcSubscription\Shortcodes\Renderers\WcSubscriptionCancelledRenderer;

$is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;

$element_data = isset( $args['element']['data'] ) ? $args['element']['data'] : [];

$subscription_information_renderer = new WcSubscriptionCancelledRenderer( null, $element_data, $is_placeholder );

$subscription_information_renderer->render();
