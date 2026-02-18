<?php

namespace YayMailAddonWcSubscription\Shortcodes;

use YayMail\Abstracts\BaseShortcode;
use YayMail\Utils\Helpers;
use YayMailAddonWcSubscription\SingletonTrait;

/**
 * @method: static WcSubscriptionCustomerRenewalInvoiceShortcodes get_instance()
 */
class WcSubscriptionCustomerRenewalInvoiceShortcodes extends BaseShortcode {

    use SingletonTrait;

    public $available_email_ids = [
        'customer_renewal_invoice',
    ];

    public function get_shortcodes() {
        $shortcodes   = [];
        $shortcodes[] = [
            'name'        => 'yaymail_wc_subscription_renewal_invoice_text_detail',
            'description' => __( 'Renewal Invoice Text', 'yaymail' ),
            'group'       => 'woocommerce_subscription',
            'callback'    => [ $this, 'yaymail_wc_subscription_renewal_invoice_text_detail' ],
        ];

        return $shortcodes;
    }

    public function yaymail_wc_subscription_renewal_invoice_text_detail( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        $is_placeholder = isset( $data['is_placeholder'] ) ? $data['is_placeholder'] : false;

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            ob_start();
            ?>
                <?php
                echo wp_kses(
                    sprintf(
                    // translators: %1$s: name of the blog, %2$s: link to checkout payment url, note: no full stop due to url at the end
                        _x( 'An order has been created for you to renew your subscription on %1$s. To pay for this invoice please use the following link: %2$s', 'In customer renewal invoice email', 'woocommerce-subscriptions' ),
                        esc_html( get_bloginfo( 'name' ) ),
                        '<a href="#">' . esc_html__( 'Pay Now &raquo;', 'woocommerce-subscriptions' ) . '</a>'
                    ),
                    [ 'a' => [ 'href' => true ] ]
                );
                ?>
            <?php

            $html = ob_get_contents();
            ob_end_clean();
            return $html;
        }

        $order = '';

        if ( $is_placeholder ) {
            $order = Helpers::get_order_from_shortcode_data( $render_data );
        } else {
            $order = $render_data['order'] ?? null;
        }

        if ( empty( $order ) ) {
            /**
             * Not having subscription
             */
            return '';
        }

        ob_start();

        if ( $is_placeholder ) {
            ?>
                <?php
                    echo wp_kses(
                        sprintf(
                        // translators: %1$s: name of the blog, %2$s: link to checkout payment url, note: no full stop due to url at the end
                            _x( 'An order has been created for you to renew your subscription on %1$s. To pay for this invoice please use the following link: %2$s', 'In customer renewal invoice email', 'woocommerce-subscriptions' ),
                            esc_html( get_bloginfo( 'name' ) ),
                            '<a href="' . esc_url( $order->get_checkout_payment_url() ) . '">' . esc_html__( 'Pay Now &raquo;', 'woocommerce-subscriptions' ) . '</a>'
                        ),
                        [ 'a' => [ 'href' => true ] ]
                    );
                ?>
            <?php
        } else {
            if ( $order->has_status( 'pending' ) ) :
                ?>
                <?php
                    echo wp_kses(
                        sprintf(
                        // translators: %1$s: name of the blog, %2$s: link to checkout payment url, note: no full stop due to url at the end
                            _x( 'An order has been created for you to renew your subscription on %1$s. To pay for this invoice please use the following link: %2$s', 'In customer renewal invoice email', 'woocommerce-subscriptions' ),
                            esc_html( get_bloginfo( 'name' ) ),
                            '<a href="' . esc_url( $order->get_checkout_payment_url() ) . '">' . esc_html__( 'Pay Now &raquo;', 'woocommerce-subscriptions' ) . '</a>'
                        ),
                        [ 'a' => [ 'href' => true ] ]
                    );
                ?>
                <?php elseif ( $order->has_status( 'failed' ) ) : ?>
                    <?php
                        echo wp_kses(
                            sprintf(
                            // translators: %1$s: name of the blog, %2$s: link to checkout payment url, note: no full stop due to url at the end
                                _x( 'The automatic payment to renew your subscription with %1$s has failed. To reactivate the subscription, please log in and pay for the renewal from your account page: %2$s', 'In customer renewal invoice email', 'woocommerce-subscriptions' ),
                                esc_html( get_bloginfo( 'name' ) ),
                                '<a href="' . esc_url( $order->get_checkout_payment_url() ) . '">' . esc_html__( 'Pay Now &raquo;', 'woocommerce-subscriptions' ) . '</a>'
                            ),
                            [ 'a' => [ 'href' => true ] ]
                        );
                    ?>
            <?php endif; ?>    
            <?php
        }

        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }


}
