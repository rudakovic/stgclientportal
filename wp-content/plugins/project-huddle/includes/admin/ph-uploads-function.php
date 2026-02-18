<?php

if (!defined('ABSPATH')) {
    exit;
}
/**
 * PH Upload Functions / Separate upload folder is created for PH Uploads
 * 
 * @since 4.5.5
 * 
 */
function ph_generate_files() {
    $setting_field = get_option( 'ph_separate_uploads_folder', false ) ? true : false;
    if( $setting_field ) {

        $wp_upload_dir = wp_upload_dir();
	    wp_mkdir_p( $wp_upload_dir['basedir'] . '/ph_uploads' );
	    wp_mkdir_p( $wp_upload_dir['basedir'] . '/ph_uploads/mockups' );
	    wp_mkdir_p( $wp_upload_dir['basedir'] . '/ph_uploads/attachments' );

        $path = $wp_upload_dir['basedir'] . '/ph_uploads';

        if(! file_exists( $path . '/.htaccess' )) {
            file_put_contents( $path . '/.htaccess', 'Options -Indexes' );
        } 

		if ( ! file_exists( $path . '/index.html' ) ) {
			file_put_contents( $path . '/index.html', '' );
		}
	}
}

add_action( 'admin_init', 'ph_generate_files');
 
/**
 * Change Downloads Upload Directory
 * @return void
 */
add_filter( 'wp_handle_upload_prefilter', 'ph_pre_upload' );

function ph_pre_upload( $file ) {
    $setting_field = get_option( 'ph_separate_uploads_folder', false ) ? true : false;
    if( $setting_field ) {
	    $is_mockup = isset( $_POST['ph_upload_type'] ) ? sanitize_text_field( wp_unslash( $_POST['ph_upload_type'] ) ) : false;
	    $post_id   = isset( $_POST['post'] ) ? sanitize_text_field( wp_unslash( $_POST['post'] ) ) : false;
	    $post_type = get_post_type( $post_id );
	    $ph_token  = isset( $_POST['access_token'] ) ? sanitize_text_field( wp_unslash( $_POST['access_token'] ) ) : false;

        if( 'ph_mockup' == $is_mockup || ( "phw_comment_loc" == $post_type || $ph_token ) ) {
            add_filter( 'upload_dir', 'ph_custom_upload_dir' );
        }
    }

    return $file;
}

function ph_custom_upload_dir( $param ) {

    $setting_field = get_option( 'ph_separate_uploads_folder', false ) ? true : false;
    if( $setting_field ) {
	    $is_mockup = isset( $_POST['ph_upload_type'] ) ? sanitize_text_field( wp_unslash( $_POST['ph_upload_type'] ) ) : false;
	    $post_id   = isset( $_POST['post'] ) ? sanitize_text_field( wp_unslash( $_POST['post'] ) ) : false;
	    $post_type = get_post_type( $post_id );
	    $ph_token  = isset( $_POST['access_token'] ) ? sanitize_text_field( wp_unslash( $_POST['access_token'] ) ) : false;
        
        if( 'ph_mockup' == $is_mockup ) {
            $ph_dir         = '/ph_uploads/mockups';
            $param['path'] = $param['basedir'] . $ph_dir;
            $param['url']  = $param['baseurl'] . $ph_dir;
        }
        
        if( ( "phw_comment_loc" == $post_type || $ph_token ) ) {
            $ph_dir         = '/ph_uploads/attachments';
            $param['path'] = $param['basedir'] . $ph_dir;
            $param['url']  = $param['baseurl'] . $ph_dir;
        }

    }

    return $param;
}
?>