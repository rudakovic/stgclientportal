<?php
namespace NUX\StripeSubToWooSub;

use WC_Customer;
use WC_Subscriptions_Product;
use WC_Stripe_API;
use CLI;

class WooCommerce_Helpers {

    private static function stripe() {
        
        $mode = get_option('nux_migration_staging'); // true if using test data
        if ( ! $mode ) {
            \WP_CLI::confirm( "You are running this process with live Stripe data, are you sure you want to proceed?");
        }
       
        $key = $mode ? get_option('stripe_api_secret_test') : get_option('stripe_api_secret_live');
        $stripe = new \Stripe\StripeClient($key); 
        return $stripe;
    }

    public static function test_stripe_connection() {
        $stripe = self::stripe();
        return $stripe;
    }

    public static function create_customer($cusomter_data, $disable_notification = true ) {

        if ( $disable_notification ) {
            //prevent sending email to customer
            remove_action( 'woocommerce_created_customer', 'WC_Emails::send_transactional_email', 10, 10);
        }
     
        $customer = get_user_by('email',$cusomter_data['Customer Email']);
        if (!$customer) {
            $customer = new WC_Customer();
            $customer->set_email($cusomter_data['Customer Email']);
            $customer->set_role('customer');
            $customer->set_first_name($cusomter_data['Customer Name']);
            //$customer->set_default_source($source); //this is the default payment src for stripe, set this later
            $customer->save();
            update_user_option( $customer->get_id(), '_stripe_customer_id', $cusomter_data['Customer ID'], false );
            update_user_meta( $customer->get_id(), 'description', $cusomter_data['Customer Description'] );
            return $customer;
        } else {
            return false;
        } 
    }

    public static function create_subscription( $subscription_data, $mapped_product, $disable_notification = true ) {
        if ( $disable_notification ) {
            //prevent sending email to customer
            remove_action( 'woocommerce_order_status_completed', 'WC_Emails::send_transactional_email', 10, 10);
            //error_log(print_r($GLOBALS['wp_filter'],true));
        }

        //create subscription
        // First make sure all required functions and classes exist
        if( ! function_exists( 'wc_create_order' ) || ! function_exists( 'wcs_create_subscription' ) || ! class_exists( 'WC_Subscriptions_Product' ) ){
            return false;
        }
        $user = get_user_by('email',$subscription_data['Customer Email']);
        $user_id = $user->ID;
        $order = wc_create_order( array( 'customer_id' => $user_id ));

        if( is_wp_error( $order ) ){
            return false;
        }
        
        $product = wc_get_product( $mapped_product );
        $fname     = $user->first_name;
        $lname     = $user->last_name;
        $email     = $user->user_email;

        $address         = array(
            'first_name' => $fname,
            'last_name'  => $lname,
            'email'      => $email,
        );

        $order->set_address( $address, 'billing' );
        $qty = $subscription_data['Quantity'];
        $order->add_product( $product, $qty );

        $sub = wcs_create_subscription(array(
            'order_id' => $order->get_id(),
            'status' => 'pending', // Status should be initially set to pending to match how normal checkout process goes
            'billing_period' => WC_Subscriptions_Product::get_period( $product ),
            'billing_interval' => WC_Subscriptions_Product::get_interval( $product )
        ));

        if( is_wp_error( $sub ) ){
            //error_log('error creating subscription: ' . print_r($sub, true));
            return false;
        }

        // Modeled after WC_Subscriptions_Cart::calculate_subscription_totals()
        $start_date = $subscription_data['Created (UTC)'] . ':00';
        // Add product to subscription
        
        $sub->add_product( $product, $qty );

        $dates = array(
            'start'        => $start_date,
            'trial_end'    => WC_Subscriptions_Product::get_trial_expiration_date( $product, $start_date ),
            'next_payment' => $subscription_data['Current Period End (UTC)'] . ':00',
            'end'          => WC_Subscriptions_Product::get_expiration_date( $product, $start_date ),
        );

        $sub->update_dates( $dates );
        $sub->calculate_totals();

        // Update order status with custom note
        $note = ! empty( $note ) ? $note : __( 'Subscription added by wp-cli Stripe to Woo Subs migration.' );
        $order->update_status( 'completed', $note, true );
        // Also update subscription status to on hold from pending
        $sub->update_status( 'on-hold', $note, true );

        $sub->update_meta_data( '_migration_data', maybe_serialize( $subscription_data ) );
        $sub->update_meta_data( '_imported_by', 'wp-cli script' );
        $sub->update_meta_data( '_import_status', 'created' );
        $order->update_meta_data( '_migration_data', maybe_serialize( $subscription_data ) );
        $order->update_meta_data( '_imported_by', 'wp-cli script' );
        $order->update_meta_data( '_import_status', 'created' );
        if ( ! empty( $subscription_data['Customer ID'] ) ) {
            $sub->update_meta_data( '_import_contains_stripe_data', true );
            $order->update_meta_data( '_import_contains_stripe_data', true );
        }

        if ( ! empty( $subscription_data['id'] ) ) {
            $sub->update_meta_data( '_import_contains_stripe_sub', true );
            $order->update_meta_data( '_import_contains_stripe_sub', true );
        }
        
        $sub->save();
        $order->save();
        return $sub;
    }

