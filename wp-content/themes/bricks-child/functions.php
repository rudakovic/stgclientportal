<?php


function remove_admin_bar()
{
    if (current_user_can('administrator')) {
        return true;
    }
    return false;
}

add_filter('show_admin_bar', 'remove_admin_bar', PHP_INT_MAX);


function valet_logout(){ ?>
	<a href="<?php echo wp_logout_url( home_url() ) ?>">Log out</a>
	<?php
}

add_shortcode('valet_logout', 'valet_logout');