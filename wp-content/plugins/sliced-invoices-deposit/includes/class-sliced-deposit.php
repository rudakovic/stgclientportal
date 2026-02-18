<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Sliced_Deposit
 */
class Sliced_Deposit {
	
	/** @var  object  Instance of this class */
	protected static $instance = null;
	
	public static $options = array(
		'deposit-deposit-message' => 'This is a Deposit Invoice for %deposit_amount% of the project total',
		'deposit-balance-message' => 'This is a Balance Invoice for %deposit_amount% of the project total',
	);
	
	/** @var  array  Deprecated. For compatibility with Easy Translate Extension < v2.0.0. Will be removed soon. */
	public static $translate = array();
	
	/**
	 * Gets the instance of this class, or constructs one if it doesn't exist.
	 */
	public static function get_instance() {
		
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Construct the class.
	 *
	 * Populates our current settings, validates settings, and hooks into all the
	 * appropriate filters/actions we will need.
	 *
	 * @version 2.4.0
	 * @since   1.0.0
	 */
	public function __construct() {
		
		load_plugin_textdomain(
			'sliced-invoices-deposit',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		); 
		
		if ( ! $this->validate_settings() ) {
			return;
		}
		
		$this->load_options();
		
		$this->load_translations();
		
		Sliced_Invoices_Deposit_Admin::get_instance();
		
		add_action( 'load-edit.php', array( $this, 'create_the_deposit_invoice' ) );
		
		// the filter to add the deposit to the totals
		add_filter( 'sliced_invoice_totals', array( $this, 'add_deposit_to_totals' ), 1000, 2 );
		
		// adding to the invoice on the front end
		add_filter( 'sliced_invoice_totals_output', array( $this, 'add_deposit_balance_field' ), 10 );
		add_action( 'sliced_invoice_after_body', array( $this, 'add_deposit_balance_text' ), 9999 );
		
	}
	
	
	/**
	 * Adding the deposit and balance field to the bottom of the invoice.
	 *
	 * @version 2.4.0
	 * @since   1.0.0
	 */
	public function add_deposit_balance_field( $output ) {
		
		$id = sliced_get_the_id();
		$invoice_type = $this->check_deposit_invoice( $id );
		$totals = Sliced_Shared::get_totals( $id );
		
		// check if this is the deposit invoice or balance
		if ( ! empty( $invoice_type ) ) {
			
			// remove everything from totals row to end of table (we'll replace it shortly)
			if ( $totals['payments'] ) {
				$output = preg_replace( '#<tr class="row-paid">(.*?)</table>#s', '', $output );
			} else {
				$output = preg_replace( '#<tr class="table-active row-total">(.*?)</table>#s', '', $output );
			}
			
			// here we go
			$output .= '
						<tr class="table-active row-total">
							<td class="rate"><strong>' . Sliced_Deposit::$translate['deposit-project-total'] . '</strong></td>
							<td class="total"><strong>' . esc_html( Sliced_Shared::get_formatted_currency( $totals['addons']['deposit']['original_totals']['total'] ) ) . '</strong></td>
						</tr>
						<tr>
							<td colspan="2" class="blank"></td>
						</tr>
						<tr>
							<td colspan="2" class="table-active">
								' . Sliced_Deposit::$translate["deposit-amount-payable-{$invoice_type}"] . '
							</td>
						</tr>';
			if ( $totals['addons']['deposit']['this_is'] === 'deposit' ) {
				$output .= '
						<tr class="row-deposit">
							<td class="rate">' . Sliced_Deposit::$translate['deposit-deposit'] . '</td>
							<td class="total">' . esc_html( Sliced_Shared::get_formatted_currency( $totals['addons']['deposit']['deposit'] ) ) . '</td>
						</tr>';
			}
			if ( $totals['addons']['deposit']['this_is'] === 'balance' ) {
				$output .= '
						<tr class="row-deposit">
							<td class="rate">' . Sliced_Deposit::$translate['deposit-deposit'] . '</td>
							<td class="total"><span style="color:red;">-' . esc_html( Sliced_Shared::get_formatted_currency( $totals['addons']['deposit']['deposit'] ) ) . '</span></td>
						</tr>';
			}
			if ( $totals['payments'] ) {
				$paid = Sliced_Shared::get_formatted_currency( $totals['payments'] );
				$output .= '
						<tr class="row-paid">
							<td class="rate">' . __( 'Paid', 'sliced-invoices' ) . '</td>
							<td class="total"><span style="color:red;">-' . esc_html( $paid ) . '</span></td>
						</tr>
				';
			}
			$output .= '<tr class="table-active row-total">
							<td class="rate"><strong>' .  __( 'Total Due', 'sliced-invoices' ) . '</strong></td>
							<td class="total"><strong>' .  esc_html( sliced_get_invoice_total_due() ) . '</strong></td>
						</tr>
					</tbody>
				</table>';
			
		}
		
		return apply_filters( 'sliced_deposit_invoice_totals_output', $output );
		
	}
	
	
	/**
	 * Adding the deposit and balance text to the top of the invoice.
	 *
	 * @version 2.4.0
	 * @since   1.0.0
	 */
	public function add_deposit_balance_text( $total ) {
		
		$invoice_type = $this->check_deposit_invoice( sliced_get_the_id() );
		
		// check if this is the deposit invoice or balance
		if ( ! empty( $invoice_type ) ) {
			
			$deposit    = sliced_get_invoice_deposit();
			
			if ( ! is_array( $deposit ) ) {
				// legacy... percentage only
				$deposit = $invoice_type == 'deposit' ? $deposit . '%' : ( 100 - (float)$deposit ) . '%'; // work out the balance percentage or the deposit percentage
			} elseif ( $deposit['type'] === 'percentage' ) {
				$deposit = $invoice_type == 'deposit' ? $deposit['amount'] . '%' : ( 100 - (float)$deposit['amount'] ) . '%';
			} elseif ( $deposit['type'] === 'amount' ) {
				$deposit = $invoice_type == 'deposit' ? Sliced_Shared::get_formatted_currency( $deposit['amount'] ) : sliced_get_invoice_total( $id );
			}
			
			$message = Sliced_Deposit::$options["deposit-{$invoice_type}-message"];
			$message = str_replace( '%deposit_amount%', $deposit, $message );
			
			echo apply_filters( 'sliced_deposit_invoice_message', '<h4 class="container no-print sliced-message message">'.$message.'</h4>' );
			
		}
		
	}
	
	
	/**
	 * Adds the original amount and the deposit amount to the line items.
	 *
	 * @version 2.4.0
	 * @since   1.0.0
	 */
	public function add_deposit_to_totals( $totals, $id ) {
		
		$decimals = Sliced_Shared::get_decimals();
		$deposit  = sliced_get_invoice_deposit( $id );
		$this_is  = $this->check_deposit_invoice( $id );
		
		if ( ! $this_is ) {
			return $totals;
		}
		
		$output = array(
			'original_totals'   => $totals,
			'this_is'           => $this_is,
			'deposit'           => 0,
			'balance'           => 0,
		);
		
		// first apply adjustments from any other addons
		if ( isset( $output['original_totals']['addons'] ) && is_array( $output['original_totals']['addons'] ) ) {
			foreach ( $output['original_totals']['addons'] as $addon ) {
				if ( isset( $addon['_adjustments'] ) && is_array( $addon['_adjustments'] ) ) {
					foreach ( $addon['_adjustments'] as $adjustment ) {
						$type   = isset( $adjustment['type'] ) ? $adjustment['type'] : false;
						$source = isset( $adjustment['source'] ) ? $adjustment['source'] : false;
						$target = isset( $adjustment['target'] ) ? $adjustment['target'] : false;
						if ( ! $type || ! $source || ! $target ) {
							continue; // if missing required fields, skip
						}
						if ( ! isset( $addon[ $source ] ) ) {
							continue; // if can't map source, skip
						}
						if ( ! isset( $output['original_totals'][ $target ] ) ) {
							continue; // if can't map target, skip
						}
						switch ( $type ) {
							case 'add':
								$output['original_totals'][ $target ] = $output['original_totals'][ $target ] + $addon[ $source ];
								break;
							case 'subtract':
								$output['original_totals'][ $target ] = $output['original_totals'][ $target ] - $addon[ $source ];
								break;
						}
						
					}
				}
			
			}
		}
		
		// calculate our balance and deposit amounts
		switch ( $this_is ) {
			
			case 'deposit': // check if this is the deposit invoice
				
				if ( ! is_array( $deposit ) ) {
					// legacy... percentage only
					$output['deposit'] = round( $output['original_totals']['total'] * ( (float)$deposit / 100 ), $decimals );
				} elseif ( $deposit['type'] === 'percentage' ) {
					$output['deposit'] = round( $output['original_totals']['total'] * ( (float)$deposit['amount'] / 100 ), $decimals );
				} elseif ( $deposit['type'] === 'amount' ) {
					$output['deposit'] = $deposit['amount'];
				}
				
				$output['balance'] = $output['original_totals']['total'] - $output['deposit'];
				
				$output['_adjustments'] = array(
					array(
						'type'   => 'subtract',
						'source' => 'balance',
						'target' => 'total',
					),
				);
				
				break;
				
			case 'balance':  // check if this is the balance invoice 
				
				// get totals from the deposit invoice, just in case the balance invoice was modified later
				$deposit_invoice_id = get_post_meta( $id, '_sliced_deposit_child', true );
				$deposit_totals = Sliced_Shared::get_totals( $deposit_invoice_id );
				
				// the balance remaining is simply the current total minus whatever deposit was paid
				$output['deposit'] = $deposit_totals['total'];
				$output['balance'] = $output['original_totals']['total'] - $output['deposit'];
				
				$output['_adjustments'] = array(
					array(
						'type'   => 'subtract',
						'source' => 'deposit',
						'target' => 'total',
					),
				);
				
				break;
				
		}
		
		// return data
		$totals['addons']['deposit'] = $output;
		
		/*
		// extra info for whoever needs it later
		if ( is_array( $deposit ) ) {
			$deposit['this_is'] = $this_is;
			$deposit['sub_total'] = $sub_total;
			$deposit['total'] = $total;
			$deposit['tax'] = $tax;
		}
		
		return array(
			'original_sub_total'    => $original_sub_total,
			'original_total'        => $original_total,
			'original_tax'          => $original_tax,
			'sub_total'             => $sub_total,
			'tax'                   => $tax,
			'total'                 => $total,
			'total_due'             => $total,   // this is important for the Discount and Partial Payments Extension
			'deposit'               => $deposit, // this is important for the Woo invoices Plugin
		);
		*/
		$totals['deposit'] = array( 'total' => $output[$this_is] - $output['original_totals']['payments'] ); //temporary for woo invoices
		
		return $totals;
		
	}
	
	
	/**
	 * Check if this is a deposit, balance or normal invoice.
	 *
	 * @version 2.4.0
	 * @since   1.0.0
	 */
	public static function check_deposit_invoice( $id ) {
		
		$has_parent = get_post_meta( $id, '_sliced_deposit_parent', true );
		$has_child  = get_post_meta( $id, '_sliced_deposit_child', true );
		
		if ( $has_parent ) { // this is a deposit invoice
			$result = 'deposit';
		} elseif ( $has_child ) { // this is the balance invoice
			$result = 'balance';
		} else { // there is no deposit
			$result = false;
		}
		
		return $result;
		
	}
	
	
	/**
	 * Creates the deposit invoice.
	 *
	 * @since 1.0.0
	 */
	public function create_the_deposit_invoice() {
		
		//Check if our nonce is set.
		if ( ! isset( $_POST['sliced_invoices_deposit_nonce'] ) )
			return;
		
		if ( ! wp_verify_nonce( $_POST['sliced_invoices_deposit_nonce'], 'sliced_invoices_deposit' ))
			wp_die( 'Oh no, there was an issue creating the deposit invoice.' );
		
		global $wpdb;
		
		// OK, its safe for us to save the data now.
		$id = $_POST['sliced_deposit_invoice_id'];
		
		// Sanitize the user input and Update the meta field
		$amount = sanitize_text_field( $_POST['sliced_deposit_amount'] );
		$type = sanitize_text_field( $_POST['sliced_deposit_type'] );
		update_post_meta( $id, '_sliced_invoice_deposit', array( 'amount' => $amount, 'type' => $type ) );
		
		// get the parent invoice post object so we can copy the data
		$parent_invoice = get_post( $id );
		
		// Arguments for the new invoice
		$args = apply_filters( 'sliced_deposit_invoice_args', array(
			'post_title'      => __( 'Deposit -', 'sliced-invoices-deposit' ) . ' ' . $parent_invoice->post_title,
			'post_content'    => $parent_invoice->post_content,
			'post_status'     => 'publish',
			'post_type'       => 'sliced_invoice',
			'post_parent'     => $parent_invoice->ID,
			'post_password'   => $parent_invoice->post_password,
			'post_date'       => $parent_invoice->post_date,
		) );
		
		// Insert the new child/deposit invoice into the database
		$new_invoice_id = wp_insert_post( $args );
		
		/*
		 * get all current post terms ad set them to the new post draft
		 */
		$taxonomies = get_object_taxonomies($parent_invoice->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($parent_invoice->ID, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($new_invoice_id, $post_terms, $taxonomy, false);
		}
		
		// duplicate post metas
		$post_metas = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id=%d",
				$parent_invoice->ID
			)
		);
		if ( $post_metas && count( $post_metas ) ) {
			$sql_query = "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES ";
			$sql_values = array();
			foreach ( $post_metas as $post_meta ) {
				$meta_key = esc_sql( $post_meta->meta_key );
				$meta_value = esc_sql( $post_meta->meta_value );
				$sql_values[]= "($new_invoice_id, '$meta_key', '$meta_value')";
			}
			$sql_query .= implode( ',', $sql_values );
			$wpdb->query( $sql_query );
		}
		
		// update the invoice number on the deposit with a suffix
		$number = sliced_get_invoice_number( $parent_invoice->ID );
		update_post_meta( $new_invoice_id, '_sliced_invoice_number', $number . '-1' );

		update_post_meta( $new_invoice_id, '_sliced_deposit_parent', $id );
		update_post_meta( $id, '_sliced_deposit_child', $new_invoice_id );
		
		do_action( 'sliced_deposit_created', $new_invoice_id, $id );
		
	}
	
	
	/**
	 * Load options or use defaults.
	 *
	 * @version 2.4.0
	 * @since   2.1.0
	 */
	public function load_options() {
		$invoice_options = get_option( 'sliced_invoices' );
		foreach ( Sliced_Deposit::$options as $key => $value ) {
			if ( isset( $invoice_options[$key] ) ) Sliced_Deposit::$options[ $key ] = $invoice_options[ $key ];
		}
	}
	
	
	/**
	 * Load translations or use defaults.
	 * Deprecated. For compatibility with Easy Translate Extension < v2.0.0. Will be removed soon.
	 *
	 * @version 2.4.0
	 * @since   2.1.0
	 */
	public function load_translations() {
		Sliced_Deposit::$translate = array(
			'deposit-deposit'                => __( 'Deposit', 'sliced-invoices-deposit' ),
			'deposit-amount-payable-deposit' => __( 'Amount payable for this Deposit Invoice', 'sliced-invoices-deposit' ),
			'deposit-amount-payable-balance' => __( 'Amount payable for this Balance Invoice', 'sliced-invoices-deposit' ),
			'deposit-project-total'          => __( 'Project Total', 'sliced-invoices-deposit' ),
		);
		if (
			class_exists( 'Sliced_Translate' )
			&& defined( 'SI_TRANSLATE_VERSION' )
			&& version_compare( SI_TRANSLATE_VERSION, '2.0.0', '<' )
		) {
			$translate = get_option( 'sliced_translate' );
			foreach ( Sliced_Deposit::$translate as $key => $value ) {
				if ( isset( $translate[ $key ] ) ) Sliced_Deposit::$translate[ $key ] = $translate[ $key ];
			}
		}
	}
	
	
	/**
	 * Output requirements not met notice.
	 *
	 * @since   2.3.1
	 */
	public function requirements_not_met_notice() {
		echo '<div id="message" class="error">';
		echo '<p>' . sprintf( __( 'Sliced Invoices Deposit extension cannot find the required <a href="%s">Sliced Invoices plugin</a>. Please make sure the core Sliced Invoices plugin is <a href="%s">installed and activated</a>.', 'sliced-invoices-deposit' ), 'https://wordpress.org/plugins/sliced-invoices/', admin_url( 'plugins.php' ) ) . '</p>';
		echo '</div>';
	}
	
	
	/**
	 * Validate settings, make sure all requirements met, etc.
	 *
	 * @since   2.3.1
	 */
	public function validate_settings() {
	
		if ( ! class_exists( 'Sliced_Invoices' ) ) {
			
			// Add a dashboard notice.
			add_action( 'admin_notices', array( $this, 'requirements_not_met_notice' ) );
			
			return false;
		}
		
		return true;
	}
	
}