    public static function connect_sub_to_stripe_payment($subscription) {
        //look at the existing sub if it exists
        $has_sub = $subscription->get_meta('_import_contains_stripe_sub', true);
        $stripe = self::stripe();
        $migration_data = maybe_unserialize( $subscription->get_meta('_migration_data', true) );
        if ( $has_sub ) {

            //use the current subs payment data
            try {
                $stripe_sub = $stripe->subscriptions->retrieve($migration_data['id'], []);
            } catch (\Exception $e) {
                //error_log('error retrieving stripe sub: ' . print_r($e, true));
                $subscription->update_meta_data( '_import_status', 'stripe connection failed' );
                $subscription->add_order_note( 'We attempted but were not able to connect this customers Stripe data to the subscription.' . $e->getMessage() );
                $subscription->save();
                return false;
            }

            //check if there is a default payment method
            if ( isset($stripe_sub) && $stripe_sub->default_payment_method ) {
                $payment_method = $stripe_sub->default_payment_method;
            } /*else {
                $stripe_cus = $stripe->customers->retrieve($migration_data['Customer ID'], []);
                if ( isset($stripe_cus) && ! empty($stripe_cus->default_source) ) {
                    $payment_method = $stripe_cus->default_source;
                } elseif ( isset($stripe_cus) && ! empty($stripe_cus->invoice_settings->default_payment_method) ) {
                    //error_log(print_r($stripe_cus, true));
                    $payment_method = $stripe_cus->invoice_settings->default_payment_method;
                    //error_log(print_r($stripe_cus, true));
                }
            }

            if (! $payment_method ) {
                //error_log('no payment method found');
                //error_log(print_r($stripe_cus,true));
                $subscription->update_meta_data( '_import_status', 'stripe connection failed' );
                $subscription->add_order_note( 'We attempted to connect to customer stripe data but could not determine a default payment method in stripe for this customer.' );
                return false;
            }

            //error_log('stripe subscription: ' . print_r($stripe_sub, true));
        } else {*/
        }

        if ( ! $payment_method ) {
            //find payment data for customer
            try {
                $stripe_cus = $stripe->customers->retrieve($migration_data['Customer ID'], []);
            } catch (\Exception $e) {
                //error_log('error retrieving stripe customer: ' . print_r($e, true));
                $subscription->update_meta_data( '_import_status', 'stripe connection failed' );
                $subscription->add_order_note( 'We attempted but were not able to connect this customers Stripe data to the subscription.' . $e->getMessage() );
                $subscription->save();
                return false;
            }
            if ( isset($stripe_cus) && ! empty($stripe_cus->default_source) ) {
                $payment_method = $stripe_cus->default_source;
            } elseif ( isset($stripe_cus) && ! empty($stripe_cus->invoice_settings->default_payment_method) ) {
                //error_log(print_r($stripe_cus, true));
                $payment_method = $stripe_cus->invoice_settings->default_payment_method;
                //error_log(print_r($stripe_cus, true));
            }
        }
            

        if (! $payment_method ) {
            //error_log('no payment method found');
            //error_log(print_r($stripe_cus,true));
            $subscription->update_meta_data( '_import_status', 'stripe connection failed' );
            $subscription->add_order_note( 'We attempted to connect to customer stripe data but could not determine a default payment method in stripe for this customer.' );
            return false;
        }
        
         // get the stripe api key to use here
        //error_log('stripe subscription: ' . print_r($stripe_sub, true));
        $payment_gateways = WC()->payment_gateways->payment_gateways();
        $payment_metadata = array(
            'post_meta' => array(
                '_stripe_customer_id' => [
                    'value' => $migration_data['Customer ID'],
                    'label' => 'Stripe Customer ID',
                ],
                '_stripe_source_id'   => [
                    'value' => $payment_method,
                    'label' => 'Stripe Source ID',
                ],
            )
                
        );
         // check if this is already processing automatically
        if ( isset($stripe_sub) && $stripe_sub->collection_method === 'charge_automatically' ) {
            error_log('charging is automattic for ' . $migration_data['id']);
            $subscription->set_payment_method( $payment_gateways[ 'stripe' ], $payment_metadata );
        } else {
            error_log('charging is not automattic for ' . $migration_data['id']);
            //error_log(print_r($stripe_sub, true));
            $subscription->set_payment_method( 'manual', $payment_metadata );
        }
        
        $subscription->update_meta_data( '_import_status', 'connected' );
        $subscription->add_order_note( 'We successfully added stripe data to this subscription.' );
        $subscription->save();            
        return true;
    }

    public static function activate_subscription($subscription) {
        $subscription->update_status( 'active' );
        $subscription->update_meta_data( '_import_status', 'pending cancellation' );
        $subscription->add_order_note( 'We successfully activated this imported subscription.' );
        $subscription->save();
        return true;
    }

    public static function cancel_stripe_legacy_sub($subscription) {
        $stripe = self::stripe();
        $migration_data = maybe_unserialize( $subscription->get_meta('_migration_data', true) );
        try {
            $result = $stripe->subscriptions->cancel($migration_data['id'], []);
        } catch (\Exception $e) {
            error_log('error cancelling stripe sub: ' . print_r($e, true));
            $subscription->update_meta_data( '_import_status', 'stripe cancellation failed' );
            $subscription->add_order_note( 'We attempted but were not able to cancel this customers Stripe subscription. Subscription is being placed on hold.' . $e->getMessage() );
            $subscription->update_status( 'on-hold' );
            $subscription->save();
            return false;
        }
    }
}