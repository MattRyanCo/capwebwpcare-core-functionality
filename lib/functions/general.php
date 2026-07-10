<?php
/**
 * General
 *
 * This file contains any general functions.
 *
 * @package      Core_Functionality
 * @since        1.0.0
 * @link         https://github.com/mattryanco/mrwpcare-core-functionality
 * @author       Matt Ryan <matt@mattryan.co>
 * @copyright    Copyright (c) 2017, Matt Ryan
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */


/**
 * Customize Menu Order
 *
 * @since 1.0.0
 *
 * @param array $menu_ord. Current order.
 * @return array $menu_ord. New order.
 */
function mrco_custom_menu_order( $menu_ord ) {
	if ( ! $menu_ord ) { return true;
	}
	return array(
		'index.php', // this represents the dashboard link
		'edit.php?post_type=page', // the page tab
		'edit.php', // the posts tab
		'edit-comments.php', // the comments tab
		'upload.php', // the media manager
	);
}
add_filter( 'custom_menu_order', 'mrco_custom_menu_order' );
add_filter( 'menu_order', 'mrco_custom_menu_order' );

// Keep MainWP site user logged in all the time.
function mrco_extend_auth_cookie_expiration( $expiration, $user_id, $remember ) {
    // Set the desired cookie expiration time
    $cookie_expire = 2 * MONTH_IN_SECONDS;

    // Return the new expiration time
    return $cookie_expire;
}
add_filter( 'auth_cookie_expiration', 'mrco_extend_auth_cookie_expiration', 10, 3 );
