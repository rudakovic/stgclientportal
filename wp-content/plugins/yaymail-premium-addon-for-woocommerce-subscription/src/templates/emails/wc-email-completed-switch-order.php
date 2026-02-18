<?php

use YayMailAddonWcSubscription\Emails\WcEmailCompletedSwitchOrder;

defined( 'ABSPATH' ) || exit;

$template = WcEmailCompletedSwitchOrder::get_instance()->template;

if ( ! empty( $template ) ) {
    $content = $template->get_content( $args ); // TODO: process args later.
    yaymail_kses_post_e( $content );
}
