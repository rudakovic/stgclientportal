<?php

namespace YayMailAddonWcSubscription\Elements;

use YayMailAddonWcSubscription\SingletonTrait;

/**
 * Email Loader Class
 *
 * @method static ElementsLoader get_instance()
 */
class ElementsLoader {
    use SingletonTrait;

    private function __construct() {
        add_action( 'yaymail_register_elements', [ $this, 'register_elements' ] );
    }

    public function register_elements( $service ) {
        $dir = new \DirectoryIterator( YAYMAIL_ADDON_WS_PLUGIN_PATH . '/src/Elements' );
        foreach ( $dir as $fileinfo ) {
            if ( ! $fileinfo->isDot() ) {
                $file_name  = $fileinfo->getFilename();
                $class_name = basename( $file_name, '.php' );
                $class      = 'YayMailAddonWcSubscription\\Elements\\' . $class_name;
                if ( __CLASS__ === $class ) {
                    continue;
                }
                if ( class_exists( $class ) ) {
                    $service->register_element( $class::get_instance() );
                }
            }
        }
    }
}
