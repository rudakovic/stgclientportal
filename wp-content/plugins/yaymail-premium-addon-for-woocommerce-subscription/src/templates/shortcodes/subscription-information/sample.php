<?php
defined( 'ABSPATH' ) || exit;

use YayMailAddonWcSubscription\Shortcodes\Renderers\WcSubscriptionInformationRenderer;

$is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;

$element_data = isset( $args['element']['data'] ) ? $args['element']['data'] : [];

$subscription_information_renderer = new WcSubscriptionInformationRenderer( null, $element_data, $is_placeholder, $args );

$subscription_information_renderer->render();
