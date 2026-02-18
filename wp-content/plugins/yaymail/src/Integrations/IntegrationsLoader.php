<?php

namespace YayMail\Integrations;

use YayMail\Integrations\AdminAndSiteEnhancements\AdminAndSiteEnhancements;
use YayMail\Utils\SingletonTrait;
use YayMail\Integrations\DHL\DHLIntegration;
use YayMail\Integrations\F4ShippingPhoneAndEmailForWooCommerce\F4ShippingPhoneAndEmailForWooCommerce;

/**
 * IntegrationsLoader
 * * @method static IntegrationsLoader get_instance()
 */
class IntegrationsLoader {
    use SingletonTrait;

    protected function __construct() {
        RankMath::get_instance();
        F4ShippingPhoneAndEmailForWooCommerce::get_instance();
        AdminAndSiteEnhancements::get_instance();
        DHLIntegration::get_instance();
    }
}
