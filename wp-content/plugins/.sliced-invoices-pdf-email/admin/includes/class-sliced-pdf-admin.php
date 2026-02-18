<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Sliced_Invoices_Pdf_Admin
 */
class Sliced_Invoices_Pdf_Admin {
	
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
	 * @version 1.8.1
	 * @since   1.8.0
	 */
	public function __construct() {
		
		add_action( 'admin_init', array( $this, 'admin_notices' ) );
		add_filter( 'plugin_action_links_sliced-invoices-pdf-email/sliced-invoices-pdf-email.php', array( $this, 'plugin_action_links' ) );
		add_filter( 'sliced_actions_column', array( $this, 'add_pdf_button' ) );
		add_filter( 'sliced_email_option_fields', array( $this, 'add_email_options' ), 1 );
		add_filter( 'sliced_general_option_fields', array( $this, 'add_pdf_ssl_options' ), 1 );
		add_filter( 'sliced_invoice_option_fields', array( $this, 'add_invoice_pdf_options' ), 1 );
		add_filter( 'sliced_quote_option_fields', array( $this, 'add_quote_pdf_options' ), 1 );
		add_filter( 'sliced_pdf_option_fields', array( $this, 'add_pdf_options' ), 1 );
		add_filter( 'upload_mimes', array( $this, 'add_font_mimes' ) );
		add_filter( 'wp_check_filetype_and_ext', array( $this, 'add_font_mimes_2' ), 10, 4 );
		
	}
	
	
	/**
	 * Add the options fields for the emails.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	public function add_email_options( $options ) {
		
		// remove promo text from description
		$desc = explode( '<br />', $options['desc'] );
		$options['desc'] = $desc[0];
		
		$options['fields'][] = array(
			'name'      => __( 'Body Background', 'sliced-invoices-pdf-email' ),
			'default'   => '#eeeeee',
			'type'      => 'colorpicker',
			'id'        => 'body_bg',
		);
		$options['fields'][] = array(
			'name'      => __( 'Header Background', 'sliced-invoices-pdf-email' ),
			'default'   => '#dddddd',
			'type'      => 'colorpicker',
			'id'        => 'header_bg',
		);
		$options['fields'][] = array(
			'name'      => __( 'Content Background', 'sliced-invoices-pdf-email' ),
			'default'   => '#ffffff',
			'type'      => 'colorpicker',
			'id'        => 'content_bg',
		);
		$options['fields'][] = array(
			'name'      => __( 'Content Text Color', 'sliced-invoices-pdf-email' ),
			'default'   => '#444444',
			'type'      => 'colorpicker',
			'id'        => 'content_color',
		);
		$options['fields'][] = array(
			'name'      => __( 'Footer Background', 'sliced-invoices-pdf-email' ),
			'default'   => '#444444',
			'type'      => 'colorpicker',
			'id'        => 'footer_bg',
		);
		$options['fields'][] = array(
			'name'      => __( 'Footer Text Color', 'sliced-invoices-pdf-email' ),
			'default'   => '#ffffff',
			'type'      => 'colorpicker',
			'id'        => 'footer_color',
		);
		$options['fields'][] = array(
			'name'      => __( 'Footer Text', 'sliced-invoices-pdf-email' ),
			'type'      => 'wysiwyg',
			'default'   => '',
			'id'        => 'footer',
			'sanitization_cb' => false,
			'options'   => array(
				'media_buttons' => false, // show insert/upload button(s)
				'textarea_rows' => get_option('default_post_edit_rows', 5), // rows="..."
				'teeny'         => true, // output the minimal editor config used in Press This
				'tinymce'       => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
				'quicktags'     => true, // load Quicktags, can be used to pass settings directly to Quicktags using an array()
			),
		);
		
		return $options;
	}
	
	
	/**
	 * Allow upload of font files.
	 *
	 * @since   1.2.5
	 */
	public function add_font_mimes( $mimes ) {
		// see https://core.trac.wordpress.org/ticket/40175
		$mimes = array_merge($mimes, array(
			'ttf' => 'application/octet-stream',    // for WordPress 4.7.1-4.7.2
			'ttf|ttf' => 'application/x-font-ttf',  // hack for WordPress 4.7.3+
			// and a little future proofing:
			// (see http://www.iana.org/assignments/media-types/application/font-sfnt)
			'ttf|ttf|ttf' => 'application/font-sfnt',
		));
		return $mimes;
	}
	
