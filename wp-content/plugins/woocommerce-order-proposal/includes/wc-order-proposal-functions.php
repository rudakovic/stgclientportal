<?php
/**
 * WooCommerce Product Order Proposal
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 *
 * DISCLAIMER
 *
 * @author    Voleatech
 * @category  Product
 * @copyright Copyright (c) 2014-2016, Voleatech GmbH
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

function wc_order_proposal_validate_date( $date ) {
	$_date = new \WC_DateTime( $date );

	if ( $_date ) {
		return true;
	} else {
		return false;
	}
}

function wc_order_proposal_save_time( $order_id, $order_start_time, $time, $prepay = "true" ) {

	$order_start_time = new \WC_DateTime( $order_start_time );
	$time             = new \WC_DateTime( $time );
	$order            = wc_get_order( $order_id );
	
	$order->update_meta_data( WC_Order_Proposal::ORDER_PROPOSAL_TIME, $time );
	$order->update_meta_data( WC_Order_Proposal::ORDER_PROPOSAL_USED, true );
	$order->update_meta_data( WC_Order_Proposal::ORDER_PROPOSAL_PREPAY, $prepay );
	$order->update_meta_data( WC_Order_Proposal::ORDER_PROPOSAL_START_TIME, $order_start_time );
	$order->save_meta_data();
}

/**
 * Get order proposal start date time.
 * 
 * @param int $order_id
 * @return WC_DateTime|string
 */
function wc_order_proposal_get_start_date( int $order_id ) {
	$start_date_time = wc_order_proposal_start_date( $order_id );
	// if the start date time is not set change it to order time
	if ( empty( $start_date_time ) ) {
		$order           = wc_get_order( $order_id );
		$start_date_time = $order->get_date_created();
	}

	return $start_date_time ?? '';
}

/**
 * Get order proposal start date time.
 * 
 * @param int $order_id
 * @deprecated 2.0.4
 * @return WC_DateTime|string|null
 */
function wc_order_proposal_get_start_time( $order_id ) {
	wc_deprecated_function( 'wc_order_proposal_get_start_time', '2.0.4', 'wc_order_proposal_get_start_date' );
	return wc_order_proposal_get_start_date( $order_id );
}

/**
 * Get order proposal start date time.
 * 
 * @param int $order_id
 * @return string
 */
function wc_order_proposal_start_date( $order_id ): string {
	$order = wc_get_order( $order_id );
	return $order->get_meta( WC_Order_Proposal::ORDER_PROPOSAL_START_TIME );
}

/**
 * Get order proposal start date time.
 * 
 * @param int $order_id
 * @deprecated 2.0.4
 * @return string
 */
function wc_order_proposal_start_time( $order_id ): string {
	wc_deprecated_function( 'wc_order_proposal_start_time', '2.0.4', 'wc_order_proposal_start_date' );
	return wc_order_proposal_start_date( $order_id );
}

function wc_order_proposal_get_order_old_time( $order_id ) {
	$order          = wc_get_order( $order_id );
	$order_old_time = new \WC_DateTime( $order->get_meta( WC_Order_Proposal::ORDER_OLD_TIME ) );

	return $order_old_time;
}

function wc_order_proposal_save_order_old_time( $order_id ) {
	$order          = wc_get_order( $order_id );
	$order_old_time = new \WC_DateTime( $order->get_date_created() );

	$order->update_meta_data( WC_Order_Proposal::ORDER_OLD_TIME, $order_old_time );
	$order->save_meta_data();
}

function wc_order_proposal_date_changed( $order_id ) {
	$order                       = wc_get_order( $order_id );
	$order_proposal_date_changed = $order->get_meta( WC_Order_Proposal::ORDER_PROPOSAL_DATE_CHANGED );

	return ( $order_proposal_date_changed == "true" );
}

function wc_order_proposal_set_date_changed( $order_id, $new_value ) {
	$order = wc_get_order( $order_id );
	$order->update_meta_data( WC_Order_Proposal::ORDER_PROPOSAL_DATE_CHANGED, $new_value ? "true" : "false" );
	$order->save_meta_data();
}

function wc_order_proposal_order_has_proposal( $order_id ) {
	if ( empty( $order_id ) ) {
		return false;
	}

	$order          = wc_get_order( $order_id );
	$order_proposal = $order->get_meta( WC_Order_Proposal::ORDER_PROPOSAL_USED );

	if ( ! empty( $order_proposal ) && $order_proposal ) {
		return true;
	}

	return false;
}

function wc_order_proposal_get_default_order_time() {
	return get_option( WC_Order_Proposal::ORDER_PROPOSAL_DEFAULT_TIME_OPTION, WC_Order_Proposal::ORDER_PROPOSAL_DEFAULT_TIME );
}

function wc_order_proposal_reserve_stock_manually() {
	$reserve = get_option( WC_Order_Proposal::ORDER_PROPOSAL_RESERVE_STOCK, 'no' );

	if ( $reserve == 'yes' ) {
		return true;
	}

	return false;
}

function wc_order_proposal_cancel_expired() {
	
	$default_prepay = get_option( WC_Order_Proposal::ORDER_PROPOSAL_CANCEL_EXPIRED, 'yes' );

	if ( $default_prepay == 'yes' ) {
		return true;
	}

	return false;
}

