<?php 
/**
 * Last Login
 *
 * Function for post duplication. Duplicates appear as drafts. User is redirected to the edit screen
 *
 * @package      Core_Functionality
 * @since        3.0.2
 * @author       Matt Ryan <matt@mattryan.co>
 * @copyright    Copyright (c) 2025, Matt Ryan
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

function mrco_duplicate_post_as_draft(){
	global $wpdb;
	if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'mrco_duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
		wp_die('No post to duplicate has been supplied!');
	}
 
	/*
	 * Nonce verification
	 */
	if ( !isset( $_GET['duplicate_nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['duplicate_nonce'] ) ), basename( __FILE__ ) ) )
		return;
 
	/*
	 * get the original post id
	 */
	$post_id = (isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
	/*
	 * and all the original post data then
	 */
	$post = get_post( $post_id );
 
	/*
	 * if you don't want current user to be the new post author,
	 * then change next couple of lines to this: $new_post_author = $post->post_author;
	 */
	$current_user = wp_get_current_user();
	$new_post_author = $current_user->ID;
 
	/*
	 * if post data exists, create the post duplicate
	 */
	if (isset( $post ) && $post != null) {
 
		/*
		 * new post data array
		 * Append suffix to name to make it stand out as duplicate.
		 */
		$args = array(
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $new_post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'draft',
			'post_title'     => $post->post_title . '-DUPLICATE',
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order
		);
 
		/*
		 * insert the post by wp_insert_post() function
		 */
		$new_post_id = wp_insert_post( $args );
 
		/*
		 * get all current post terms and set them to the new post draft
		 */
		$taxonomies = get_object_taxonomies($post->post_type); // Returns array of taxonomy names for post type, ex array("category", "post_tag").
		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
		}
 
		/*
		 * duplicate all post meta using $wpdb->prepare()
		 */
		$post_meta_infos = get_post_meta($post_id);
		if (!empty($post_meta_infos)) {
			foreach ($post_meta_infos as $meta_key => $meta_values) {
				if ($meta_key == '_wp_old_slug') continue;
				foreach ($meta_values as $meta_value) {
					add_post_meta($new_post_id, $meta_key, $meta_value);
				}
			}
		}
 
		/*
		 * finally, redirect to the edit post screen for the new draft
		 */
		wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
		exit;
	} else {
		wp_die(esc_html__('Post creation failed, could not find original post: ', 'mrwpcare-core-functionality') . esc_html($post_id));
	}
}
add_action( 'admin_action_mrco_duplicate_post_as_draft','mrco_duplicate_post_as_draft' );
 
/*
 * Add the duplicate link to action list for post_row_actions
 */
function mrco_duplicate_post_link( $actions, $post ) {
	if (current_user_can('edit_posts')) {
		$actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=mrco_duplicate_post_as_draft&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce' ) . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
	}
	return $actions;
}
 
// Enable Duplicate option for post and pages
add_filter( 'post_row_actions','mrco_duplicate_post_link', 10, 2 );
add_filter( 'page_row_actions','mrco_duplicate_post_link', 10, 2 );
