<?php

use YayMailAddonWcSubscription\Emails\WCSEmailCustomerNotificationAutoTrialExpiration;

defined( 'ABSPATH' ) || exit;

$template = WCSEmailCustomerNotificationAutoTrialExpiration::get_instance()->template;

if ( ! empty( $template ) ) {
    $content = $template->get_content( $args ); // TODO: process args later.
    yaymail_kses_post_e( $content );
}
