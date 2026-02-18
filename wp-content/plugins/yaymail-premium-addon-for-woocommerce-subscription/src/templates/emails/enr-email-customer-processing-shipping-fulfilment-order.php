<?php

use YayMailAddonWcSubscription\Emails\ENREmailCustomerProcessingShippingFulfilmentOrder;

defined( 'ABSPATH' ) || exit;

$template = ENREmailCustomerProcessingShippingFulfilmentOrder::get_instance()->template;

if ( ! empty( $template ) ) {
    $content = $template->get_content( $args ); // TODO: process args later.
    yaymail_kses_post_e( $content );
}
