<?php

namespace YayMailAddonWcSubscription\Migrations\Versions;

use YayMailAddonWcSubscription\SingletonTrait;
use YayMail\Migrations\AbstractMigration;


/**
 * Wc_Subscription_Ver_3_0_0 Class
 *
 * @method static Wc_Subscription_Ver_3_0_0 get_instance()
 */
final class Wc_Subscription_Ver_3_0_0 extends AbstractMigration {
    use SingletonTrait;

    private function __construct() {
        parent::__construct( '2.9.9', '4.0', 'YayMailAddonWcSubscription' );
    }

    protected function up() {
        global $wpdb;
        $template_posts_query = "
            SELECT * 
            FROM {$wpdb->posts}
            WHERE post_type = 'yaymail_template'
        ";
        $template_posts       = $wpdb->get_results( $template_posts_query );
        if ( empty( $template_posts ) ) {
            $this->logger->log( 'There is no template to be migrated' );
            return;
        }
        foreach ( $template_posts as $template ) {
            $template_id = $template->ID;

            $elements = get_post_meta( $template_id, \YayMail\YayMailTemplate::META_KEYS['elements'], true );

            $this->migrate_elements( $elements, $template );

            update_post_meta( $template_id, \YayMail\YayMailTemplate::META_KEYS['elements'], $elements );
            $this->may_mark_template_as_v4_supported( $template_id, \YayMailAddonWcSubscription\Migrations\WcSubscriptionMigration::get_instance() );

        }
    }

