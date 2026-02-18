<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Sliced_Invoices_Deposit_Admin
 */
class Sliced_Invoices_Deposit_Admin {
	
	/** @var  object  Instance of this class */
	protected static $instance = null;
	
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
	 * @since   2.4.0
	 */
	public function __construct() {
		global $pagenow;
		
		add_action( 'add_meta_boxes', array( $this, 'add_deposit_meta_box' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 999 );
		add_action( 'admin_notices', array( $this, 'deposit_admin_notices' ) );
		
		if ( $pagenow === 'post.php' && sliced_get_the_type() === 'invoice' ) {
			add_action( 'admin_footer', array( $this, 'create_deposit_form' ) );
		}
		
		add_filter( 'sliced_admin_total_column', array( $this, 'add_total_amount_to_columns' ), 10, 1 );
		add_filter( 'sliced_display_the_line_totals', array( $this, 'add_deposit_balance_field_admin' ), 10 );
		add_filter( 'sliced_invoice_option_fields', array( $this, 'add_options_fields' ) );
		add_filter( 'sliced_translate_option_fields', array( $this, 'add_translate_options' ) );
		
	}
	
	
	/**
	 * Adding the deposit and balance field to the bottom of the invoice admin page.
	 *
	 * @since 2.2.0
	 */
	public function add_deposit_balance_field_admin( $output ) {
		
		$id = sliced_get_the_id();
		$invoice_type = Sliced_Deposit::check_deposit_invoice( $id );
		
		if ( ! empty( $invoice_type ) ) :
			
			// append "Project Total" line before total
			$append = '
				<div class="total deposit">' . __( Sliced_Deposit::$translate['deposit-project-total'], 'sliced-invoices-deposit' ) . ' <span class="alignright"><span id="sliced_deposit_project_total">0.00</span></span></div>
				<div class="total" style="display:none;">
			';
			$output = str_replace( '<div class="total">', $append, $output );
			
			if ( substr( $output, -6 ) === '</div>' ) {
				$totals = Sliced_Shared::get_totals( $id );
				$append = '<div class="total total-due">' .  __( Sliced_Deposit::$translate["deposit-amount-payable-{$invoice_type}"], 'sliced-invoices-deposit' );
				$append .= ' <span class="alignright">' . esc_html( Sliced_Shared::get_formatted_currency( $totals['total_due'] ) ) . '</span>';
				$append .= '</div>';
				$append .= '</div>';
				$output = substr_replace( $output, $append, -6 );
			}
			
			$output .= '<input type="hidden" id="sliced_deposit_this_is" value="' . $invoice_type . '" />';
			
		endif; 
		
		return $output;
		
	}
	
	
	/**
	 * Adds the deposit meta box container.
	 *
	 * @version 2.4.0
	 * @since   1.0.0
	 */
	public function add_deposit_meta_box() {
	
		global $pagenow;
		// check if we are adding a new invoice
		if ( $pagenow === 'post-new.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'sliced_invoice' ) {
			// add the new invoice meta box
			add_meta_box( 
				'sliced_invoices_deposit', 
				__( 'Deposit Invoices', 'sliced-invoices-deposit' ),
				array( $this, 'render_meta_box_help' ),
				'sliced_invoice', 
				'side' , 
				'low' 
			);
		}
		// otherwise, we go on...
		
		if ( ! isset( $_GET['post'] ) ) {
			return;
		}
		
		$id = (int) $_GET['post'];
		$recurring = get_post_meta( $id, '_sliced_recurring_number', true );
		
		if ( $id == null || $id == 0 || ! empty( $recurring ) )
			return;
		
		switch ( Sliced_Deposit::check_deposit_invoice( $id ) ) {
			case 'deposit':
				$label = sprintf( __( 'Deposit %s', 'sliced-invoices-deposit' ), sliced_get_invoice_label() ); 
				break;
			case 'balance':
				$label = sprintf( __( 'Balance %s', 'sliced-invoices-deposit' ), sliced_get_invoice_label() ); 
				break;
			default:
				$label = sprintf( __( 'Deposit %s', 'sliced-invoices-deposit' ), sliced_get_invoice_label() ); 
				break;
		}
		
		add_meta_box( 'sliced_invoices_deposit', __( $label, 'sliced-invoices-deposit' ), array( $this, 'render_meta_box_content' ), 'sliced_invoice', 'side' , 'high' );
		
	}
	
	
	/**
	 * Add the options for this extension to the invoice settings.
	 * 
	 * @version 2.4.0
	 * @since   2.1.0
	 */
	public function add_options_fields( $options ) {
		
		$options['fields'][] = array(
			'name'       => __( 'Deposit Invoices', 'sliced-invoices-deposit' ),
			'desc'       => '',
			'id'         => 'invoice_deposit_title',
			'type'       => 'title',
		);
		$options['fields'][] = array(
			'name'       => __( 'Deposit Invoice message', 'sliced-invoices-deposit' ),
			'desc'       => __( 'Default: "This is a Deposit Invoice for %deposit_amount% of the project total"<br />(The wildcard %deposit_amount% will be replaced with the amount of the deposit invoice.)', 'sliced-invoices-deposit' ),
			'default'    => Sliced_Deposit::$options['deposit-deposit-message'],
			'type'       => 'text',
			'id'         => 'deposit-deposit-message',
			'attributes' => array(
				'class'      => 'i18n-multilingual regular-text',
			),
			'sanitization_cb' => false, // otherwise %de will be interpreted as a unicode character
		);
		$options['fields'][] = array(
			'name'       => __( 'Balance Invoice message', 'sliced-invoices-deposit' ),
			'desc'       => __( 'Default: "This is a Balance Invoice for %deposit_amount% of the project total"<br />(The wildcard %deposit_amount% will be replaced with the amount of the balance invoice.)', 'sliced-invoices-deposit' ),
			'default'    => Sliced_Deposit::$options['deposit-balance-message'],
			'type'       => 'text',
			'id'         => 'deposit-balance-message',
			'attributes' => array(
				'class'      => 'i18n-multilingual regular-text',
			),
			'sanitization_cb' => false,
		);
		
		return $options;
	}
	
	
	/**
	 * Adding the deposit and balance amounts to the admin columns.
	 *
	 * @version 2.4.0
	 * @since   1.0.0
	 */
	public function add_total_amount_to_columns( $total ) {
		
		$id = sliced_get_the_id();
		$invoice_type = Sliced_Deposit::check_deposit_invoice( $id );
		
		// check if this is the deposit or balance invoice
		if ( ! empty( $invoice_type ) ) {
			
			$totals     = Sliced_Shared::get_totals( $id );
			$deposit    = sliced_get_invoice_deposit();
			
			if ( ! is_array( $deposit ) ) {
				// legacy... percentage only
				$deposit = $invoice_type == 'deposit' ? $deposit : ( 100 - (float)$deposit ); // work out the balance percentage or the deposit percentage
			} elseif ( $deposit['type'] === 'percentage' ) {
				$deposit = $invoice_type == 'deposit' ? $deposit['amount'] . '%' : ( 100 - (float)$deposit['amount'] ) . '%';
			} elseif ( $deposit['type'] === 'amount' ) {
				$deposit = $invoice_type == 'deposit' ? Sliced_Shared::get_formatted_currency( $deposit['amount'] ) : sliced_get_invoice_total( $id );
			}
			
			$total  = sprintf( __( '<span class="ui-tip" title="%1s %2s for %3s"><strong>%4s: %5s</strong></span><br />', 'sliced-invoices-deposit' )
						, ucfirst( $invoice_type ), sliced_get_invoice_label(), $deposit, ucfirst( $invoice_type ), sliced_get_invoice_total() );
			
			$total .= sprintf( __( 'Total: %s', 'sliced-invoices-deposit' )
						, Sliced_Shared::get_formatted_currency( $totals['addons']['deposit']['original_totals']['total'] ) );
			
		} 
		
		return $total;
		
	}
	
	
	/**
	 * Add the options for this extension to the translate settings.
	 * Deprecated. For compatibility with Easy Translate Extension < v2.0.0. Will be removed soon.
	 *
	 * @version 2.4.0
	 * @since   2.1.0
	 */
	public function add_translate_options( $options ) {
		
		if (
			class_exists( 'Sliced_Translate' )
			&& defined( 'SI_TRANSLATE_VERSION' )
			&& version_compare( SI_TRANSLATE_VERSION, '2.0.0', '<' )
		) {
		
			// add fields to end of options array
			$options['fields'][] = array(
				'name'       => __( 'Deposit Invoices', 'sliced-invoices-deposit' ),
				'desc'       => '',
				'id'         => 'translate_deposit_title',
				'type'       => 'title',
			);
			$options['fields'][] = array(
				'name'       => __( 'Deposit', 'sliced-invoices-deposit' ),
				'desc'       => '',
				'default'    => Sliced_Deposit::$translate['deposit-deposit'],
				'type'       => 'text',
				'id'         => 'deposit-deposit',
				'attributes' => array(
					'class'      => 'i18n-multilingual regular-text',
				),
			);
			$options['fields'][] = array(
				'name'       => __( 'Amount payable for this Deposit Invoice', 'sliced-invoices-deposit' ),
				'desc'       => '',
				'default'    => Sliced_Deposit::$translate['deposit-amount-payable-deposit'],
				'type'       => 'text',
				'id'         => 'deposit-amount-payable-deposit',
				'attributes' => array(
					'class'      => 'i18n-multilingual regular-text',
				),
			);
			$options['fields'][] = array(
				'name'       => __( 'Amount payable for this Balance Invoice', 'sliced-invoices-deposit' ),
				'desc'       => '',
				'default'    => Sliced_Deposit::$translate['deposit-amount-payable-balance'],
				'type'       => 'text',
				'id'         => 'deposit-amount-payable-balance',
				'attributes' => array(
					'class'      => 'i18n-multilingual regular-text',
				),
			);
			$options['fields'][] = array(
				'name'       => __( 'Project Total', 'sliced-invoices-deposit' ),
				'desc'       => '',
				'default'    => Sliced_Deposit::$translate['deposit-project-total'],
				'type'       => 'text',
				'id'         => 'deposit-project-total',
				'attributes' => array(
					'class'      => 'i18n-multilingual regular-text',
				),
			);
			
		}
		
		return $options;
	}
	
	
	/**
	 * Popup form to create the deposit.
	 *
	 * @since 1.0.0
	 */
	public function create_deposit_form() { 
		#region create_deposit_form
		
		$query_args = array( 'post_type' => 'sliced_invoice', 'deposit' => 'created' );
		$admin_url  = get_admin_url() . 'edit.php';
		
		?>
		<div id="create_deposit" style="display:none">
			
			<p><strong><?php _e( 'Important!', 'sliced-invoices-deposit' ) ?></strong> - <?php _e( 'Please ensure that any changes to this invoice are saved before creating the deposit invoice.', 'sliced-invoices-deposit' ) ?></p>
			
			<form method="POST" action="<?php echo esc_url( add_query_arg( $query_args, $admin_url ) ); ?>">
				
				<?php wp_nonce_field( 'sliced_invoices_deposit', 'sliced_invoices_deposit_nonce' ); ?>
				<p>
					<label><?php _e( 'Deposit Amount', 'sliced-invoices-deposit' ) ?></label>
					<input type="text" name="sliced_deposit_amount" value="" placeholder="10" required>
					<label><input type="radio" name="sliced_deposit_type" value="percentage" checked="checked">%</label>
					<label><input type="radio" name="sliced_deposit_type" value="amount"><?php echo sliced_get_currency_symbol(); ?></label>
				</p>
				<input type="hidden" name="sliced_deposit_invoice_id" value="<?php echo (int)$_GET['post'] ?>" >
				<p><input class="button button-primary button-large" type="submit" name="" value="<?php _e( 'Create The Deposit Invoice', 'sliced-invoices-deposit' ) ?>"></p>
				
			</form>
			
		</div>
		
		<?php
		#endregion create_deposit_form
	}
	
	
	/**
	 * Admin notice.
	 *
	 * @version 2.3.2
	 * @since   1.0.0
	 */
	public function deposit_admin_notices() {
		
		global $pagenow;
		
		if ( $pagenow === 'edit.php' && isset( $_GET['deposit'] ) && $_GET['deposit'] === 'created' ) {
			echo '<div class="updated">
				<p>' . sprintf( __( 'Deposit %s successfully created.', 'sliced-invoices' ), sliced_get_invoice_label() ) . '</p>
			</div>';
		}
		
	}
	
	
	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @version 2.3.2
	 * @since   2.2.1
	 */
	public function enqueue_scripts() {
		
		if ( method_exists( 'Sliced_Shared', 'is_sliced_invoices_page' ) && ! Sliced_Shared::is_sliced_invoices_page() ) {
			return;
		}
		
		if ( version_compare( SLICED_VERSION, '3.6.0', '>=' ) ) {
			wp_enqueue_script(
				'sliced-deposit',
				SLICED_INVOICES_DEPOSIT_URL . 'admin/js/admin.js',
				array( 'sliced-invoices' ),
				SLICED_INVOICES_DEPOSIT_VERSION
			);
		}
	}
	
	
	/**
	 * Render Meta Box content,
	 * depending on the type of invoice we are dealing with.
	 *
	 * @version 2.4.0
	 * @since   1.0.0
	 */
	public function render_meta_box_content() {
		
		$id = (int)$_GET['post'];
		
		$deposit = sliced_get_invoice_deposit();
		$invoice_type = Sliced_Deposit::check_deposit_invoice( $id );
		
		$has_parent = get_post_meta( $id, '_sliced_deposit_parent', true );
		$has_child  = get_post_meta( $id, '_sliced_deposit_child', true );
		
		if ( $invoice_type == 'deposit' ) {
			$edit_id    = get_post_meta( $id, '_sliced_deposit_parent', true );
			$other_type = __( 'balance', 'sliced-invoices-deposit' );
		} elseif ( $invoice_type == 'balance' ) { 
			$edit_id    = get_post_meta( $id, '_sliced_deposit_child', true );
			$other_type = __( 'deposit', 'sliced-invoices-deposit' );
		}
		
		if ( $invoice_type ) {
			$view = '<a class="button button-small" target="_blank" href="' . esc_url( get_the_permalink( $edit_id ) ) . '">' . __( 'View', 'sliced-invoices-deposit' ) . '</a>';
			$edit = '<a class="button button-small" target="_blank" href="' . esc_url( admin_url( 'post.php?post=' . $edit_id ) ) . '&action=edit' . '">' . __( 'Edit', 'sliced-invoices-deposit' ) . '</a>';
			
			if ( ! is_array( $deposit ) ) {
				// legacy... percentage only
				$deposit = $invoice_type == 'deposit' ? $deposit : ( 100 - (float)$deposit ); // work out the balance percentage or the deposit percentage
				printf( __( '<h4 class="no-print sliced-message message">This is a %1s %2s for %3s%% of the project total</h4>', 'sliced-invoices-deposit' ), ucfirst( $invoice_type ), sliced_get_invoice_label(), $deposit );
			} elseif ( $deposit['type'] === 'percentage' ) {
				$deposit = $invoice_type == 'deposit' ? $deposit['amount'] : ( 100 - (float)$deposit['amount'] );
				printf( __( '<h4 class="no-print sliced-message message">This is a %1s %2s for %3s%% of the project total</h4>', 'sliced-invoices-deposit' ), ucfirst( $invoice_type ), sliced_get_invoice_label(), $deposit );
			} elseif ( $deposit['type'] === 'amount' ) {
				$deposit = $invoice_type == 'deposit' ? Sliced_Shared::get_formatted_currency( $deposit['amount'] ) : sliced_get_invoice_total( $id );
				printf( __( '<h4 class="no-print sliced-message message">This is a %1s %2s for %3s of the project total</h4>', 'sliced-invoices-deposit' ), ucfirst( $invoice_type ), sliced_get_invoice_label(), $deposit );
			}
			
			printf( __( '%1s or %2s the %3s %4s', 'sliced-invoices-deposit' ), $view, $edit, $other_type, sliced_get_invoice_label() );
		} else {
			echo '<a class="button button-large thickbox" href="#TB_inline?height=155&width=300&inlineId=create_deposit" title="' . __( 'Create Deposit Invoice', 'sliced-invoices-deposit' ) . '">' . __( 'Create Deposit', 'sliced-invoices-deposit' ) . '</a>';
		}
		
	}
	
	
	/**
	 * Render Help Meta Box content,
	 *
	 * @since 2.2.1
	 */
	public function render_meta_box_help() {
		
		$output = '<em>' . __( 'Deposit Invoice options will be displayed after you save this invoice.', 'sliced-invoices-deposit' ) . '</em>';
		
		echo $output;
		
	}
	
}
