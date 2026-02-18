<?php
namespace NUX\StripeSubToWooSub;

use WP_CLI_Command;
use WP_CLI;
use NUX\StripeSubToWooSub\CSV_Importer;
use NUX\StripeSubToWooSub\WooCommerce_Helpers;
use WP;

if ( ! class_exists( 'WP_CLI_Command' ) ) {
	return;
}


/**
 * CLI access to the sample plugin.
 *
 * This class adds CLI capabilities to the plugin by extending WP CLI. For testing purposes it includes a basic game of
 * ping pong. A version that is impossible to win.
 *
 * @since     1.0.0
 * @author    Mindsize <info@mindsize.me>
 * @copyright Copyright (c) 2017 Mindsize <info@mindsize.me>
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 *
 * ## EXAMPLES
 *
 *     # Play a round of ping pong.
 *     $ wp nux ping
 *     pong
 */
class CLI extends WP_CLI_Command {

    /**
	 * Example method to test that commands are registered.
	 *
	 * @return void
	 */
	public function ping() {
		WP_CLI::line( 'pong' );
	}

	public function verify_data_type( $args, $assoc_args ) {
		if ( get_option('nux_migration_staging') ) {
			WP_CLI::line('Connecting to Stripe in Test Mode');
		} else {
			WP_CLI::line('Connecting to Stripe in Live Mode');
		}
	}

	public function test_stripe( $args, $assoc_args ) {
		WooCommerce_Helpers::test_stripe_connection();
	}

    /**
	 * Imports data from CSV
	 *
	 * <file>
	 * :the csv file, full url path to file
	 *
	 * [--disable-notification[=<true>]]
	 * :disable sending email to customer
	 * default: true
	 *
	 * @return void
	 */
	public function import_stripe_customers( $args, $assoc_args ) {
		$disable_notification = $assoc_args['disable-notification'] === 'false' ? false : true;
		$file       = $args[0];
		$data = CSV_Importer::get_csv_data($file);

        $progress = \WP_CLI\Utils\make_progress_bar( 'Importing customers', count($data) );
		foreach($data as $row) {
            $customer = WooCommerce_Helpers::create_customer($row, $disable_notification);
			if ($customer) {
				WP_CLI::line('Customer created: ' . $row['Customer Email']);
			} else {
				WP_CLI::line('Customer already exists: ' . $row['Customer Email']);
			}
			$progress->tick();
        }
		$progress->finish();
	}


	/**
	 * Imports data from CSV
	 *
	 * --stripe-subs=<file>
	 * :the csv file, full url path to file
	 * 
	 * --mapped-products=<file>
	 * :the csv file, full url path to file
	 *
	 * [--mapped-key=<number>]
	 * :the column number of the key to map to
	 * default: 0
	 * 
	 * [--disable-notification[=<true>]]
	 * :disable sending email to customer
	 * default: true
	 *
	 * @return void
	 */
	public function create_woo_subscriptions( $args, $assoc_args ) {
		$disable_notification = $assoc_args['disable-notification'] === 'false' ? false : true;
		$mapped_key           = $assoc_args['mapped-key'] ?? 0;
		$subs_file            = $assoc_args['stripe-subs'];
		$mappings             = $assoc_args['mapped-products'];
		
		$subs_data = CSV_Importer::get_csv_data($subs_file);
		$mapping_data = CSV_Importer::get_keyed_csv_data($mappings, $mapped_key);
		//error_log('mapping data: ' . print_r($mapping_data, true));
        $progress = \WP_CLI\Utils\make_progress_bar( 'Importing Subscriptions', count($subs_data) );
		foreach($subs_data as $row) {
			//error_log('row: ' . print_r($mapping_data[$row['Plan']]['Mapped Subscription Product ID'], true));
            $subscription = WooCommerce_Helpers::create_subscription($row, $mapping_data[$row['Plan']]['Mapped Subscription Product ID'], $disable_notification);
			if ($subscription) {
				WP_CLI::line('Subscription created: ' . $row['Customer Email']);
			} else {
				WP_CLI::line('Subscription could not be created: ' . $row['Customer Email']);
			}
			$progress->tick();
			//break;
        }
		$progress->finish();
	}