	/**
	 * Allow upload of font files, part 2.
	 *
	 * @since   1.8.1
	 */
	public function add_font_mimes_2( $wp_check_filetype_and_ext, $file, $filename, $mimes ) {
		// see https://core.trac.wordpress.org/ticket/40175
		// 5 years and counting that WordPress has still not resolved this bug... their mime handling is still stupid and broken. :(
		// One would expect that adding mimes via the upload_mimes filter (see add_font_mimes() above) would be sufficient -- it used to be -- but not anymore.
		// So, the following code should "fix" it again for more recent versions of WP:
		if ( ! empty( $wp_check_filetype_and_ext['ext'] ) ) {
			return $wp_check_filetype_and_ext;
		}
		
		$ext = strtolower( end( explode( '.', basename( $filename ) ) ) );
		if ( $ext === 'ttf' ) {
			$wp_check_filetype_and_ext = array(
				'ext'  => 'ttf',
				'type' => 'application/font-sfnt',
			);
		}
		return $wp_check_filetype_and_ext;
	}
	
	
	/**
	 * Add the options field to the invoice section.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	public function add_invoice_pdf_options( $options ) {
		
		$options['fields'][] = array(
			'name'      => __( 'Custom PDF CSS', 'sliced-invoices-pdf-email' ),
			'desc'      => __( 'Add custom CSS to the PDF. Due to the nature of printing PDF\'s (and it\'s limited support of CSS), occasionally you may need to add extra styles to get the PDF to look right.', 'sliced-invoices-pdf-email' ),
			'default'   => '',
			'type'      => 'textarea_small',
			'id'        => 'pdf_css',
		);
		
		return $options;
	}
	
	
	/**
	 * Add the options field to the quote section.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	public function add_quote_pdf_options( $options ) {
		
		$options['fields'][] = array(
			'name'      => __( 'Custom PDF CSS', 'sliced-invoices-pdf-email' ),
			'desc'      => __( 'Add custom CSS to the PDF. Due to the nature of printing PDF\'s (and it\'s limited support of CSS), occasionally you may need to add extra styles to get the PDF to look right.', 'sliced-invoices-pdf-email' ),
			'default'   => '',
			'type'      => 'textarea_small',
			'id'        => 'pdf_css',
		);
		
		return $options;
	}
	
	
	/**
	 * Add PDF button to admin quote/invoices listings.
	 *
	 * @version 1.6.2
	 * @since   1.0.0
	 */
	public function add_pdf_button( $button ) {
		return Sliced_Pdf::get_pdf_button();
	}
	
	
	/**
	 * Add the options fields for PDFs.
	 *
	 * @version 1.8.0
	 * @since   1.2.5
	 */
	public function add_pdf_options( $options ) {
		
		// remove promo text from description
		$desc = explode( '<br />', $options['desc'] );
		$options['desc'] = $desc[0];
		
		$options['fields'][] = array(
			'name'      => __( 'Page Size', 'sliced-invoices-pdf-email' ),
			'desc'      => __( 'Select paper size for generated PDFs', 'sliced-invoices-pdf-email' ),
			'id'        => 'page_size',
			'type'      => 'select',
			'default'   => 'LETTER',
			'options'   => array(
				'LETTER'    => __( 'Letter', 'sliced-invoices-pdf-email' ),
				'LEGAL'     => __( 'Legal', 'sliced-invoices-pdf-email' ),
				'LEDGER'    => __( 'Ledger', 'sliced-invoices-pdf-email' ),
				'TABLOID'   => __( 'Tabloid', 'sliced-invoices-pdf-email' ),
				'EXECUTIVE' => __( 'Executive', 'sliced-invoices-pdf-email' ),
				'FOLIO'     => __( 'Folio', 'sliced-invoices-pdf-email' ),
				'B'         => __( 'B', 'sliced-invoices-pdf-email' ),
				'A'         => __( 'A', 'sliced-invoices-pdf-email' ),
				'DEMY'      => __( 'Demy', 'sliced-invoices-pdf-email' ),
				'ROYAL'     => __( 'Royal', 'sliced-invoices-pdf-email' ),
				'4A0'       => __( '4A0', 'sliced-invoices-pdf-email' ),
				'2A0'       => __( '2A0', 'sliced-invoices-pdf-email' ),
				'A0'        => __( 'A0', 'sliced-invoices-pdf-email' ),
				'A1'        => __( 'A1', 'sliced-invoices-pdf-email' ),
				'A2'        => __( 'A2', 'sliced-invoices-pdf-email' ),
				'A3'        => __( 'A3', 'sliced-invoices-pdf-email' ),
				'A4'        => __( 'A4', 'sliced-invoices-pdf-email' ),
				'A5'        => __( 'A5', 'sliced-invoices-pdf-email' ),
				'A6'        => __( 'A6', 'sliced-invoices-pdf-email' ),
				'A7'        => __( 'A7', 'sliced-invoices-pdf-email' ),
				'A8'        => __( 'A8', 'sliced-invoices-pdf-email' ),
				'A9'        => __( 'A9', 'sliced-invoices-pdf-email' ),
				'A10'       => __( 'A10', 'sliced-invoices-pdf-email' ),
				'B0'        => __( 'B0', 'sliced-invoices-pdf-email' ),
				'B1'        => __( 'B1', 'sliced-invoices-pdf-email' ),
				'B2'        => __( 'B2', 'sliced-invoices-pdf-email' ),
				'B3'        => __( 'B3', 'sliced-invoices-pdf-email' ),
				'B4'        => __( 'B4', 'sliced-invoices-pdf-email' ),
				'B5'        => __( 'B5', 'sliced-invoices-pdf-email' ),
				'B6'        => __( 'B6', 'sliced-invoices-pdf-email' ),
				'B7'        => __( 'B7', 'sliced-invoices-pdf-email' ),
				'B8'        => __( 'B8', 'sliced-invoices-pdf-email' ),
				'B9'        => __( 'B9', 'sliced-invoices-pdf-email' ),
				'B10'       => __( 'B10', 'sliced-invoices-pdf-email' ),
				'C0'        => __( 'C0', 'sliced-invoices-pdf-email' ),
				'C1'        => __( 'C1', 'sliced-invoices-pdf-email' ),
				'C2'        => __( 'C2', 'sliced-invoices-pdf-email' ),
				'C3'        => __( 'C3', 'sliced-invoices-pdf-email' ),
				'C4'        => __( 'C4', 'sliced-invoices-pdf-email' ),
				'C5'        => __( 'C5', 'sliced-invoices-pdf-email' ),
				'C6'        => __( 'C6', 'sliced-invoices-pdf-email' ),
				'C7'        => __( 'C7', 'sliced-invoices-pdf-email' ),
				'C8'        => __( 'C8', 'sliced-invoices-pdf-email' ),
				'C9'        => __( 'C9', 'sliced-invoices-pdf-email' ),
				'C10'       => __( 'C10', 'sliced-invoices-pdf-email' ),
				'RA0'       => __( 'RA0', 'sliced-invoices-pdf-email' ),
				'RA1'       => __( 'RA1', 'sliced-invoices-pdf-email' ),
				'RA2'       => __( 'RA2', 'sliced-invoices-pdf-email' ),
				'RA3'       => __( 'RA3', 'sliced-invoices-pdf-email' ),
				'RA4'       => __( 'RA4', 'sliced-invoices-pdf-email' ),
				'SRA0'      => __( 'SRA0', 'sliced-invoices-pdf-email' ),
				'SRA1'      => __( 'SRA1', 'sliced-invoices-pdf-email' ),
				'SRA2'      => __( 'SRA2', 'sliced-invoices-pdf-email' ),
				'SRA3'      => __( 'SRA3', 'sliced-invoices-pdf-email' ),
				'SRA4'      => __( 'SRA4', 'sliced-invoices-pdf-email' ),
			),
		);
		$options['fields'][] = array(
			'name'      => __( 'Page Orientation', 'sliced-invoices-pdf-email' ),
			'id'        => 'page_orientation',
			'type'      => 'select',
			'default'   => 'portrait',
			'options'   => array(
				'portrait'    => __( 'Portrait', 'sliced-invoices-pdf-email' ),
				'landscape'   => __( 'Landscape', 'sliced-invoices-pdf-email' ),
			),
		);
		$options['fields'][] = array(
			'name'      => __( 'Page Font Size', 'sliced-invoices-pdf-email' ),
			'desc'      => __( 'Default font size to use in the PDF.  Can be overridden by custom CSS, if any.', 'sliced-invoices-pdf-email' ),
			'id'        => 'page_font_size',
			'type'      => 'select',
			'default'   => '',
			'options'   => array(
				''        => __( 'Default', 'sliced-invoices-pdf-email' ),
				'8px'     => '8',
				'9px'     => '9',
				'10px'    => '10',
				'11px'    => '11',
				'12px'    => '12',
				'13px'    => '13',
				'14px'    => '14',
				'15px'    => '15',
				'16px'    => '16',
				'17px'    => '17',
				'18px'    => '18',
			),
		);
		$options['fields'][] = array(
			'name'      => __( 'Add Unicode Font (.ttf)', 'sliced-invoices-pdf-email' ),
			'desc'      => __( 'If you need to print PDFs in languages requiring unicode characters (Japanese, Chinese, Korean, etc.), you may upload the required font here. <br>(For a good source of international language fonts, see <a href="https://www.google.com/get/noto/" target="_blank">Google Noto Fonts</a>)', 'sliced-invoices-pdf-email' ),
			'id'        => 'extra_font',
			'type'      => 'file',
			'options'   => array(
				'url'     => false,
			),
		);
		$options['fields'][] = array(
			'name'      => __( 'Add Extended Unicode Font (.ttf)', 'sliced-invoices-pdf-email' ),
			'desc'      => __( 'If your unicode font requires a second, extended font file, you may upload it here.', 'sliced-invoices-pdf-email' ),
			'id'        => 'extra_font_ext',
			'type'      => 'file',
			'options'   => array(
				'url'     => false,
			),
		);
		$options['fields'][] = array(
			'name'      => __( 'PDF Generation Mode', 'sliced-invoices-pdf-email' ),
			'desc'      => __( 'Try "compatibility" mode if you encounter issues with creating PDFs.', 'sliced-invoices-pdf-email' ),
			'id'        => 'mode',
			'type'      => 'select',
			'default'   => 'fast',
			'options'   => array(
				'fast'    => __( 'Default', 'sliced-invoices-pdf-email' ),
				'slow'    => __( 'Compatibility', 'sliced-invoices-pdf-email' ),
			),
		);
		
		return $options;
	}
	
	
	/**
	 * Add the options field to the general section.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	public function add_pdf_ssl_options( $options ) {
		
		$options['fields'][] = array(
			'name'      => __( 'SSL Verify', 'sliced-invoices-pdf-email' ),
			'desc'      => __( 'Set this to False if there are issues printing PDF\'s. It may resolve the issue.', 'sliced-invoices-pdf-email' ),
			'default'   => 'true',
			'type'      => 'select',
			'id'        => 'pdf_ssl',
			'options'   => array(
				'true'    => 'True', 
				'false'   => 'False', 
			),
		);
		
		return $options;
	}
	
	
	/**
	 * Admin notices for various things.
	 *
	 * @version 1.8.0
	 * @since   1.5.0
	 */
	public function admin_notices() {
		
		// check just in case we're on < Sliced Invoices v3.5.0
		if ( class_exists( 'Sliced_Admin_Notices' ) ) {
		
			// Low memory warning
			$memory_good = false;
			$memory_limit = ini_get('memory_limit');
			if ( $memory_limit == -1 || $memory_limit === '' ) {
				$memory_good = true; // if it's -1, we're good!
			} else {
				$bytes = trim($memory_limit);
				$last = strtolower($bytes[strlen($bytes)-1]);
				$bytes = intval($memory_limit);
				switch($last) {
					// The 'G' modifier is available since PHP 5.1.0
					case 'g':
						$bytes *= 1024;
					case 'm':
						$bytes *= 1024;
					case 'k':
						$bytes *= 1024;
				}
				if ($bytes >= (64 * 1024 * 1024)) {
					$memory_good = true;
				}
			}
			if ( ! $memory_good ) {
				if ( ! Sliced_Admin_Notices::has_notice( 'pdf_invoice_low_memory_warning' ) ) {
					$notice_args = array(
						'class' => 'notice-warning',
						'content' => '<p>' . sprintf( __( 'Sliced Invoices PDF Extension has detected your server\'s memory limit is very low.  If you experience difficulty generating PDF files, please increase your PHP memory limit.  For further information, <a href="%s">see here</a>.', 'sliced-invoices-pdf-email' ), 'https://slicedinvoices.com/question/increase-php-memory-limit/?utm_source=notice_low_memory&utm_campaign=premium&utm_medium=sliced_invoices_pdf' ) . '</p>',
						'dismissable' => true,
						'dismiss_permanent' => '1',
					);
					Sliced_Admin_Notices::add_custom_notice( 'pdf_invoice_low_memory_warning', $notice_args );
				}
			} else {
				Sliced_Admin_Notices::remove_notice( 'pdf_invoice_low_memory_warning' );
			}
			
			// check for mbstring support (error, not dismissable)
			if ( ! extension_loaded( 'mbstring' ) ) {
			
				if ( ! Sliced_Admin_Notices::has_notice( 'pdf_invoice_mbstring_missing' ) ) {
					$notice_args = array(
						'class' => 'notice-error',
						'content' => '<p>' . sprintf( __( 'Sliced Invoices PDF Extension has detected \'mbstring\' is not enabled on your server.  Mbstring is required and must be enabled for PDF functionality to work properly.  For further information, <a href="%s">see here</a>.', 'sliced-invoices-pdf-email' ), 'https://slicedinvoices.com/question/what-is-mbstring-and-how-do-i-enable-it/?utm_source=notice_mbstring_missing&utm_campaign=premium&utm_medium=sliced_invoices_pdf' ) . '</p>',
						'dismissable' => false,
					);
					Sliced_Admin_Notices::add_custom_notice( 'pdf_invoice_mbstring_missing', $notice_args );
				}
			
			} else {
				Sliced_Admin_Notices::remove_notice( 'pdf_invoice_mbstring_missing' );
			}
			
			// WP Super Cache compatibility warning (warning, dismissable permanently (or until this plugin is activated again))
			if ( function_exists( 'wp_super_cache_text_domain' ) ) {
			
				if ( ! Sliced_Admin_Notices::has_notice( 'pdf_invoice_wp_super_cache_warning' ) ) {
					$notice_args = array(
						'class' => 'notice-warning',
						'content' => '<p>' . sprintf( __( 'Hey there, we noticed you are using WP Super Cache, which is great plugin... However, certain settings in WP Super Cache can potentially conflict with your Sliced Invoices PDF Extension.  Please be sure to <a href="%s">read this page</a> to make sure everything keeps running smoothly. --<em>Your friends at Sliced Invoices</em>', 'sliced-invoices-pdf-email' ), 'https://slicedinvoices.com/question/pdfs-look-like-gibberish-using-wp-super-cache/?utm_source=notice_wp_super_cache&utm_campaign=premium&utm_medium=sliced_invoices_pdf' ) . '</p>',
						'dismissable' => true,
						'dismiss_permanent' => '1',
					);
					Sliced_Admin_Notices::add_custom_notice( 'pdf_invoice_wp_super_cache_warning', $notice_args );
				}
				
			}
			
		}
		
	}
	
	
	/**
	 * Add links to plugin page
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	public function plugin_action_links( $links ) {
		$links[] = '<a href="'. esc_url( get_admin_url( null, 'admin.php?page=sliced_invoices_settings&tab=pdf' ) ) .'">' . __( 'Settings', 'sliced-invoices' ) . '</a>';
		return $links;
	}
	
}
