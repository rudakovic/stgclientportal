<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Remove the action for All in one intranet plugin
 * `https://wordpress.org/plugins/all-in-one-intranet/
 *
 * @since 1.0.9
 */
if ( class_exists( 'aioi_basic_all_in_one_intranet' ) && isset( $_GET['create'] ) ) {
	remove_action( 'template_redirect', array( aioi_basic_all_in_one_intranet::get_instance(), 'aioi_template_redirect' ), 10 );
}