function wc_order_proposal_no_reduce_stock() {
	
	$default_no_reduce_stock = get_option( WC_Order_Proposal::ORDER_PROPOSAL_NO_REDUCE_STOCK, 'yes' );

	if ( $default_no_reduce_stock == 'yes' ) {
		return true;
	}

	return false;
}

function wc_order_proposal_pay_without_login() {
	
	$default_pay_no_login = get_option( WC_Order_Proposal::ORDER_PROPOSAL_PAY_NO_LOGIN, 'no' );

	if ( $default_pay_no_login == 'yes' ) {
		return true;
	}

	return false;
}

function wc_order_proposal_default_prepay() {
	
	$default_prepay = get_option( WC_Order_Proposal::ORDER_PROPOSAL_DEFAULT_PREPAY, 'no' );

	if ( $default_prepay == 'yes' ) {
		return true;
	}

	return false;
}

/**
 * Get order proposal date time.
 * 
 * @param int $order_id
 * @return WC_DateTime|string
 */
function wc_order_proposal_get_date( int $order_id ) {
	if ( wc_order_proposal_order_has_proposal( $order_id ) ) {
		$order = wc_get_order( $order_id );
		$time  = $order->get_meta( WC_Order_Proposal::ORDER_PROPOSAL_TIME );
	} else {
		$default_time = wc_order_proposal_get_default_order_time();
	
		// time add is in days so we have to recalculate
		$time = new \WC_DateTime( date_i18n( 'Y-m-d', current_time( 'timestamp' ) + $default_time * 24 * 60 * 60 ) );
	}

	return $time ?? '';
}

/**
 * Get order proposal date time.
 * 
 * @param int $order_id
 * @deprecated 2.0.4
 * @return WC_DateTime|string|null
 */
function wc_order_proposal_get_time( int $order_id ) {
	wc_deprecated_function( 'wc_order_proposal_get_time', '2.0.4', 'wc_order_proposal_get_date' );
	return wc_order_proposal_get_date( $order_id );
}

function wc_order_proposal_get_prepay( $order_id ) {
	if ( empty( $order_id ) ) {
		return false;
	}

	$order  = wc_get_order( $order_id );
	$prepay = $order->get_meta( WC_Order_Proposal::ORDER_PROPOSAL_PREPAY );
	
	if ( empty( $prepay ) ) {
		return wc_order_proposal_default_prepay();
	}

	if ( ! empty( $prepay ) && $prepay == "false" ) {
		return false;
	}

	return true;
}

/**
 * Change the order time to now
 */
function wc_order_proposal_change_time( $order_id ) {
	if ( ! empty( $order_id ) ) {
		$order = wc_get_order( $order_id );

		if ( $order ) {
			$order->set_date_created( new \WC_DateTime( 'now' ) );
			$order->save();
		}
	}
}

function wc_order_proposal_save_language( $order_id, $order_lang ) {
	$order = wc_get_order( $order_id );
	$order->update_meta_data( 'wpml_language', $order_lang );
	$order->save_meta_data();
}

/**
* Increase stock when order was cancelled
*/
function wc_order_proposal_increase_order_stock( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( $order ) {
		if ( ( ! wc_order_proposal_no_reduce_stock() && apply_filters( 'woocommerce_can_reduce_order_stock', true, $order ) && wc_order_proposal_order_has_proposal( $order_id ) && $order->get_meta( '_order_stock_reduced' ) && sizeof( $order->get_items() ) > 0 ) || ( apply_filters( 'order_proposal_always_reduce_stock', false, $order_id ) ) ) {
			foreach ( $order->get_items() as $item ) {
				$product_id = $item['variation_id'] != 0 ? $item['variation_id'] : $item['product_id'];
				$product    = wc_get_product( $product_id );
				if ( $product && $product->exists() && $product->managing_stock() ) {
					$qty       = apply_filters( 'woocommerce_order_item_quantity', $item->get_quantity(), $order, $item );
					$new_stock = wc_update_product_stock( $product, $qty, 'increase' );

					if ( $item['variation_id'] != 0 ) {
						/* translators: 1. item ID, 2. variation ID, 3,4. stock quantity */
						$order->add_order_note( sprintf( __( 'Item #%1$s variation #%2$s stock increased from %3$s to %4$s.', 'woocommerce-order-proposal' ), $item['product_id'], $item['variation_id'], $new_stock - $qty, $new_stock) );
					} else {
						/* translators: 1. item ID, 2,3. stock quantity*/
						$order->add_order_note( sprintf( __( 'Item #%1$s stock increased from %2$s to %3$s.', 'woocommerce-order-proposal' ), $item['product_id'], $new_stock - $qty, $new_stock) );
					}
				}
			}

			do_action( 'woocommerce_increase_order_stock', $order );
			//unset the has order stock reduced flag
			$order->delete_meta_data( '_order_stock_reduced' );
			$order->save_meta_data();
		}
	}
}

/**
 * Proposal time formatting function.
 * 
 * @param string $date
 * @param string $date_format
 * 
 * @return string
 */
function wc_order_proposal_format_date( string $date, string $date_format ): string {
    return apply_filters( 'wc_order_proposal_format_date', date_i18n( $date_format, strtotime( $date ) ), $date, $date_format );
}