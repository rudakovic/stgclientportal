<?php
/**
 * Customer order proposal email
 *
 * @author 		Voleatech
 * @package 	WooCommerce/Templates/Emails
 * @version     2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php _e( "As requested, we send you your order proposal.", 'woocommerce-order-proposal' ); ?>
<br>
<strong>
	<?php
		/* translators: proposal date */
		printf( __( 'This proposal is valid until %s', 'woocommerce-order-proposal'), $proposal_date );
	?>
</strong></p>

<?php if ( apply_filters( 'woocommerce_order_proposal_send_payment_link', true, $order, $email ) ) : ?>
	<?php /* translators: payment link */ ?>
	<p><strong><?php printf( __( 'To pay for this proposal please use the following link: %s', 'woocommerce-order-proposal' ), '<a href="' . esc_url( $order->get_checkout_payment_url() ) . '">' . __( 'pay', 'woocommerce-order-proposal' ) . '</a>' ); ?></strong></p>
<?php endif; ?>

<p><?php _e( "Your proposal details are shown below:", 'woocommerce-order-proposal' ); ?></p>


<?php

/**
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
