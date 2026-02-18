<?php

use YayMailAddonWcSubscription\Emails\WcEmailExpiredSubscription;

defined( 'ABSPATH' ) || exit;

$template = WcEmailExpiredSubscription::get_instance()->template;

if ( ! empty( $template ) ) {
    $content = $template->get_content( $args ); // TODO: process args later.
    yaymail_kses_post_e( $content );
}