    private function migrate_elements( &$elements, $template ) {
        foreach ( $elements as $key => &$element ) {
            if ( isset( $element['children'] ) ) {
                $this->migrate_elements( $element['children'], $template );
            }

            if ( $element['type'] === 'AddonWooSubscriptionInformation' ) {
                $element['name']               = __( 'Subscription Information', 'yaymail' );
                $element['type']               = 'addon_ws_subscription_information';
                $element['data']['main_title'] = __( 'Subscription information', 'yaymail' );
                $element['data']['rich_text']  = '[yaymail_wc_subscription_information]';

                $element['data']['id_title']              = $this->get_value_or_default( $element, 'titleID', __( 'ID', 'woocommerce' ) );
                $element['data']['start_date_title']      = $this->get_value_or_default( $element, 'titleStartDate', __( 'Start Date', 'woocommerce' ) );
                $element['data']['end_date_title']        = $this->get_value_or_default( $element, 'titleEndDate', __( 'End Date', 'woocommerce' ) );
                $element['data']['recurring_total_title'] = $this->get_value_or_default( $element, 'titleRecurringTotal', __( 'Recurring Total', 'woocommerce' ) );
            }

            if ( $element['type'] === 'AddonWooSubscriptionCancelled' ) {
                $element['name']               = __( 'Subscription Cancelled', 'yaymail' );
                $element['type']               = 'addon_ws_subscription_cancelled';
                $element['data']['main_title'] = __( 'Subscription cancelled', 'yaymail' );
                $element['data']['rich_text']  = '[yaymail_wc_subscription_cancelled]';

                $element['data']['id_title']          = $this->get_value_or_default( $element, 'titleSubscription', __( 'Subscription', 'woocommerce' ) );
                $element['data']['price_title']       = $this->get_value_or_default( $element, 'titlePrice', __( 'Price', 'woocommerce' ) );
                $element['data']['last_date_title']   = $this->get_value_or_default( $element, 'titleLastOrderDate', __( 'Last Order Date', 'woocommerce' ) );
                $element['data']['end_prepaid_title'] = $this->get_value_or_default( $element, 'titleEndOfPrepaidTerm', __( 'End of Prepaid Term', 'woocommerce' ) );
            }

            if ( $element['type'] === 'AddonWooSubscriptionExpired' ) {
                $element['name']               = __( 'Subscription Expired', 'yaymail' );
                $element['type']               = 'addon_ws_subscription_expired';
                $element['data']['main_title'] = __( 'Subscription expired', 'yaymail' );
                $element['data']['rich_text']  = '[yaymail_wc_subscription_expired]';

                $element['data']['id_title']        = $this->get_value_or_default( $element, 'titleSubscription', __( 'Subscription', 'woocommerce' ) );
                $element['data']['price_title']     = $this->get_value_or_default( $element, 'titlePrice', __( 'Price', 'woocommerce' ) );
                $element['data']['last_date_title'] = $this->get_value_or_default( $element, 'titleLastOrderDate', __( 'Last Order Date', 'woocommerce' ) );
                $element['data']['end_date_title']  = $this->get_value_or_default( $element, 'titleEndDate', __( 'End Date', 'woocommerce' ) );
            }

            if ( $element['type'] === 'AddonWooSubscriptionNewDetails' || $element['type'] === 'AddonWooSubscriptionDetails' ) {
                $element['name']              = __( 'Subscription Order Details', 'yaymail' );
                $element['type']              = 'addon_ws_subscription_order_details';
                $element['data']['title']     = '[subscription #[yaymail_wc_subscription_id]]';
                $element['data']['rich_text'] = '[yaymail_wc_subscription_order_details]';

                $element['data']['product_title']        = $this->get_value_or_default( $element, 'titleProduct', __( 'Product', 'woocommerce' ) );
                $element['data']['cost_title']           = __( 'Cost', 'woocommerce' );
                $element['data']['quantity_title']       = $this->get_value_or_default( $element, 'titleQuantity', __( 'Quantity', 'woocommerce' ) );
                $element['data']['price_title']          = $this->get_value_or_default( $element, 'titlePrice', __( 'Price', 'woocommerce' ) );
                $element['data']['cart_subtotal_title']  = $this->get_value_or_default( $element, 'titleSubtotal', __( 'Subtotal', 'woocommerce' ) );
                $element['data']['payment_method_title'] = $this->get_value_or_default( $element, 'titlePaymentMethod', __( 'Payment method', 'woocommerce' ) );
                $element['data']['order_total_title']    = $this->get_value_or_default( $element, 'titleTotal', __( 'Total', 'woocommerce' ) );
                $element['data']['order_note_title']     = __( 'Note title', 'woocommerce' );
                $element['data']['shipping_title']       = $this->get_value_or_default( $element, 'titleShipping', __( 'Shipping', 'woocommerce' ) );
                $element['data']['discount_title']       = $this->get_value_or_default( $element, 'titleDiscount', __( 'Discount', 'woocommerce' ) );
            }

            if ( $element['type'] === 'AddonWooSubscriptionSuspended' ) {
                $element['name']               = __( 'Subscription Suspended', 'yaymail' );
                $element['type']               = 'addon_ws_subscription_suspended';
                $element['data']['main_title'] = __( 'Subscription Suspended', 'yaymail' );
                $element['data']['rich_text']  = '[yaymail_wc_subscription_suspended]';

                $element['data']['id_title']             = $this->get_value_or_default( $element, 'titleSubscription', __( 'Subscription', 'woocommerce' ) );
                $element['data']['price_title']          = $this->get_value_or_default( $element, 'titlePrice', __( 'Price', 'woocommerce' ) );
                $element['data']['last_date_title']      = $this->get_value_or_default( $element, 'titleLastOrderDate', __( 'Last Order Date', 'woocommerce' ) );
                $element['data']['date_suspended_title'] = $this->get_value_or_default( $element, 'titleDateSuspended', __( 'Date Suspended', 'woocommerce' ) );
            }

            if ( $element['type'] === 'AddonWooSubscriptionCustomerExpiryReminder' ) {
                $element['name']               = __( 'Subscription End Details', 'yaymail' );
                $element['type']               = 'addon_enr_subscription_end_details';
                $element['data']['main_title'] = __( 'Subscription Trial End Details', 'yaymail' );
                $element['data']['rich_text']  = '[yaymail_enr_subscription_end_details]';

                $element['data']['id_title']       = $this->get_value_or_default( $element, 'titleSubscription', __( 'Subscription', 'woocommerce' ) );
                $element['data']['price_title']    = $this->get_value_or_default( $element, 'titlePrice', __( 'Price', 'woocommerce' ) );
                $element['data']['end_date_title'] = $this->get_value_or_default( $element, 'titleEndDate', __( 'End Date', 'woocommerce' ) );
            }

            if ( $element['type'] === 'AddonWooSubscriptionPriceUpdated' ) {
                $element['name']               = __( 'Subscription Price Changed Details', 'yaymail' );
                $element['type']               = 'addon_enr_subscription_end_details';
                $element['data']['main_title'] = __( 'Subscription Price Changed Details', 'yaymail' );
                $element['data']['rich_text']  = '[yaymail_enr_subscription_price_changed_details]';

                $element['data']['new_price_title'] = $this->get_value_or_default( $element, 'titleNewPrice', __( 'New Price', 'woocommerce' ) );
                $element['data']['old_price_title'] = $this->get_value_or_default( $element, 'titleOldPrice', __( 'Old Price', 'woocommerce' ) );
            }

            if ( $element['type'] === 'AddonWooSubscriptionTrialEndingReminder' ) {
                $element['name']               = __( 'Subscription Trial End Details', 'yaymail' );
                $element['type']               = 'addon_enr_subscription_trial_end_details';
                $element['data']['main_title'] = __( 'Subscription Trial End Details', 'yaymail' );
                $element['data']['rich_text']  = '[yaymail_enr_subscription_trial_end_details]';

                $element['data']['id_title']       = $this->get_value_or_default( $element, 'titleSubscription', __( 'Subscription', 'woocommerce' ) );
                $element['data']['price_title']    = $this->get_value_or_default( $element, 'titlePrice', __( 'Price', 'woocommerce' ) );
                $element['data']['end_date_title'] = $this->get_value_or_default( $element, 'titleEndDate', __( 'End Date', 'woocommerce' ) );
            }
        }//end foreach

        foreach ( $elements as $key => &$element ) {
            $this->migrate_shortcodes( $element['data'] );
        }
    }

    private function migrate_shortcodes( &$data ) {
        foreach ( $data as $key => &$value ) {
            if ( is_array( $value ) ) {
                $this->migrate_shortcodes( $value );
                continue;
            }

            $shortcodes_map = [
                '[yaymail_addon_subscription_retry_time]' => '[yaymail_wc_subscription_get_human_time_diff]',
                '[yaymail_addon_subscription_order_url]'  => '[yaymail_wc_subscription_order_url]',
                '[yaymail_addon_subscription_order_number]' => '[yaymail_wc_subscription_order_number]',
                '[yaymail_addon_subscription_time_next_payment]' => '[yaymail_wc_subscription_next_payment]',
                '[yaymail_addon_subscription_shipping_cycle_string]' => '[yaymail_enr_subscription_shipping_cycle_string]',
                '[yaymail_addon_subscription_time_til_event]' => '[yaymail_wc_subscription_time_til_event]',
                '[yaymail_addon_subscription_event_date]' => '[yaymail_wc_subscription_event_date]',
            ];

            foreach ( $shortcodes_map as $old => $new ) {
                $value = str_replace( $old, $new, $value );
            }
        }
    }

    // Get value or default from old version
    private function get_value_or_default( $element, $key, $default ) {
        return isset( $element['settingRow'][ $key ] ) ? $element['settingRow'][ $key ] : $default;
    }
}
