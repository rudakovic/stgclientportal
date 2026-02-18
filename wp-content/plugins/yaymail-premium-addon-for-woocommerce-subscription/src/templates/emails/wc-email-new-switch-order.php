<?php

use YayMailAddonWcSubscription\Emails\WcEmailNewSwitchOrder;

defined( 'ABSPATH' ) || exit;

$template = WcEmailNewSwitchOrder::get_instance()->template;

if ( ! empty( $template ) ) {
    $content = $template->get_content( $args ); // TODO: process args later.
    yaymail_kses_post_e( $content );
}
