<?php

use YayMailAddonWcSubscription\Emails\WcEmailNewRenewalOrder;

defined( 'ABSPATH' ) || exit;

$template = WcEmailNewRenewalOrder::get_instance()->template;

if ( ! empty( $template ) ) {
    $content = $template->get_content( $args ); // TODO: process args later.
    yaymail_kses_post_e( $content );
}