	public function link_subs_to_stripe_payments( $args, $assoc_args ) {
		//get all subscriptions that have a meta value for _import_contains_stripe_data set to true
		$args = array(
			'subscriptions_per_page' => -1,
			'offset' => 0,
			'subscription_status' => array( 'any' ),
			//'post_type' => 'shop_subscription',
			//'post_status' => 'any',
			'meta_query' => array(
				array(
					'key' => '_import_contains_stripe_data',
					'value' => true,
					'compare' => '='
				)
			)
		);
	
		$subscriptions = wcs_get_subscriptions($args);
	
		// Now $subscriptions contains all subscriptions with _import_contains_stripe_data set to true
		error_log('subs marked true: ' . print_r(count($subscriptions), true));
		$progress = \WP_CLI\Utils\make_progress_bar( 'Importing Subscriptions', count($subscriptions) );
		$success = 0;
		$fail = 0;
		foreach( $subscriptions as $subscription ) {
			//error_log('subscription: ' . print_r($subscription, true));
			
			$result = WooCommerce_Helpers::connect_sub_to_stripe_payment($subscription);
			if ( $result ) {
				WP_CLI::line('Subscription connected to Stripe Payment: ' . $subscription->get_id());
				$success++;
			} else {
				WP_CLI::line('Subscription could not be connected to Stripe Payment: ' . $subscription->get_id());
				$fail++;
			
			}
			$progress->tick();
			
		}
		$progress->finish();
		WP_CLI::success('Finished Successfully: ' . $success . ', Failed: ' . $fail);
	}

	public function activate_imported_subscriptions( $args, $assoc_args ) {
		//only activating subscriptions that successfully connected to stripe payments or did not have stripe data
		\WP_CLI::confirm( "Before you activate, please confirm your understanding that the next payment date will be validated and may be adjusted to two hours from now if the date is in the past. Do you want to continue?" );
		//get all subscriptions that have a meta value for _import_contains_stripe_data set to true
		$args = array(
			'subscriptions_per_page' => -1,
			'offset' => 0,
			'subscription_status' => array( 'any' ),
			//'post_type' => 'shop_subscription',
			//'post_status' => 'any',
			'meta_query' => array(
				
				array(
					'key' => '_import_status',
					'value' => array('connected','created'),
					'compare' => 'IN'
				)
			)
		);
	
		$subscriptions = wcs_get_subscriptions($args);
	
		// Now $subscriptions contains all subscriptions with _import_contains_stripe_data set to true
		error_log('subs marked true: ' . print_r(count($subscriptions), true));
		
		$progress = \WP_CLI\Utils\make_progress_bar( 'Activating Subscriptions', count($subscriptions) );

		foreach( $subscriptions as $subscription ) {
			//error_log('subscription: ' . print_r($subscription, true));
			
			WooCommerce_Helpers::activate_subscription($subscription);
			
			$progress->tick();
		}
		$progress->finish();
		WP_CLI::success('Finished');
		
	}

	public function cancel_legacy_stripe_subscription( $args, $assoc_args ) {
		$args = array(
			'subscriptions_per_page' => -1,
			'offset' => 0,
			'subscription_status' => array( 'any' ),
			//'post_type' => 'shop_subscription',
			//'post_status' => 'any',
			'meta_query' => array(
				'relationship' => 'AND',
				array(
					'key' => '_import_status',
					'value' => array('pending cancellation'),
					'compare' => 'IN'
				),
				array(
					'key' => '_import_contains_stripe_sub',
					'value' => true,
					'compare' => '='
				)
			)
		);
		$subscriptions = wcs_get_subscriptions($args);
		error_log('subs being cancelled: ' . print_r(count($subscriptions), true));
		$progress = \WP_CLI\Utils\make_progress_bar( 'Cancelling Stripe Subscriptions', count($subscriptions) );

		foreach( $subscriptions as $subscription ) {
			//error_log('subscription: ' . print_r($subscription, true));
			
			WooCommerce_Helpers::cancel_stripe_legacy_sub($subscription);
			
			$progress->tick();
		}
		$progress->finish();
		WP_CLI::success('Finished');
	}

	public function check_subscription( $args, $assoc_args ) {

		$sub = wcs_get_subscription( 546 );
		error_log('sub: ' . print_r($sub, true));
		$payment_method = $sub->get_payment_method();
		error_log('payment method: ' . print_r($payment_method,true));
		/*$args = array(
			'subscriptions_per_page' => -1,
			'offset' => 0,
			'subscription_status' => array( 'any' ),
			//'post_type' => 'shop_subscription',
			//'post_status' => 'any',
			'meta_query' => array(
			  array(
				'key' => '_import_contains_stripe_data',
				'value' => true,
				'compare' => '='
			  )
			)
		  );
		  
		  $subscriptions = \wcs_get_subscriptions($args);
		  error_log(print_r($subscriptions, true));*/
	}

}

//new CLI();

WP_CLI::add_command( 'nux', __NAMESPACE__ . '\CLI' );